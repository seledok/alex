<?php


namespace Oc;


use Base\Model;
use Product\toStore;

class ocManufacturer extends Model
{

    protected $allowed_fields = ['name','image','noindex','sort_order','rrc'];
    protected $table = 'oc_manufacturer';
    protected $main_key = 'manufacturer_id';


    protected function afterInsertDescription()
    {
        ocManufacturerDescription::model()
            ->set('manufacturer_id',$this->data[$this->main_key])
            ->set('language_id',1)
            ->save();

        \sDb::query("INSERT INTO oc_manufacturer_to_layout SET manufacturer_id = '?', store_id = '?', 'layout_id' = '?' ",
            $this->data[$this->main_key],
            0,
            0
        );

        \sDb::query("INSERT INTO oc_manufacturer_to_store SET manufacturer_id = '?', store_id = '?' ",
            $this->data[$this->main_key],
            0
        );
    }

    protected function onDelete()
    {
        toStore::unlink('manufacturer',$this->data[$this->main_key]);
    }
}