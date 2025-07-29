<?php

namespace Mongo\resources;

trait FindAllPaginate
{
    public function findAllWithPaginate(array $filter = [], int $limit = 10, int $page = 1): array
    {
        unset($filter['limit']);
        unset($filter['page']);
        $skip = ($page - 1) * $limit;

        $collection = $this->db->selectCollection($this->collection);

        $data = $collection->find($filter, [
            'limit' => $limit,
            'skip' => $skip,
            'sort' => ['_id' => -1]
        ]);

        $total = $collection->countDocuments($filter);

        $items = array_map(function ($item) {
            return $this->bsonToArray($item);
        }, iterator_to_array($data));

        return [
            'data' => $items,

            'paginate' => [
                "current_page"  => $page,
                "total"         => $total,
                "per_page"      => $limit,
                "last_page"     => ceil($total / $limit)
            ],
        ];
    }
}