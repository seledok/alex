<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-12-05
 * Time: 10:44
 */

namespace Oc;


use Base\Model;

class ocAttributeDescription extends Model
{
    protected $table = 'oc_attribute_description';
    protected $allowed_fields = ['language_id','name','attribute_id'];
    protected $main_key = 'attribute_id';
}