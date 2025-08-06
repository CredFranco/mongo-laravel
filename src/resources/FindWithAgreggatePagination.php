<?php

namespace Mongo\resources;

trait FindWithAgreggatePagination
{
    public function findWithAgreggate(
        array $filter = [],
        array $pipeline = [],
        int $limit = 10,
        int $page = 1
    ): array {
        unset($filter['limit'], $filter['page']);
        $skip = ($page - 1) * $limit;

        $collection = $this->db->selectCollection($this->collection);

        if (!empty($filter)) {
            $pipeline[] = ['$match' => $filter];
        }

        $countPipeline = array_merge($pipeline, [
            ['$count' => 'total']
        ]);

        $countResult = iterator_to_array($collection->aggregate($countPipeline));
        $total = $countResult[0]['total'] ?? 0;

        $pipeline[] = ['$sort' => ['_id' => -1]];
        $pipeline[] = ['$skip' => $skip];
        $pipeline[] = ['$limit' => $limit];

        $cursor = $collection->aggregate($pipeline);

        $items = array_map(function ($item) {
            $array = $this->recursiveBsonToArray($item);
            return $this->flattenSingleValueArrays($array);
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

    private function recursiveBsonToArray($bson)
    {
        if ($bson instanceof \MongoDB\Model\BSONDocument || $bson instanceof \MongoDB\Model\BSONArray) {
            $bson = $bson->getArrayCopy();
        }

        if (is_array($bson)) {
            foreach ($bson as $key => $value) {
                $bson[$key] = $this->recursiveBsonToArray($value);
            }
        }

        return $bson;
    }

    private function flattenSingleValueArrays(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Se for array com 1 elemento e chave 0, substitui pelo valor interno
                if (count($value) === 1 && array_key_exists(0, $value)) {
                    $data[$key] = $this->flattenSingleValueArrays($value[0]);
                } else {
                    // Senão, aplica recursivamente
                    $data[$key] = $this->flattenSingleValueArrays($value);
                }
            }
        }

        return $data;
    }
}
