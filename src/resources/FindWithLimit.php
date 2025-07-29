<?php

namespace Mongo\resources;

trait FindWithLimit
{
    public function findWithLimitNotFilter(array $limit = []):self
    {
        $pipeline = [];

        $pipeline[] = ['$sample' => ['size' => $limit['limit'] ?? 10]];

        $this->search = $this->db
            ->selectCollection($this->collection)
            ->aggregate($pipeline);

        return $this;
    }


    public function findWithLimit(array $filter, array $limit = []):self
    {

        $limite = $limit['limit'] ?? 10;
        $rand = mt_rand() / mt_getrandmax();

        $options = [
            'sort' => ['randomOrder' => 1],
            'limit' => $limite,
            'batchSize' => 1000,
            'maxTimeMS' => 60000,
        ];

        // Primeira tentativa: randomOrder >= rand
        $query = array_merge($filter, [
            'randomOrder' => ['$gte' => $rand]
        ]);

        $cursor = $this->db->selectCollection($this->collection)->find($query, $options);

        $docs = [];
        foreach ($cursor as $doc) {
            $docs[] = $doc;
        }

    // Se retornar menos que o limite, tenta buscar o restante com randomOrder < rand
        if (count($docs) < $limite) {
            $faltando = $limite - count($docs);

            $fallbackQuery = array_merge($filter, [
                'randomOrder' => ['$lt' => $rand]
            ]);

            $fallbackOptions = [
                'sort' => ['randomOrder' => 1],
                'limit' => $faltando,
                'batchSize' => 1000,
                'maxTimeMS' => 60000,
            ];

            $fallbackCursor = $this->db->selectCollection($this->collection)->find($fallbackQuery, $fallbackOptions);

            foreach ($fallbackCursor as $doc) {
                $docs[] = $doc;
            }
        }

        $this->search = $docs;

        return $this;
    }
}