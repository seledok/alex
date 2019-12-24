<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-12-05
 * Time: 10:58
 */

namespace Oc;


use Base\MultiTableInstance;

class Attribute extends MultiTableInstance
{

    public function import(array $data)
    {

        $data['attribute_group_id'] = 1;
        $data['language_id'] = 1;

        $model = ocAttribute::model()
            ->create($data)->save();

        if(empty($data['attribute_id']))
            $attribute_id = $model->getField('attribute_id');
        else
            $attribute_id = $data['attribute_id'];

       // dd($model);

        ocAttributeDescription::model()->create
        (
            array_merge(
            ['attribute_id'=>$attribute_id],
            $data
            )
        )
            ->save();

        return $attribute_id;

    }

    public function idByName($name)
    {
        $attribute_id = ocAttributeDescription::model()
            ->where('name',$name)
            ->get()
            ->getField('attribute_id');

        if(!$attribute_id)
        {
            $attribute_id = $this->import(['name'=>$name]);
        }

        return $attribute_id;
    }

    public static function scopeFull()
    {

    }

}