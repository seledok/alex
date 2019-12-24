<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-05
 * Time: 11:08
 */

namespace Base;

use Vendor\Product;
use Vendor\Column;
use Vendor\vBuffer;
use Vendor\vColumn;
use Vendor\vVendor;

class BufferRead
{
    protected $vendor_id;

    protected $last_category;
    protected $column2field = [];
    protected $name2field = [];
    protected $float_fields = ['price','price_out','price_rrc','length','width','height','weight'];
    protected $int_fields = ['delivery','quantity','transit'];

    protected $config;
    protected $default_quantity;

    protected $product_data;
    protected $row_data;

    protected $last_row_not_category = true;


    protected $rate;




    public function __construct($vendor_id)
    {
        $this->vendor_id = $vendor_id;
        // получим настройки полей
        $this->config = new Column($vendor_id);

    }

    public function start()
    {
        $start = 0;
        $limit = 50;
        $product_model = new Product($this->vendor_id);

        $vbuffer = new vBuffer();

        // пропуск первого листа
        if($this->config['skip_sheet'])
            $vbuffer->where('sheet_id',0,'!=');

        do {
            // начинаем перебирать
            $rows = $vbuffer
                ->where('vendor_id', $this->vendor_id)
                ->limit($start, $limit)
                ->get()
                ->toArray();

            $start = ($start + $limit) < $vbuffer->total_count ? $start + $limit : null;

            // получим курсы долларов и прочее
            $this->prepare();

            foreach ($rows as $row)
            {
                $this->product_data =
                    [
                        'vendor_id' => $this->vendor_id,
                        'category' =>$this->last_category,
                    ];


                // выудим инфу из строки
                $this->startMethods('getter',$row);

                // получим данные
                $this->parse($row);

                // преобразуем полученные данные
                $this->startMethods('mutator',$this->product_data);

                // загрузим в vendor_product
                $product_model->import($this->product_data);

                // если есть еще одна конка загрузим и ее
                if($this->config->other_vendor) {
                    $this->otherVendor($row);
                    $product_model->import($this->product_data);
                }
            }

        } while ($start !== null);
    }

    protected function otherVendor($row)
    {
        if($this->config->other_vendor && !empty($row[$this->config->other_quantity]) ) {
            $this->product_data['vendor_id'] = $this->config->other_vendor;
            $this->product_data['quantity'] = $row[$this->config->other_quantity];
            if($this->config->other_price)
                $this->product_data['price'] = round(str_replace(' ','',$row[$this->config->other_price]) * $this->rate);
        }
    }

    protected function prepare()
    {
        // ye
         $this->currency = $this->config->get_currency();
    }

    protected function parse($row)
    {
        $this->product_data = ['vendor_id' => $this->vendor_id];
        if($this->last_category)
            $this->product_data['category'] = $this->last_category;

        foreach ($row as $index => $value)
        {
            if($field_name = $this->config->index2field($index))
                $this->product_data[$field_name] = $value;
        }


    }

    protected function mutatorRate()
    {
        if($this->product_data['currency'] == 'RUR')
            $this->rate = 1;
        else
            $this->rate = $this->config->get_rate($this->product_data['currency']);
    }

    protected function mutatorQuantity($quantity)
    {
        if( // если количество пустое
            isset($this->product_data['quantity'])   &&
            $this->product_data['quantity'] == ''
        )
            $this->product_data['quantity'] = 0;
        elseif (!isset($this->product_data['quantity']))
            $this->product_data['quantity'] = $this->default_quantity;
        elseif ( isset($this->product_data['quantity']))
            $this->product_data['quantity'] = $this->config->convertQuantity($this->product_data['quantity']);


        if(isset($this->config->kol[$quantity])) {
            $this->product_data['quantity'] = $this->config->kol[$quantity];
            $this->product_data['exact'] = 0;
        } else {
            $this->product_data['exact'] = 1;
            $this->product_data['quantity'] = \hlp::toInt($this->product_data['quantity']);
        }


    }

    protected function mutatorEan()
    {
        if(isset($this->product_data['ean'])) {
            $this->product_data['ean'] = substr(preg_replace("/[^0-9]/", '', $this->product_data['ean']),0,13);
        }
    }

    protected function startMethods($key,$data=null)
    {
        $methods = \hlp::getMethods($key,__CLASS__);

        foreach ($methods as $method)
        {
            $this->${$method}($data);
        }
    }

    protected function getterCategory($row)
    {
        $maybe_category_name = null;

        // если задана категория то
        if($this->config->category_field && !empty($row[$this->config->category_field])) {

            // маркер категорий
            if (!empty($this->config->category_marker)) {
                foreach ($this->config->category_marker as $key => $is_val) {
                    if (trim($row[$key]) != $is_val)
                        $maybe_category_name = null;
                }
                // иначе проверим что пустые поля совпали
            } else {
                foreach ($this->config->category_empty as $empty_pole) {
                    if (trim($row[$empty_pole]))
                        $maybe_category_name = null;
                }
            }

            if ($maybe_category_name && (!$this->config->category_top || $this->last_row_not_category) ) {
                $this->last_category = $maybe_category_name;
                $this->last_row_not_category = false;
            } else {
                $this->last_row_not_category = true;
            }

        } else $this->last_row_not_category = true;

    }


    protected function getterIdByTitle($row)
    {
        if($this->config->byId(''))
        foreach ($row as $index => $cell)
        {
            // елси колонка = чему то из массива то установим ее ИД полю
            if($field = $this->config->value2field($cell))
                $this->config->numbers[$field] = $index;
        }
    }

    /////////////////////////// мутаторы ////////////////////////////////////

    protected function mutatorDelivery()
    {
        //$this->product_data['delivery'] =

        // сроки доставки / не понял суть
        if ($this->config->delivery_kol) {

            foreach ($this->config->delivery_kol as $col => $delivery) {
                $is_delivery = $row[$col];
                //todo - что то сделать!
                if ($is_delivery) { // каждое значение какой то срок
                    if (is_array($delivery)) {
                        if (isset($delivery[$is_delivery]))
                            $this->product_data['delivery'] = $delivery[$is_delivery];
                    } else { // просто срок поставки в колонке в дн
                        $this->product_data['delivery'] = $delivery;
                        break;
                    }
                }
            }
            // если не нашли сроков а надо было то не грузим товар
            if (!isset($this->product_data['delivery']))
                $this->product_data = [];
        }

        // лимит на строк доставки
        if (isset($this->product_data['delivery'])) {
            if ($this->config->delivery_limit !== null && intval($this->product_data['delivery']) > $this->config->delivery_limit)
                $this->product_data['quantity'] = 0;

            $this->product_data['delivery'] = \hlp::convert2day($this->product_data['delivery']);
        } else
            $this->product_data['delivery'] = 0;
    }

    protected function mutatorDrop()
    {
        if (!empty($this->config->drop)) {
            $word = $this->config->drop['name'];
            $match = preg_match('/' . $word . '/', $this->product_data['name'], $matches);
            if ($match) {
                $this->product_data = [];
            }
        }

        if (isset($this->product_data['price'] ) && intval($this->product_data['price']) == 999999)
            $this->product_data = [];
    }


    protected function mutatorInteger()
    {
        // float
        foreach ($this->float_fields as $field_name)
        {
            if(isset($this->product_data[$field_name]))
                $this->product_data[$field_name] = \hlp::toFloat($this->product_data[$field_name]);
        }

        // int
        foreach ($this->int_fields as $field_name)
        {
            if(isset($this->product_data[$field_name]))
                $this->product_data[$field_name] = \hlp::toInt($this->product_data[$field_name]);
        }


    }

    protected function mutatorPrice()
    {

        if(isset($this->product_data['price']))
            $this->product_data['price'] =  round($this->product_data['price']) * $this->rate;

        if($this->config->plus)
            $this->product_data['price'] = $this->product_data['price'] * (1 + $this->config->plus);

    }


}