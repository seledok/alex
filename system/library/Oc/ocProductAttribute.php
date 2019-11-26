<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-24
 * Time: 11:12
 */

namespace Product;


use Base\Model;

class ocProductAttribute extends Model
{
    protected $table = 'oc_product_attribute';
    protected $allowed_fields = [];
    protected $multi_identifier = ['product_id','attribute_id'];
    protected $main_key = 'product_id';
}