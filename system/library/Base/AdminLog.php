<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-21
 * Time: 11:57
 */

namespace Base;


class AdminLog extends Model
{
    protected $table = '1c_admin_log';
    protected $allowed_fields =
        [
        'datetime',
            'userid',
            'item_type',
            'item_id',
            'action',
            'data',
            'info',
            'price',
            'vendor_id'
        ];

    protected $json_fields = ['data'];
}