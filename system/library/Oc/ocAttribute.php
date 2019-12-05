<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-12-05
 * Time: 10:43
 */

namespace Oc;


use Base\Model;

class ocAttribute extends Model
{

    protected $table = 'oc_attribute';
    protected $allowed_fields = ['attribute_group_id','sort_order'];
    protected $main_key = 'attribute_id';
}