<?php

/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 07.12.16
 * Time: 15:00
 */

class importXml
{
    protected $manufact;
    public $add_cnt = 0;
    public $skip_cnt = 0;
    public $price_cnt = 0;
    public $upd_cnt = 0;
    public $null_cnt = 0;
    public $params = array();
    private $category_array = array();
    public $status = '';
    public $type = 0;
    public $import_time;
    protected $parent_cat=array(0,0,0,0,0,0,0);




    static function xml()
    {
        return new static();
    }

    public function parseXml($path)
    {

        //dd($path);

        $reader = new XMLReader();
        $reader->open($path);

        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT) {
                // если находим объект
                if($reader->localName == 'КоммерческаяИнформация') $idate = $reader->getAttribute('ДатаФормирования');
                if($reader->localName == 'Классификатор' || $reader->localName == 'ПакетПредложений') {
                    if($reader->getAttribute('СодержитТолькоИзменения')=='false')
                    {
                        echo  date('H:m:s').' Загружаем полный '.$reader->localName.' от '.$idate. "\r\n";
                        if($reader->localName == 'ПакетПредложений') $this->type = 1;
                    }
                    else
                    {
                        echo date('H:m:s').' Загружаем частичный '.$reader->localName.' от '.$idate. "\r\n";
                        if($reader->localName == 'ПакетПредложений') $this->type = 0;
                    }
                }
                elseif ($reader->localName == 'Товар') $this->xmlProduct($reader->readOuterXML());
                elseif ($reader->localName == 'Предложение') $this->xmlOffer($reader->readOuterXML());
                elseif ($reader->localName == 'Документ') $this->xmlOrder($reader->readOuterXML());

            }

        }

        return $this->import_time;

    }



    public function xmlOrder($inner)
    {

        $xml = simplexml_load_string($inner);

        $number = $xml->Номер->__toString();
        $na = explode('-',$number);

        if(count($na) < 2) return;
        $data['shop'] = $na[0];
        $data['order_id'] = (int)$na[1];

        echo "Получен заказ ".$data['order_id']." в магазин ".$data['shop']."\n";

        foreach ($xml->Товары->Товар as $product) {
            $data['products'][$product->Ид->__toString()]['quantity'] = (int)$product->Количество->__toString();
            $data['products'][$product->Ид->__toString()]['name'] = $product->Наименование->__toString();
            $data['products'][$product->Ид->__toString()]['price'] = (float)$product->ЦенаЗаЕдиницу->__toString();
        }


        foreach ($xml->ЗначенияРеквизитов->ЗначениеРеквизита as $rekv) {
            if ($rekv->Наименование->__toString() == 'Статуса заказа ИД')
            {
                $data['order_status_id'] = (int)$rekv->Значение->__toString();
            }
            if ($rekv->Наименование->__toString() == 'Номер по 1С')
            {
                $data['1c_number'] = trim($rekv->Значение->__toString());
            }

            if ($rekv->Наименование->__toString() == 'Отменен')
            {
                $data['order_info']['canceled'] = ($rekv->Значение->__toString()=='true') ? true : false;
            }

            if ($rekv->Наименование->__toString() == 'Проведен')
            {
                $data['proveden'] = $rekv->Значение->__toString() == 'false' ? false : true;
            }
            if ($rekv->Наименование->__toString() == 'ПометкаУдаления')
            {
                $data['deleted'] = $rekv->Значение->__toString() == 'false' ? false : true;
            }
            if ($rekv->Наименование->__toString() == 'Оплачен')
            {
                $data['payd'] = $rekv->Значение->__toString() == 'false' ? false : true;
            }
            if ($rekv->Наименование->__toString() == 'Отгружен')
            {
                $data['shiped'] = $rekv->Значение->__toString() == 'false' ? false : true;
            }

        }


        // установим статус
        if($data['order_status_id'] == 16 && !$data['shiped'] ) $data['order_status_id'] = 16;
        elseif($data['deleted']) $data['order_status_id'] = 16;
        elseif($data['shiped']) {
            if($data['shop']=='opt_answare') {
                $data['order_status_id'] = 20;
            } else {
                $data['order_status_id'] = 13;
            }
        }
        elseif(!$data['proveden']) $data['order_status_id'] = 7;
        elseif ($data['proveden']) {
            if($data['order_status_id'] == 8) $data['order_status_id'] = 8;
            elseif ($data['order_status_id'] == 9) { // ожидает оплаты
                if($data['payd']) $data['order_status_id'] = 10;
                else $data['order_status_id'] = 9;
            }
            elseif ($data['order_status_id'] == 11) $data['order_status_id'] = 11;
        }


        //var_dump($data);

            // найдем выбранный заказ и  обновим
            $order_sync = new order_sync($this->registry);
            $order_sync->orderSync($data);


    }

    public function xmlProduct($inner)
    {

        $xml = simplexml_load_string($inner);

        \Oc\Product::model()->import(
          [
              'model'=>$xml->Код->__toString(),
              'upc'=>$xml->Ид->__toString(),
              'ean'=>$xml->Штрихкод->__toString(),
              'name'=>$xml->Наименование->__toString(),
              'sku'=>$xml->Артикул->__toString(),
              'status'=>1
          ]
        );

    }


    public function xmlOffer($inner)
    {
        $xml = simplexml_load_string($inner);
        \Oc\Product::model()->import(
            [
                'model'=>$xml->Код->__toString(),
                'upc'=>$xml->Ид->__toString(),
                'quantity'=>(int)$xml->Количество->__toString(),
                'price'=>(int)$xml->Количество->__toString(),
            ]
        );

        //todo: конвретер тип цен в колонки у нас



    }

}