<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-12-12
 * Time: 10:08
 */

namespace Oc;


use Base\Model;

class ocCustomerGroupDescription extends Model
{
    protected $allowed_fields = ['language_id','name','description','customer_group_id'];
    protected $table = 'oc_customer_group_description';
    protected $main_key = 'customer_group_id';
}