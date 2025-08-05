<?php

namespace Mongo\resources;

trait FindWithAgreggatePagination
{
    public function findWithAgreggate(array $filter = [], string $from, string $localField, string $foreignField, string $as, int $limit = 10, int $page = 1): array
    {
        unset($filter['limit'], $filter['page']);
        $skip = ($page - 1) * $limit;

        $collection = $this->db->selectCollection($this->collection);

        $pipeline = [];

        // Filtro inicial (se houver)
        if (!empty($filter)) {
            $pipeline[] = ['$match' => $filter];
        }
        
        $pipeline[] = [
            '$lookup' => [
                'from' => $from,
                'localField' => $localField,
                'foreignField' => $foreignField,
                'as' => $as
            ]
        ];

        $pipeline[] = [
            '$match' => [
                $as . '.0' => ['$exists' => true]
            ]
        ];

        // Total sem paginação
        $countPipeline = array_merge($pipeline, [
            ['$count' => 'total']
        ]);
        $countResult = iterator_to_array($collection->aggregate($countPipeline));
        $total = $countResult[0]['total'] ?? 0;

        // Paginação e ordenação
        $pipeline[] = ['$sort' => ['_id' => -1]];
        $pipeline[] = ['$skip' => $skip];
        $pipeline[] = ['$limit' => $limit];

        // Buscar os dados paginados
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
}