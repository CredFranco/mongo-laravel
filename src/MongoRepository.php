<?php

namespace Mongo;

use Illuminate\Support\Facades\DB;
use Mongo\resources\{
    FindOne, FindAllPaginate, FindWithLimit, ApplyLimit, CreateData, GenerateArray, FindAll, FindWithAgreggatePagination
};

class MongoRepository
{
    use FindOne, FindAllPaginate, FindWithLimit, ApplyLimit, CreateData, GenerateArray, FindAll, FindWithAgreggatePagination;

    protected $db;

    protected string $collection = '';

    protected $search;

    public function setCollection(string $collection): self
    {
        $this->collection = $collection;
        return $this;
    }

    public function __construct()
    {
        $this->db = DB::connection('mongodb')->getMongoDB();
    }

    
}
