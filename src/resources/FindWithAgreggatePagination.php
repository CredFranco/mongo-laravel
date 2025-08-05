<?php

namespace Mongo\resources;

trait FindWithAgreggatePagination
{
    public function findWithAgreggate(
        array $filter = [],
        string $from,
        string $localField,
        string $foreignField,
        string $as,
        int $limit = 10,
        int $page = 1
    ): array {
        unset($filter['limit'], $filter['page']);
        $skip = ($page - 1) * $limit;

        $collection = $this->db->selectCollection($this->collection);

        $pipeline = [];

        if (!empty($filter)) {
            $pipeline[] = ['$match' => $filter];
        }

        $pipeline[] = [
            '$lookup' => [
                'from'         => $from,
                'localField'   => $localField,
                'foreignField' => $foreignField,
                'as'           => $as
            ]
        ];

        $pipeline[] = [
            '$match' => [
                $as . '.0' => ['$exists' => true]
            ]
        ];

        // Pipeline para contar total
        $countPipeline = array_merge($pipeline, [
            ['$count' => 'total']
        ]);

        $countResult = iterator_to_array($collection->aggregate($countPipeline));
        $total = $countResult[0]['total'] ?? 0;

        // Paginação
        $pipeline[] = ['$sort' => ['_id' => -1]];
        $pipeline[] = ['$skip' => $skip];
        $pipeline[] = ['$limit' => $limit];

        // Busca dados com paginação
        $cursor = $collection->aggregate($pipeline);
        $items = array_map(function ($item) {
            return $this->bsonToArray($item);
        }, iterator_to_array($cursor));

        return [
            'data' => $items,
            'paginate' => [
                'current_page' => $page,
                'total'        => $total,
                'per_page'     => $limit,
                'last_page'    => ceil($total / $limit)
            ],
        ];
    }

    // Conversão recursiva segura para arrays nativos
    private function bsonToArray($bson): array
    {
        if ($bson instanceof \MongoDB\Model\BSONDocument || $bson instanceof \MongoDB\Model\BSONArray) {
            $bson = $bson->getArrayCopy();
        }

        if (is_array($bson)) {
            foreach ($bson as $key => $value) {
                $bson[$key] = $this->bsonToArray($value);
            }
        }

        return $bson;
    }
}