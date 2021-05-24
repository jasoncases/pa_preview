<?php

namespace Proaction\System\Model;

use Illuminate\Database\Eloquent\Model;
use Proaction\Domain\Users\Resource\UserFactory;
use Proaction\System\Database\CDB;
use Proaction\System\Resource\Helpers\Arr;

class BaseProactionModel extends Model
{

    protected $autoColumns = [];

    public static function lastInsertId()
    {
        return static::first(CDB::raw('MAX(id) as id'))->id;
    }

    /**
     * This is a temporary solution to converting Proaction from the
     * custom framework to Laravels ORM.
     *
     * @param [type] $arr
     * @return void
     */
    public static function p_update($arr)
    {
        return (new static)->_p_update($arr);
    }

    /**
     * Insert a new record
     *
     * @param [type] $array
     * @return BaseProactionModel - returns the model with the row id
     */
    public static function p_create($array)
    {
        try {
            return (new static)->_p_create($array);
        } catch (\Exception $e) {
            die(static::class . '::p_create - ' . $e->getMessage());
        }
    }


    protected function _p_update($arr)
    {
        // check that there is an id value. Be default Laravel models
        // allow for changing identifiers, but Proaction uses only `id`
        // on all database tables
        if (!isset($arr['id'])) {
            throw new \Exception('BaseProactionModel::p_update - given array missing `id` property');
        }
        // get the target model
        $model = static::where('id', $arr['id'])->first();

        // remove the `id` property from the given array
        // this probably isn't strictly necessary, but we have no cause
        // to actually change the id value
        unset($arr['id']);
        // loop through given array and set the model properties
        foreach ($arr as $key => $value) {
            // check that the property exists
            if (array_key_exists($key, $model->getAttributes())) {
                // set the property to the value in the given array
                $model->{$key} = $value;
            }
        }

        // run auto columns
        $model = $this->_addAutoColumnValuesByAction($model, 'update');

        // call save on the model
        $model->save();

        // return the model instance
        return $model;
    }

    public static function deleteWhere($column, $value)
    {
        $model = static::where($column, $value)->get();
        foreach ($model as $m) {
            $m->delete();
        }
    }


    protected function _p_create($array)
    {
        // create the model and set the values from the provided array
        $model = new static;
        foreach ($array as $key => $value) {
            $model->{$key} = $value;
        }

        // allow for original proaction autoColumn values
        $model = $this->_addAutoColumnValues($model, 'save');

        $model->save();

        return $model;
    }

    private function _addAutoColumnValues($model, $action)
    {
        if (is_null($this->autoColumns) || empty($this->autoColumns)) {
            return $model;
        }
        return $this->_addAutoColumnValuesByAction($model, $action);
    }

    private function _addAutoColumnValuesByAction($model, $action)
    {
        if ($action == 'save') {
            return $this->_addInsertAutoColumns($model);
        } else if ($action == 'update') {
            return $this->_addUpdateAutoColumns($model);
        } else if ($action == 'delete') {
            return $this->_addDeleteAutoColumns($model);
        }
    }

    private function _addUpdateAutoColumns($model)
    {
        $validInsert = ['edited_by'];
        foreach ($this->autoColumns as $column) {
            // make sure we are valid insert column
            if (in_array($column, $validInsert)) {
                // if it's already set, do NOT overwrite
                if (!isset($model->{$column})) {
                    $model->{$column} = $this->_addValue($column);
                }
            }
        }
        return $model;
    }


    /**
     * Undocumented function
     *
     * @param [type] $model
     * @return void
     */
    private function _addInsertAutoColumns($model)
    {
        $validInsert = ['_ip', 'author', 'manager_id'];
        foreach ($this->autoColumns as $column) {
            // make sure we are valid insert column
            if (in_array($column, $validInsert)) {
                // if it's already set, do NOT overwrite
                if (!isset($model->{$column})) {
                    $model->{$column} = $this->_addValue($column);
                }
            }
        }
        return $model;
    }



    private function _addValue($column)
    {
        switch ($column) {
            case "_ip":
                return $this->_addModelIp();
            case "author";
                return $this->_addAuthor();
            case "manager_id":
                return $this->_addAuthor();
            case "edited_by":
                return $this->_addAuthor();
            case "deleted_by":
                return $this->_addAuthor();
        }
    }

    private function _addAuthor()
    {
        $user = UserFactory::create();
        return $user->get('employeeId');
    }

    private function _addModelIp()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'IP NOT FOUND';
    }
}
