<?php

namespace Mongo\resources;

trait UpdateData
{
    public function UpdateData(array $data, array $filter)
    {
        $update = ['$set' => $data];

        $result = $this->db
            ->selectCollection($this->collection)
            ->updateOne($filter, $update);

        return $result->getModifiedCount();
    }
}