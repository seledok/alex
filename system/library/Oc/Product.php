<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-21
 * Time: 10:41
 */

namespace Oc;


use Base\Model;
use Base\MultiTableInstance;

class Product extends MultiTableInstance
{

    protected $classes = [
        'ocProduct',
        'ocProductDescription',
        'ocProductToCategory',
        'ocProductAttribute',
        'ocProductDiscount',
        'ocProductImage',
        'ocProductOption',
        'ocProductOptionValue',
        'ocProductRelated',
        'ocProductSpecial'
    ];

    public function import(array $data)
    {
        // основная таблица
        $model =  ocProduct::model()
            ->create($data)
            ->save();

        $product_id = $model->getField('product_id');

        //todo пробелма елси product_id был передан ранее!!!

        // описание товара
        ocProductDescription::model()
            ->create(array_merge(['language_id'=>1,'product_id'=>$product_id],$data))
            ->save();

        // категория
        if(!empty($data['category_id']))
            ocProductToCategory::model()
                ->set('category_id',$data['category_id'])
                ->set('product_id',$product_id)
                ->set('main_category',1);


        return $product_id;
    }

    public static function scopeFull()
    {
        return ocProduct::model()
            ->leftJoin('oc_product_description','pd');
    }


}
