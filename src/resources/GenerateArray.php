<?php

namespace Mongo\resources;

trait GenerateArray
{
    public function toArray($transformInternalData = false): array
    {
        if (is_null($this->search)) {
            return [];
        }

        // Se for um BSONDocument (ex: findOne), converter para array
        if ($this->search instanceof \MongoDB\Model\BSONDocument) {
            if($transformInternalData){
                return $this->bsonToArray(array_map(function ($item) {
                    if($item instanceof \MongoDB\Model\BSONArray){
                        return $this->bsonToArray($item);
                    }else{
                        return $item;
                    }

                }, iterator_to_array($this->search)));
            }
            return $this->bsonToArray($this->search);
        }

        // Se for um Cursor (ex: aggregate), iterar e converter cada item
        return array_map(function ($item) {

            return $this->bsonToArray($item);
        }, iterator_to_array($this->search));
    }

    private function bsonToArray($bson): array
    {
        $raw = (array) $bson;
        return $this->sanitizeForJson($raw);
    }

    private function sanitizeForJson($data)
    {
        if (!is_array($data) && !is_object($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $data[$key] = $this->sanitizeForJson($value);
            } elseif (is_float($value) && (is_nan($value) || is_infinite($value))) {
                $data[$key] = null; // ou 0
            }
        }

        return $data;
    }
}