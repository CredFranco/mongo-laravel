<?php

namespace Mongo\resources;

trait FindAll
{
    public function all(array $filter):array
    {
        $collection = $this->db->selectCollection($this->collection);

        $data = $collection->find($filter);

        return array_map(function ($item) {
            return $this->bsonToArray($item);
        }, iterator_to_array($data));
    }
}
