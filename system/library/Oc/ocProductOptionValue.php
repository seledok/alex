<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-24
 * Time: 11:30
 */

namespace Product;


use Base\Model;

class ocProductOptionValue extends Model
{
    protected $table = "oc_product_option_valure";
    protected $allowed_fields = [];
    protected $main_key = 'product_option_value_id';

}