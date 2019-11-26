<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-21
 * Time: 14:42
 */

namespace Base;


abstract class MultiTableInstance
{
    protected $main_key;

    protected $classes = [];

        abstract public function import(Array $data);
        abstract public static function scopeFull();

     public  function drop(int $id)
     {
        foreach ($this->classes as $class)
        {
            $model = new $class();
            $model->where($this->main_key,$id)
                ->delete();
        }
     }

    /**
     * @return MultiTableInstance
     * возвращает экземпляр класса
     */
    public static function model()
    {
        return new static();
    }
}