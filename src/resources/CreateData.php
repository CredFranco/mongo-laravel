<?php

namespace Mongo\resources;

trait CreateData
{
    public function create(array $data)
    {
        return $this->db->selectCollection($this->collection)->insertOne($data)->getInsertedCount();
    }

    public function createOrUpdate(array $data, array $filter)
    {
        $update = ['$set' => $data];

        $result = $this->db
            ->selectCollection($this->collection)
            ->updateOne($filter, $update, ['upsert' => true]);

        return $result->getUpsertedCount() + $result->getModifiedCount();
    }
}