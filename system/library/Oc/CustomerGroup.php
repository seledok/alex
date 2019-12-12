<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-12-12
 * Time: 10:15
 */

namespace Oc;


use Base\MultiTableInstance;

class CustomerGroup extends MultiTableInstance
{

    public function import(array $data)
    {

        $model = ocCustomerGroup::model()
            ->create($data)
            ->save();

        if(empty($data['customer_group_id']))
            $customer_group_id = $model->getField('customer_group_id');
        else
            $customer_group_id = $data['customer_group_id'];

         //dd($customer_group_id);

        ocCustomerGroupDescription::model()->create
        (
            array_merge(
                ['customer_group_id'=>$customer_group_id],
                $data
            )
        )
            ->save();

        return $customer_group_id;
    }

    public static function scopeFull()
    {

    }
}