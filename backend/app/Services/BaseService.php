<?php

namespace App\Services;

use App\Services\ServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;


abstract class BaseService implements ServiceInterface{

    public $repository;

    public function fetch(array $params){
        try{
            $query = $this->composeFetchQuery($params);

            if(isset($params['not_paginated'])){
                return $query->get();
            }
               return $this->repository->paginate($query);
        } catch (Exception $e){
            throw $e;
        }
    }

    public function find(int $id){
        try{
            $model = $this->repository->getOneById($id);

            if(! $model){
                abort(404, 'Entry not found');
            }

            return $model;
        }catch (Exception $e){
            throw $e;
        }
    }


    public function store(array $data){
        try{
            return $this->repository->persist($data);
        }catch (Exception $e){
            throw $e;
        }
    }

    public function update(int $id, array $data){
        try{
            return $this->repository->persist($data, $id);
        }catch (Exception $e){
            throw $e;
        }

    }


    public function destroy(int $id)
    {
        try{
            $model = $this->find($id);
            return $model->delete();

        }catch (Exception $e){
            throw $e;
        }
    }

    public function composeFetchQuery(array $params){
        $query = $this->repository->query();

        foreach($params as $key => $param){
            if($this->repository->isKeyFillable($key)){
                $query->where($query->getModel()->getTable(). '.'. $key, $param);
            }
        }

        $this->composeSearchableQuery($query, $params);

        if(isset($params['exceptions'])){
            $query->whereNotIn($this->repository->make()->getTable().'.id', $params['exceptions']);
        }

        if(isset($params['eager_loads'])){
            $query->with($params['eager_loads']);
        }

        $this->composeOrderQuery($query, $params);

        return $query;

    }

    public function composeSearchableQuery(Builder $query, array $params){

         if(defined($this->repository->model .'::SEARCHABLE') && isset($params['search'])){
                $query->where(function($query) use ($params){
                    foreach($this->repository->model::SEARCHABLE as $searchable){
                        $query->orWhere($searchable, 'like', '%'.$params['search'].'%');
                    }
                });
            }

        return $query;
    }

    public function composeOrderQuery(Builder $query, array $params){
        if(isset($params['order_by'])){
            if($this->repository->isKeyFillable($params['order_by'])){
                switch($params['order_by']){
                    default:
                        $query->orderBy($params['order_by'], $params['order_direction'] ?? 'asc');
                }
            }elseif(method_exists($this->repository->model, 'scopeCustomOrderBy')){
                $query->customOrderBy($params['order_by'], $params['order_direction'] ?? 'asc');
            }
        } elseif(defined($this->repository->model. '::DEFAULT_ORDER_BY')){
            $query->orderBy($this->repository->model::DEFAULT_ORDER_BY, defined($this->repository->model. '::DEFAULT_ORDER_BY') ? $this->repository->model::DEFAULT_ORDER_DIRECTION :'asc');
        }else{
            $query->orderBy(resolve($this->repository->model)->getTable().'.id');
        }

        return $query;
    }

}
