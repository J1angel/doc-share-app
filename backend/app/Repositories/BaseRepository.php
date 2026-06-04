<?php

namespace App\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


abstract class BaseRepository{

    public $model;
    public $modelInstance;

    protected $with=null;

    public function query(){
        return $this->make()->newQuery();
    }

    public function include($array){
        $this->with = $array;

        return $this;
    }

    public function getOneById($model){
        return $model instanceof Model ? $model: $this->getOneBy('id', $model);
    }

    public function persist(array $input, $model = null){
        if($model){
            $model = $this->getOneById($model);
        }else{
            $model = $this->make();
        }
        if($model instanceof $this->model){
            $model->fill($input);

            if($model->save()){
                return $model;
            }
        }

        throw new Exception('Not found', 404);
    }

    public function insert(array $data){
        return $this->model::insert($data);
    }

    public function delete($model){
        if($model instanceof Model){
            return $model->delete();
        }

        $id = $model;
        $model = $this->make();

        return $model->newQuery()
            ->where($model->getKeyName(), $id)
            ->delete();
    }


    public function countBy() {

        $model = $this->query();

        if(func_num_args() === 2){
            [$column, $value] = func_get_args();
            $method = is_array($value) ? 'whereIn' : 'where';
            $model = $model->$method($column, $value);
        } elseif (func_num_args() === 1){
            $columns = func_get_arg(0);

            if(is_array($columns)){
                foreach($columns as $column => $value){
                    $method = is_array($value) ? 'whereIn' : 'where';
                    $model = $model->$method($column, $value);
                }
            }
        }

        return $model->count();
    }

    public function getBy(){

        $model = $this->query();

        if($this->with !== null){
            $model->with($this->with);
        }

        if(count(func_get_args()) === 2){
            [$column, $value] = func_get_args();
            $method = is_array($value) ? 'whereIn' : 'where';
            $model = $model->$method($column, $value);
        } elseif (count(func_get_args()) === 1){
            $columns = func_get_arg(0);

            if(is_array($columns)){
                foreach($columns as $column => $value){
                    $method = is_array($value) ? 'whereIn' : 'where';
                    $model = $model->$method($column, $value);
                }
            }
        }

        return $model->get();
    }

    public function getOneBy(){

        $model = $this->query();

        if($this->with !== null){
            $model->with($this->with);
        }

        if(count(func_get_args()) === 2){
            [$column, $value] = func_get_args();
            $method = is_array($value) ? 'whereIn' : 'where';
            $model = $model->$method($column, $value);
        } elseif (count(func_get_args()) === 1){
            $columns = func_get_arg(0);
            if(is_array($columns)){
                foreach($columns as $column => $value){
                    $method = is_array($value) ? 'whereIn' : 'where';
                    $model = $model->$method($column, $value);
                }
            }
        }
        return $model->first();
    }

    public function isKeyFillable(string $key){

        if(! $this->modelInstance){
            $this->modelInstance = $this->make();
        }

        if(in_array($key, $this->modelInstance->getFillable())){
            return true;
        }

        return false;
    }

    public function paginate($query){
        return $query->paginate(request()->per_page);
    }

    public function make(){
        return new $this->model;
    }

    public function model(){
        return $this->model;
    }

    public function getId($model){
        return $model instanceof Model ? $model->getKey() : $model;
    }

    public function __call(string $name, array $arguments = [])
    {
        if(count($arguments) > 1){
            throw new Exception('Invalid arguments');
        }

        if(strpos($name, 'getBy') === 0){
            return $this->getBy(Str::snake(substr($name, 5)), $arguments[0]);
        }

        if(strpos($name, 'getOneBy') === 0){
            $column = Str::snake(substr($name, 8));

            return call_user_func([$this->make(), 'where'], $column, $arguments[0]->first());
        }
    }


}
