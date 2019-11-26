<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-21
 * Time: 10:41
 */

namespace Oc;


use Base\Model;

class ocProductDescription extends Model
{
    protected $table = 'oc_product_description';
    protected $allowed_fields =
        [
            'product_id',
            'language_id',
            'name',
            'description',
            'short_description',
            'meta_title',
            'meta_description',
            'meta_keyword',
            'meta_h1'
        ];

    protected $main_key = 'product_id';
}

