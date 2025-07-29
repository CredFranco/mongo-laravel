<?php

namespace Mongo\resources;

trait ApplyLimit
{
    public function limit(int $limit): self
    {
        $this->search = $this->db->selectCollection($this->collection)->aggregate([
            ['$sample' => ['size' => $limit]]
        ]);
        return $this;
    }
}