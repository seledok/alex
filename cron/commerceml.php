<?php

require_once "init.php";

$zippath = DIR_UPLOAD;
$xmlpath = DIR_UPLOAD.'xml/';
//

$file_list = glob($zippath.'v8*.zip');

$model = new importXml();

foreach ($file_list as $file) {
    shell_exec("unzip -o $file -d " . $xmlpath);
    shell_exec("cp '".$file."' ".DIR_UPLOAD.'old');

    $xml_list = glob($xmlpath.'*.xml');

    foreach ($xml_list as $xml_file) {
        $model->parseXml($xml_file);
        unlink($xml_file);
    }
}

\Oc\ocProduct::model()
    ->where('import_time',$model->import_time(),'!=')
    ->set('quantity',0)
    ->set('status',0)
    ->set('stock_status_id',5) //'stock_status_id' => 7
    ->save();



function base_clear(){
    //sDb::query("ALTER TABLE `oc_product` CHANGE `upc` `upc` VARCHAR(150)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT '';");
    $tables = array(
        'oc_product',
        'oc_product_image',
        'oc_manufacturer',
        'oc_manufacturer_description',
        'oc_product_description',
        'oc_product_to_category',
        'oc_product_attribute',
        'oc_attribute',
        'oc_attribute_description',
        // 'oc_attribute_value',
       // 'oc_category',
      //  'oc_category_description',
        'oc_product_to_store',
       // 'oc_category_to_store',
        'oc_manufacturer_to_store',
        'oc_product_to_layout',
       // 'oc_category_to_layout',
    );
    foreach ($tables as $table)
    {
        sDb::query("TRUNCATE TABLE $table");
        echo "Таблица $table очищена\n";
    }
}