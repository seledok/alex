<?php


namespace Oc;


use Base\Model;

class ocManufacturerDescription extends Model
{
    protected $allowed_fields = ['language_id','description','meta_description','meta_keyword','meta_title','meta_h1'];
    protected $table = 'oc_manufacturer_description';
    protected $main_key = 'manufacturer_id';


}