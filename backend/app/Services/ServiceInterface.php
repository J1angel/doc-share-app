<?php

namespace App\Services;

interface ServiceInterface{
    public function store(array $data);
    public function update(int $id, array $data);
    public function find(int $id);
    public function fetch(array $params);
    public function destroy(int $id);
}
