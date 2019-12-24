<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-12-12
 * Time: 10:08
 */

namespace Oc;


use Base\Model;

class ocCustomerGroup extends Model
{
    protected $allowed_fields = ['approval','sort_order'];
    protected $table = 'oc_customer_group';
    protected $main_key = 'customer_group_id';
}