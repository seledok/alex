<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-05
 * Time: 11:08
 */

namespace Base;

/**
 * Class Model
 * @package Base
 */
abstract class Model
{

    // WRITE
    /**
     * @var array поля разрешенные к импорту
     */
    protected $allowed_fields = [];

    /**
     * @var array поля которые являются JSON
     */
    protected $json_fields = [];


    protected $db = '';

    /**
     * @var
     * поле даты обновления если измненеий при апдейте не произошло обновится только это поле
     * поле обновляется всегда
     */
    protected $date_update_field;


    /**
     * @var array идентификаторы по кторым можно строку идетифицировать
     * ключем являеется главное а элементами сочетанияе или одно из дргуих
     */
    protected $sub_identifier = [];
    /**
     * @var array когда етсь несолкько ключей
     */
    protected $multi_identifier = [];
    /**
     * @var string
     * главный ключ таблицы
     */
    protected $main_key = 'id';

    /**
     * @var array
     * выбранные идентификаторы ИЛИ
     */
    protected $selected_identifier_or = [];
    /**
     * @var array
     * выбранные идентификаторы И
     */
    protected $selected_identifier_and = [];

    /**
     * @var array
     * при проверке есть ли строка в БД сюда помещается ответ
     */
    protected $have_item = [];

    /**
     * @var array
     * сюда помещаются поля которые мы хотим встаивть в базу
     */
    protected $data = [];
    /**
     * @var
     * имя таблицы
     */
    protected $table;

     // READ

    /**
     * @var array
     * массив таблиц и ключей в которые можно делать JOIN
     */
    protected $linked_fields = [];

    /**
     * @var array
     * тут запрос собирается что ли?
     */
    protected $query = [];
    /**
     * @var array
     * результат запроса
     */
    protected $result = [];
    /**
     * @var array
     * первая строка из результата
     */
    protected $first_result = [];
    /**
     * @var int
     * всего
     */
    public $total_count = 0;

    // массив sql в нем собирааются параметры для запросов
    /**
     * @var array
     */
    protected $sql_select = [];
    /**
     * @var array
     */
    protected $sql_where = [];
    /**
     * @var array
     */
    protected $sql_orWhere = [];
    /**
     * @var array
     */
    protected $sql_left_join = [];
    /**
     * @var array
     */
    protected $sql_inner_join = [];
    /**
     * @var array
     */
    protected $sql_group = [];

    /**
     * @var int
     */
    protected $sql_limit = 10;
    /**
     * @var int
     */
    protected $sql_start = 0;
    /**
     * @var int
     */
    protected $sql_order = [];

    /**
     * @var int
     * последнее действие
     */
    public $last_action = 0;
    /**
     * @var int
     * последний звпрос
     */
    public $last_query = 0;

    /**
     * типы запросов
     */
    const SELECT = 1;
    const DELETE = 2;
    const UPDATE = 3;
    const INSERT = 4;
    const SKIP = 5;


    /**
     * Model constructor.
     * @param array $data
     */
    public function __construct(Array $data = [])
     {
         if($data) {
             // добаивим все подходящие поля
             $this->data = array_intersect_key($data, array_flip($this->allowed_fields));
             // установим идентификаторы
             $this->updateIdentifier();
         }
     }

    /**
     * @return Model
     * возвращает экземпляр класса
     */
    public static function model()
     {
         return new static();
     }

     public function db($db)
     {
         $this->db = $db;
         return $this;
     }


     ///////////////////////// WRITE ////////////////////////////////////////////////////////////////////////////////////////////////

    /** Устанавливает значение поля
     * @param $key -имя поля
     * @param $value - значение поля
     * @return $this
     */
    public function set($key, $value)
     {
         $this->data[$key] = $value;
         $this->updateIdentifier();

         //var_dump($this);
         return $this;
     }


    /** Слудит для получения ИД элемента по имени, елси такого имени нет - создает и возвращает ИД созданного элемента
     * @param $name
     * @return mixed|null
     */
    public function getIdbyName($name)
     {
        // поищем по имени
         $id = $this->where('name',$name)->get()->getField($this->main_key); //\sDB::get_first("SELECT ? FROM ? WHERE name = '?' ",$this->main_key,$this->table,$data['name']);

         if($id)
             return $id;
         else {
             $this->data['name'] = $name;
             return $this->create($this->data)->insert()->getField($this->main_key);
         }

     }

    /** Возвращает значение осноновго ключа таблицы
     * @return bool|mixed
     */
    public function getMainKey()
     {
         return isset($this->first_result[$this->main_key]) ? $this->first_result[$this->main_key] : false;
     }


    /**
     * Заполняет идентификаторы из полученных полей
     */
    protected function updateIdentifier()
     {
         foreach ($this->data as $key => $value)
         {
             // ключ
             if($this->main_key == $key) {
                 $this->selected_identifier_and[$key] = $value;
                 $this->selected_identifier_or = [];
                 // другие поля не рассмтаривали бы но иногад главный ключ совсем не галвный
                 //return;
             }
             elseif (!empty($this->multi_identifier) && in_array($key,$this->multi_identifier))
             {
                 $this->selected_identifier_or[$key] = $this->data[$key];
             }
             elseif(!empty($this->sub_identifier) && isset($this->sub_identifier[$key])) { // это группа внутри одного идентификатора
                 $this->selected_identifier_and[$key] = $value;

                 foreach ($this->sub_identifier[$key] as $sub_identifier) { // береберем вложденные идетификаторы
                     if (isset($this->data[$sub_identifier])) // елси мы получили такой то добавим его
                         $this->selected_identifier_or[$sub_identifier] = $this->data[$sub_identifier];
                 }
             } elseif(!empty($this->sub_identifier) && in_array($key,$this->sub_identifier)) { // это группа идентификаторов
                 $this->selected_identifier_or[$key] = $value;
             }

            // elseif(in_array($key,$this->identifier))
            //     $selected_identifier_and[$key] = $value;
         }


     }

    /** добавляет поля из массива
     * @param $data
     * @return $this
     */
    public function create($data)
     {
         if($data) {
             // добаивим все подходящие поля
             $this->data = array_intersect_key($data, array_flip($this->allowed_fields));
             // установим идентификаторы
             $this->updateIdentifier();
         }

         return $this;
     }

    /**
     * сохраняет в базу элемент
     */
    public function save()
     {
         $this->mutate_data();

        if($this->sql_where || $this->sql_orWhere)
            return $this->updateWhere();
        elseif($this->haveIt())
            return $this->update();
        else
            return $this->insert();

     }

    /** Удаляет элемент
     * @return $this
     */
    public function delete()
     {

         \sDb::query($this->make_query(self::DELETE));

         $this->afterDelete();

         return $this;

     }

    /** Создает новый элемент
     * @return $this
     */
    protected function insert()
    {
        // приготоввим sql
        $insert_fields = $this->makeSql($this->data);

        //
        if(!$insert_fields) {
            $this->last_action = self::SKIP;
            return $this;
        }

        // вставим в базу
        \sDb::query("INSERT INTO ? SET $insert_fields ",$this->table);
        // получим id
        $this->data[$this->main_key] =  \sDb::insert_id();
        // присвоим результату все что втсаивли
        $this->first_result = $this->data;
        $this->result[0] = $this->data;

        $this->last_action = self::INSERT;

        $this->afterInsert();

        return $this;
    }

    /** Оставялет в массиве только поля с другими значениями / нужно для апдейта
     * @param null $data
     * @return array
     */
    protected function onlyNewValue($data=null)
    {

        //var_dump($this->result);
        //todo: может ограничить список полей которые проверяем?
        $changed_fields = $data ? $data : $this->first_result;
        foreach ($changed_fields as $key => $value)
        {
            //if(isset($this->data[$key])) echo $key.': '.$this->data[$key].' = '.$value."\n";
            if( isset($this->data[$key]) && $this->data[$key] == $value )
                unset($this->data[$key]);
        }

        return $this->data;
    }

    /** Обновляет элмент по заданым в методом Where условиям
     * @return $this
     */
    protected function updateWhere()
    {
        $this->affected = \sDb::query($this->make_query(self::UPDATE));

        return $this;
    }


    /** обновляет элемент если он был ранее найден в базе
     * @return $this
     * @throws \Exception - если нет ключей по которым обновлять
     *
     */
    protected function update()
    {
        //todo: отработать ситуация когда обновлять ничего не надо


        // если задано WHERE то обновлять то что просит

        // приготовим
        $update_fields = $this->makeSql($this->onlyNewValue());

        $sub_key_fields = $this->makeSql($this->selected_identifier_or,' || ');
        $main_key_fields = $this->makeSql($this->selected_identifier_and,' && ');

        if($sub_key_fields && $main_key_fields)
            $key_fields = $main_key_fields . ' && ('.$sub_key_fields.' )';
        elseif (!$sub_key_fields && $main_key_fields)
            $key_fields = $main_key_fields;
        elseif ($sub_key_fields && !$main_key_fields)
            $key_fields = $main_key_fields;
        else {
            d($this);
            throw new \Exception('Нет ключей');
        }
            //die('Error: Нет основного ключа! ');

        //todo: обновлять только то что имзенилось!

        if($update_fields) {
        // обновим
        \sDb::query("UPDATE ? SET $update_fields WHERE $key_fields ",$this->table);


        $this->last_query = "UPDATE {$this->table} SET $update_fields WHERE $key_fields ";
        $this->last_action = self::UPDATE;
        } else {

            $this->last_action = self::SKIP;
        }

        return $this;

    }

    /**
     * @param $data
     * @param string $delimiter
     * @return string
     */
    protected function makeSql($data, $delimiter=', ')
     {
         $sql_fields_array = [];
         foreach ($data as $key => $val)
         {
                 $sql_fields_array[] = " `".$key."` = '" . \sDB::escape($val) . "' ";
         }

         return implode($delimiter, $sql_fields_array);

     }

    /**
     * @return bool
     */
    protected function haveIt() {


        // если нет идентификаторов то вернем false
        if(!$this->selected_identifier_and && !$this->selected_identifier_or)
            return false;

         foreach ($this->selected_identifier_or as $key => $val) {
            $this->orWhere($key,$val);
         }

         foreach ($this->selected_identifier_and as $key => $val) {
             $this->where($key,$val);
         }

         $this->get();

        return $this->total_count ? true : false;
     }


     /////////////////////////////////////////////////////// READ ////////////////////////////////////////////////////////////////////////


    /**
     * @param int $method
     * @return string
     */
    protected function make_query($method=1)
    {
        /// SELECT
        $query = $method == self::SELECT ? 'SELECT ' : 'DELETE '  ;

        if($this->sql_select && $method == self::SELECT)
            $query .= ' SQL_CALC_FOUND_ROWS '. implode(', ', $this->sql_select)."\n";
        elseif($method == self::SELECT)
            $query .= " SQL_CALC_FOUND_ROWS * "."\n";

        /// FROM
        if(!$this->db)
            $query .= " FROM ".$this->table .' as mt '. PHP_EOL;
        else
            $query .= " FROM {$this->db}.".$this->table ." as mt ". PHP_EOL;

        // JOIN
        if($this->sql_left_join)
            $query .= implode(PHP_EOL, $this->sql_left_join).PHP_EOL;

        if($this->sql_inner_join)
            $query .= implode(PHP_EOL, $this->sql_inner_join).PHP_EOL;


        if($this->sql_where || $this->sql_orWhere)
            $query .= ' WHERE ';
        // WHERE
        if($this->sql_where && $this->sql_orWhere)
            $query .= implode(' && ',$this->sql_where) . ' && ( '. implode(' && ',$this->sql_orWhere).' )'."\n";
        elseif($this->sql_where && !$this->sql_orWhere)
            $query .= implode(' && ',$this->sql_where) ."\n";
        elseif(!$this->sql_where && $this->sql_orWhere)
            $query .= implode(' || ',$this->sql_orWhere) ."\n";


        // GROUP
        if($this->sql_group)
            $query .= 'GROUP BY '.implode(',',$this->sql_group)."\n";


        // ORDER
        if($this->sql_order)
            $query .= 'ORDER BY '.implode(',',$this->sql_order)."\n";

        // LIMIT
        if($method == self::SELECT)
            $query .= "LIMIT {$this->sql_start}, {$this->sql_limit} ";

        return $query;

    }

    /**
     * @param int $start
     * @param int $limit
     * @return $this
     */
    public function limit($start=0, $limit=10000)
    {
        $this->sql_start = $start;
        $this->sql_limit = $limit;
        return $this;
    }

    /**
     *
     */
    public function getCached()
    {
      //
    }

    /**
     * @param bool $cache
     * @return $this
     */
    public function get($cache=false)
    {
        //dd($this->make_query(self::SELECT));
        $this->result = \sDb::get_rows($this->make_query());
        $this->mutatorGet();
        $this->first_result = isset($this->result[0]) ? $this->result[0] : null;
        $this->total_count = \sDb::get_first("SELECT FOUND_ROWS() ");

        return $this;
    }

    public function count($cache=false)
    {
        $this->select(['COUNT(*)']);
        return \sDb::get_first($this->make_query());

    }

    /**
     * @param $field
     * @param $value
     * @param string $compare
     * @return $this
     */
    public function where($field, $value, $compare='=')
    {
        $this->makeWhere('where',$field,$value,$compare);
        return $this;
    }

    /**
     * @param $type
     * @param $field
     * @param $value
     * @param $compare
     */
    protected function makeWhere($type, $field, $value, $compare)
    {
        $type = 'sql_'.$type;

        // нужно зафиксировать где то основной ключ на всякий
        if($field == $this->main_key)
            $this->selected_identifier_and[$this->main_key] = $value;

        // если алас предан то подставим иначе подставим основной таблицы алиас
        $field_with_alias = (!strpos($field,'.')) ? 'mt.'.$field : $field;

        if($value === null) // сравнение с NULL
            $this->{$type}[] = " $field_with_alias $compare NULL ";
        elseif(is_array($value)) // IN or NOT IN
            $this->{$type}[] = " $field_with_alias $compare (".implode(',',$value).") ";
        else
            $this->{$type}[] = " $field_with_alias $compare '". \sDB::escape($value)."' ";
    }

    /**
     * @param $key
     * @return $this
     */
    public function groupBy($key)
    {
        $this->sql_group[] = $key;
        return $this;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getField($key)
    {
        if(isset($this->first_result[$key]))
            return $this->first_result[$key];
        else
            return null;
    }

    public function cache($name,$time=300)
    {
        return $this;
    }

    /**
     * @param null $key - ключ
     * @param null $only_value - сделать одномерный массив ключ - значение
     * @return array
     */
    public function toArray($key=null,$only_value=null)
    {
        $result = [];
        if(!$key)
            if(count($this->result) == 1)
                $result = $this->first_result;
            else
                $result =  $this->result;
        else
            foreach ($this->result as $value)
            {
                $result[$value[$key]] = $only_value ? $value[$only_value] : $value;
            }

        return $result;
    }

    public function first()
    {
        return $this->first_result;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        return $this->make_query();
    }

    /**
     * @param $table
     * @return $this
     */
    public function leftJoin($table,$alias=null)
    {
        $this->join('LEFT',$table,$alias);
        return $this;
    }

    /**
     * @param $on
     * @param $table
     */
    protected function makeOn($on, $table)
    {
        $on_or = [];
        $on_group = [];
        $sql = '';

        foreach ($on as $index => $condition)
        {
                if (count($condition) == 1) {  // это просто список условий
                    foreach ($condition as $key => $value) {
                        $on_or[$index] = " $table.`$key` = '$value' ";
                    }
                } else {
                    foreach ($condition as $key => $value) {
                        $on_group[$index] .= " && ";
                        $on_group[$index] .= " $table.`$key` = '$value' ";
                    }
                }
        }

        if($on_or)
            $sql_or = implode(' || ',$on_or);
        if($on_group)
            $sql_group = implode(' || ',$on_group);
    }

    /**
     * @param $type
     * @param $table
     * @param $alias
     */
    protected function join($type, $table,$alias=null)
    {
        if(!$alias)
            $alias = 'jt';


        if(isset($this->linked_fields[$table])) {
            $main_key = $this->linked_fields[$table][0];

            $foreign_key = isset($this->linked_fields[$table][1]) ? $this->linked_fields[$table][1] : $this->linked_fields[$table][0];

            $this->sql_left_join[] = "$type JOIN $table as $alias ON $alias.$foreign_key = mt.$main_key ";
        }
    }

    /**
     * @param $table
     * @return $this
     * @return $alias
     */
    public function innerJoin($table,$alias=null)
    {
        $this->join('INNER',$table,$alias);
        return $this;
    }

    /**
     * @param $val1
     * @param $val2
     * @param string $compare
     * @return $this
     */
    public function orWhere($val1, $val2, $compare='=')
    {
        $this->makeWhere('where',$val1,$val2,$compare);
        return $this;
    }

    /**
     * @param $fields
     * @return $this
     */
    public function select($fields)
    {
        $this->sql_select = $fields;
        return $this;
    }

    public function orderBy($field,$sort='ASC')
    {
        $this->sql_order[] = " $field $sort ";
        return $this;
    }

    protected function mutate_data()
    {

        // json
        if($this->json_fields) {
            foreach ($this->data as $key => &$one_data) {
                if (in_array($key, $this->json_fields))
                    $one_data = json_encode($one_data);
            }
        }

        // прочие мутаторы
        $this->mutatorSet();

        //var_dump($this->data);
    }

    protected function mutate_result()
    {
        // json
        if($this->json_fields)
            foreach ($this->result as $key => &$one_result)
            {
                if(in_array($key,$this->json_fields))
                    $one_result = json_decode($one_result);
            }

        // прочие мутаторы
        $this->mutatorGet();
    }


    /** Запускает мутаторы для колонок
     * примеры setVendor_id  getVendor_id
     * @param $key
     */
    protected function mutatorGet()
    {
        $methods = $this->getMethods('onGet',get_class($this));


        foreach ($methods as $method)
        {
            $field = substr($method,3);
            foreach ($this->result as &$result) {
                $result[$field] = $this->{$method}($result[$field]);
            }
        }
    }

    protected function mutatorSet()
    {
        $methods = $this->getMethods('onSet',get_class($this));

        foreach ($methods as $method)
        {
            $field = substr($method,3);
            $this->data[$field] = $this->${$method}($this->data[$field]);
        }
    }

    /**
     * запускает после создания нового элемента все функции с префиксом afterInsert
     */
    protected function afterInsert()
    {
        $methods = $this->getMethods('afterInsert');

        foreach ($methods as $method)
        {
            if($method == __FUNCTION__)
                continue;

            $this->{$method}();
        }
    }

    protected function afterDelete()
    {
        $methods = $this->getMethods('afterDelete');

        foreach ($methods as $method)
        {
            if($method == __FUNCTION__)
                continue;

            $this->{$method}();
        }
    }


    protected function getMethods($name)
    {
        $result = [];

        $all_methods = get_class_methods($this);


        foreach ($all_methods as $method)
        {
            if( substr($method,0,strlen($name)) == $name)
                $result[] = $method;
        }

        return $result;
    }



}