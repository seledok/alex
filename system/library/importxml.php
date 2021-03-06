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
    protected $import_time;
    protected $parent_cat=array(0,0,0,0,0,0,0);
    protected $customer_groups;
    public $have_offers;




    function __construct()
    {
        $this->import_time = time();
        // получим из кеша
        //$this->customer_groups = @file_get_contents(DIR_CONFIG.'customer_groups.json');
    }

    public function import_time()
    {
        return $this->import_time;
    }


    static function xml()
    {
        return new static();
    }

    public function parseXml($path)
    {

        $this->have_offers = false;

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
                elseif ($reader->localName == 'ТипыЦен') $this->xmlPrice($reader->readOuterXML());
                elseif ($reader->localName == 'Товар') $this->xmlProduct($reader->readOuterXML());
                elseif ($reader->localName == 'Предложение') $this->xmlOffer($reader->readOuterXML());
                elseif ($reader->localName == 'Документ') $this->xmlOrder($reader->readOuterXML());

            }

        }

        return $this->import_time;

    }



    protected function xmlOrder($inner)
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

    protected function xmlProduct($inner)
    {

        //dd($this->customer_groups);
        $xml = simplexml_load_string($inner);

        /*
         <ЗначенияСвойств>
					<ЗначенияСвойства>
						<Ид>05ac61b1-2854-11ea-9e1e-00155d466405</Ид>
						<Значение>1,62</Значение>
					</ЗначенияСвойства>
				</ЗначенияСвойств>
         */

        $minimum = 0;
        foreach($xml->ЗначенияСвойств->ЗначенияСвойства as $property)
        {
            //var_dump($property);
            if($property->Ид->__toString() == '05ac61b1-2854-11ea-9e1e-00155d466405')
                $minimum = (float) str_replace(',','.',$property->Значение->__toString());
        }


        //echo 'min: '.$minimum . PHP_EOL;

        if(! \Oc\ocProduct::model()
            ->orWhere('upc',$xml->Ид->__toString())
            ->orWhere('model',$xml->Код->__toString())
            ->count()
        )
            \Oc\Product::model()->import(
              [
                  'model'=>$xml->Код->__toString(),
                  'upc'=>$xml->Ид->__toString(),
                  'ean'=>$xml->Штрихкод->__toString(),
                  'name'=>$xml->Наименование->__toString(),
                  'sku'=>$xml->Артикул->__toString(),
                  'minimum'=>$minimum,
                 // 'status'=>1
              ]

            );

        // обновим минимум всегда // потом можно убрать
        \Oc\Product::model()->import(
            [
                'model'=>$xml->Код->__toString(),
                'upc'=>$xml->Ид->__toString(),
                'minimum'=>$minimum,
                // 'status'=>1
            ]
        );


    }


    protected function xmlOffer($inner)
    {
        $this->have_offers = true;
        $xml = simplexml_load_string($inner);
        $product_id = \Oc\Product::model()->import(
            [
                //'model'=>$xml->Код->__toString(),
                'upc'=>$xml->Ид->__toString(),
                'quantity'=>(float)$xml->Количество->__toString(),
                //'price'=>(int)$xml->Количество->__toString(),
                'status'=>1,
                'import_time' => $this->import_time,
                'stock_status_id' => 7
            ]
        );

        // очистим скидки
        \Oc\ocProductDiscount::model()
            ->where('product_id',$product_id)
            ->delete();


       // d($product_id);

        // дефолтная цена
        $default_customer_group = sDB::get_one("SELECT value FROM oc_setting WHERE `key` = 'config_customer_group_id' ");


        // добаивим скидки
        foreach($xml->Цены->Цена as $price)
        {
            $Price_id = $price->ИдТипаЦены->__toString();
            if(isset($this->customer_groups[$price->ИдТипаЦены->__toString()])) {
                if ($this->customer_groups[$price->ИдТипаЦены->__toString()] == $default_customer_group)
                    \Oc\ocProduct::model()
                        ->where('product_id', $product_id)
                        ->set('price', (float)$price->ЦенаЗаЕдиницу->__toString())
                        ->save();

                    Oc\ocProductDiscount::model()
                        ->set('product_id', $product_id)
                        ->set('price', (float)$price->ЦенаЗаЕдиницу->__toString())
                        ->set('customer_group_id', $this->customer_groups[$price->ИдТипаЦены->__toString()])
                        ->set('quantity', 1)
                        ->save();



                //d($price->ЦенаЗаЕдиницу->__toString());
            } else {
                echo 'No '.$price->ИдТипаЦены->__toString().' price group ! '.PHP_EOL;
                d($this->customer_groups);
            }


        }


    }

    protected function xmlPrice($inner)
    {
        $groups = simplexml_load_string($inner);



        foreach ($groups as $xml) {


            $customer_group_id = \Oc\ocCustomerGroupDescription::model()
                ->where('name', $xml->Наименование->__toString())
                ->get()
                ->getField('customer_group_id');

            if ($customer_group_id)
                $this->customer_groups[$xml->Ид->__toString()] = $customer_group_id;
            else {
                $customer_group_id = \Oc\CustomerGroup::model()->import(
                    [
                        'name' => $xml->Наименование->__toString(),
                        'language_id' => 1,
                        'approval' => 1
                    ]
                );
                $this->customer_groups[$xml->Ид->__toString()] = $customer_group_id;
            }
        }



    }

    public function __destruct()
    {
        /*
        file_put_contents(
            DIR_CONFIG.'customer_groups.json',
            json_encode($this->customer_groups)
        );
        */
    }

}