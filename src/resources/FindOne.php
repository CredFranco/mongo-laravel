<?php

namespace Mongo\resources;

trait FindOne
{
    public function findOneData(array $arr):self
    {
        $this->search = $this->db->selectCollection($this->collection)->findOne($arr);
        return $this;
    }
}
