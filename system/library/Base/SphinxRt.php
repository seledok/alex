<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-05
 * Time: 11:08
 */

namespace Base;

abstract class SphinxRt {

    protected $default_values = [];
    protected $vendor_product_id;
    protected $in_values;
    protected $force_replace = false;
    protected $key;
    protected $index;
    protected $table;

    protected $sql_where;
    protected $sql_select;
    protected $sql_group;
    protected $sql_start = 0;
    protected $sql_limit = 50;

    protected $result;
    protected $first_result;

    const SELECT = 1;
    const DELETE = 2;
    const UPDATE = 3;
    const INSERT = 4;
    const SKIP = 5;

    protected function set_default_values()
    {
        $this->in_values['id'] = $this->get_id();
    }

    public static function model()
    {
        return new static();
    }

    protected function prepare_for_update()
    {
        $this->set_default_values();

        $result = [];

        foreach ($this->in_values as $key => $value) {
            if(isset($this->default_values[$key]))
                $result[$key] = $value;
        }

        return $result;
    }


    protected function prepare_for_insert()
    {
        $this->set_default_values();

        $result = $this->default_values;

        foreach ($result as $key => &$one) {
            if(isset($this->in_values[$key])) $one = $this->in_values[$key];
        }

        return $result;

    }

    public function update($values)
    {
        $this->in_values = $values;
        $this->force_replace = false;
        return $this;
    }

    protected function get_id()
    {
        $key =  isset($this->in_values[$this->key]) ? $this->in_values[$this->key] : $this->{$this->key};
        if($key > 0)
            return $key;
        else {
            trigger_error('Error: Ключ = 0 !');
        }

    }

    public function getField($key)
    {
        if(isset($this->first_result[$key]))
            return $this->first_result[$key];
        else
            return null;
    }

    protected function make_query($method=1)
    {
        /// SELECT
        $query = $method == self::SELECT ? 'SELECT ' : 'DELETE '  ;

        if($this->sql_select && $method == self::SELECT)
            $query .= ' '. implode(', ', $this->sql_select)."\n";
        elseif($method == self::SELECT)
            $query .= "  * "."\n";

        /// FROM
        $query .= " FROM ".$this->index."\n";



        if($this->sql_where)
            $query .= ' WHERE '.implode(' && ',$this->sql_where) ."\n";
        // WHERE


        // GROUP
        if($this->sql_group)
            $query = 'GROUP BY '.implode(',',$this->sql_group)."\n";

        // LIMIT
        if($method == self::SELECT)
            $query .= "LIMIT {$this->sql_start}, {$this->sql_limit} ";

        return $query;

    }

    public function get()
    {
       // dd($this->make_query());
        $this->result = \sphinxDb::get_rows($this->make_query());
        $this->first_result = isset($this->result[0]) ? $this->result[0] : null;
        return $this;

    }

    public function delete()
    {
        \sphinxDb::query($this->make_query(self::DELETE));

        return $this;

    }

    public function where($field,$value,$compare='=')
    {
        $this->sql_where[] = " `$field` $compare '". \sDB::escape($value)."' ";
        return $this;
    }

    public function select($fields)
    {
        $this->select_fields = $fields;
    }

    public function insert($values)
    {
        $this->in_values = $values;
        $this->force_replace = true;
        return $this;
    }


    public function save()
    {
        if($this->force_replace || !\sphinxDb::get_first("SELECT id FROM {$this->index} WHERE id = ? ",$this->get_id()) ) {
            $fields_to_import = $this->prepare_for_insert();

            foreach ($fields_to_import as $field => $value)
            {
                $sql_vals[] = (is_int($value) || is_float($value)) ? '  '.\sDB::escape($value).' '  : " '".\sDB::escape($value)."' ";
                $skl_keys[] = " `$field` ";
            }

            //echo $fields_to_import['status'];

            $vals = implode(", ",$sql_vals);
            $keys = implode(", ",$skl_keys);

            \sphinxDb::query("REPLACE INTO {$this->index}  ($keys) VALUES($vals) ");
            //echo '/'.\sphinxDb::get_first("SELECT id FROM {$this->index} WHERE id = ? ",$this->get_id()).'/';
            //else echo '{+}';
            //var_dump($fields_to_import);
        } else {

            $fields_to_import = $this->prepare_for_update();

            foreach ($fields_to_import as $key => $val)
            {
                $fields[] = " `".$key."` = '".sDB::escape($val)."' ";
            }

            $sql_fields = implode(', ',$fields);

            \sphinxDb::query("UPDATE {$this->index} SET $sql_fields WHERE id = '?'  ", $this->get_id());
            //echo '{u}';
        }

    }



}