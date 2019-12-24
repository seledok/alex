<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-24
 * Time: 11:29
 */

namespace Product;


use Base\Model;

class ocProductOption extends Model
{
    protected $table = 'oc_product_option';
    protected $allowed_fields = [];
    protected $main_key = 'oc_product_option';
}