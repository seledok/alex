<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-24
 * Time: 11:31
 */

namespace Product;


use Base\Model;

class ocProductRelated extends Model
{

    protected $table = 'oc_product_related';
    protected $allowed_fields = [];
    protected $main_key = 'product_id';
}