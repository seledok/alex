<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-24
 * Time: 11:14
 */

namespace Product;


use Base\Model;

class ocProductImage extends Model
{
    protected $table = 'oc_product_image';
    protected $allowed_fields = [];
    protected $main_key = 'product_image_id';

}