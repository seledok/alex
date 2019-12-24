<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-24
 * Time: 11:12
 */

namespace Oc;


use Base\Model;

class ocProductAttribute extends Model
{
    protected $table = 'oc_product_attribute';
    protected $allowed_fields = ['attribute_id','text'];
    protected $multi_identifier = ['product_id','attribute_id'];
    //protected $main_key = 'product_id';
}