<?php


namespace Oc;


use Base\Model;
use Base\MultiTableInstance;

class Manufacturer extends MultiTableInstance
{
    protected $classes = [
        'ocManufacturer',
        'ocManufacturerDescription'
    ];

    public function import(array $data)
    {
        // TODO: Implement import() method.
    }

    public static function idByName($name)
    {
        $manufacturer_id = ocManufacturer::model()->getIdbyName($name);

        // manufacturer_description
        ocManufacturerDescription::model()
            ->set('manufacturer_id',$manufacturer_id)
            ->set('language_id',1)
            ->save();
        // to_store

        // to_layot

        return $manufacturer_id;
    }

    static function scopeFull()
    {
        return ocManufacturer::model()
            ->leftJoin('oc_manufacturer_description');
    }

}