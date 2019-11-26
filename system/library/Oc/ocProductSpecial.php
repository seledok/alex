<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-24
 * Time: 11:32
 */

namespace Product;


use Base\Model;

class ocProductSpecial extends Model
{
    protected $table = 'oc_product_special';
    protected $allowed_fields = [];
    protected $main_key = 'product_special_id';
}