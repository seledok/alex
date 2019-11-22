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



    public function __construct($registry)
    {


    }

    public function parseXml($path,$vendor_id = 3)
    {

        $this->vendor_id = $vendor_id;
        $reader = new XMLReader();
        $reader->open($path);

        $this->category_array = $this->shipper->get_categoryes_by_key($vendor_id);


        while (@$reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT) {
                //var_dump($reader->localName);
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

        $vendor['guid'] = $product['model']  = $xml->Ид->__toString();
        $vendor['ean'] = $product['ean']  =$xml->Штрихкод->__toString();
        $vendor['vendor_key'] = $product['ean']  =$xml->Код->__toString();
        $vendor['name'] = $product['name'] = $xml->Наименование->__toString();
        if(isset($xml->Группы->Ид)) $product['category'] = $xml->Группы->Ид->__toString();
        if(isset($xml->Артикул)) $vendor['sku'] = $product['sku'] = $vendor['sku'] = $xml->Артикул->__toString();

        if(isset($xml->Группы->Ид) && isset($this->category_array[$xml->Группы->Ид->__toString()])) $vendor['cat_id'] = $this->category_array[$xml->Группы->Ид->__toString()];

        foreach ($xml->ЗначенияРеквизитов->ЗначениеРеквизита as $rekv) {
            if ($rekv->Наименование->__toString() == 'Код') $vendor['vendor_key'] = $product['upc'] = $rekv->Значение->__toString();
        }

        $vendor['quantity'] = null;

        $this->shipper->import($vendor);

    }


    public function xmlOffer($inner)
    {

       // echo 'offer';

        $xml = simplexml_load_string($inner);
        $vendor['vendor_id'] = $this->vendor_id; // ГОТТИ
        $vendor['guid'] = $xml->Ид->__toString();
        //$product['ean'] = $xml->Штрихкод->__toString();
        $vendor['name'] = $xml->Наименование->__toString();
        $vendor['quantity'] = (int)$xml->Количество->__toString();


        //var_dump($this->shipper->getPrices($this->vendor_id));
        $prices = array_flip($this->shipper->getPrices($this->vendor_id));
        $storage = $this->shipper->getStorage($this->vendor_id);


        foreach ($xml->Цены->Цена as $price) {
           /** ждем своего часа */

           if($prices) {
               if (isset($prices[$price->ИдТипаЦены->__toString()])) {
                   $vendor[$prices[$price->ИдТипаЦены->__toString()]] = (float)$price->ЦенаЗаЕдиницу->__toString();
                  // var_dump($vendor['guid'].': '.$prices[$price->ИдТипаЦены->__toString()].' - '.$price->ЦенаЗаЕдиницу->__toString());
               }
           } else {
               $vendor['price'] = (float)$price->ЦенаЗаЕдиницу->__toString();
           }
        }

        //if(isset($vendor['price_red'])) var_dump($vendor);


        if($storage) {
            $delivery_array = array();
            foreach ($xml->Склад as $sklad) {

                // задача: получить наличе по самом ближнему складу
                $delivery_array[$storage[(string)$sklad['ИдСклада']]] = (int)$sklad['КоличествоНаСкладе'];

            }
        }

        if(!empty($delivery_array)) {

            ksort($delivery_array);

            //var_dump($storage);
            //var_dump($delivery_array);

            foreach ($delivery_array as $delivery => $quantity) {
                if ($quantity > 0) {
                    $vendor['delivery'] = $delivery;
                    $vendor['quantity'] = $quantity;
                    break;
                }
            }

            if (!isset($vendor['delivery'])) {
                $this->null_cnt++;
                $vendor['quantity'] = 0;
                $vendor['delivery'] = 100;
            }
        } else {
            $vendor['delivery'] = 0;
        }

        // для админа

       // $this->sync->setB2bPrice($product['product_id'],$price_6);




        $result_cnt = $this->shipper->import($vendor);
        //echo  $result_cnt.' ';
        if(isset($this->{$result_cnt})) $this->{$result_cnt}++;


    }

    public function xmlCategory($xml)
    {

        $xml = simplexml_load_string($xml);


        $cat['xml'] = $xml->Ид->__toString();
        $cat['name'] = $xml->Наименование->__toString();

        $this->shipper->vendor_id = $this->vendor_id;
        $this->shipper->get_category_id($cat['name'],$cat['xml']);

        //echo $cat['xml'].' - '.$cat['name'];

    }
}