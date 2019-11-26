<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-24
 * Time: 11:13
 */

namespace Product;


use Base\Model;

class ocProductDiscount extends Model
{
    protected $table = 'oc_product_discount';
    protected $allowed_fields = [];
    //protected $multi_identifier = ['product_id'm];
    protected $main_key = 'product_discount_id';
}