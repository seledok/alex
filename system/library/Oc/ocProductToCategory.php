<?php


namespace Oc;


use Base\Model;

class ocProductToCategory extends Model
{
    protected $table = 'oc_product_to_category';
    protected $allowed_fields = ['main_category'];
    protected $main_key = 'product_id';
    protected $multi_identifier = ['product_id','category_id'];

}