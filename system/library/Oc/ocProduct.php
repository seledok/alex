<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-21
 * Time: 10:41
 */

namespace Oc;


use Base\Model;

class ocProduct extends Model
{
    protected $table = 'oc_product';
    protected $allowed_fields =
        [
            'model',
            'sku',
            'upc',
            'ean',
            'jan',
            'isbn',
            'mpn',
            'quantity',
            'stock_status_id',
            'image',
            'manufacturer_id',
            'shipping',
            'price',
            'price_in',
            'date_available',
            'weight',
            'length',
            'height',
            'subtract',
            'minimum',
            'status',
            'rrc',
            'warranty',
        ];

    protected $main_key = 'product_id';

    protected $linked_fields = ['oc_product_description'=>['product_id']];

    protected function afterInsertStore()
    {
        toStore::link('product',$this->data[$this->main_key]);
    }

    protected function afterDeleteStore()
    {
        toStore::unlink('product',$this->selected_identifier_and[$this->main_key]);
    }
}

