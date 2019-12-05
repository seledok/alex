<?php

require_once "init.php";

/**
 * Class import_attributes
 * Извлекает атрибуты из XLS и создает их привязывая к товарам
 */
class import_attributes
{


    /**
     * @var
     * хранит в себе ИД созданных в первой итерации парметров
     */
    protected $columns;

    /** Основной метод
     * @param $filepath
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    function import($filepath)
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filepath);

        $worksheet = $spreadsheet->getSheet(0);
        $row_index = 0;

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();

            $row_data = $this->getRow($cellIterator);

            // если это ключи
            if($row_index==0)
            {
                foreach ($row_data as $column => $value)
                {
                    // массив содержит Ид атрибута куда писать будетм
                    $this->columns[$column] = \Oc\Attribute::model()->idByName($value);
                }
            } else {
                $product_id = 0;
                foreach ($row_data as $column => $value) {
                    if($column == 0) // это первая колонка в котрой артикул товара
                    {
                        $product_id = \Oc\ocProduct::model()
                            ->where('sku',$value)
                            ->get()
                            ->getField('product_id');
                        if(!$product_id) {
                            echo "Товар с артикулом " . $value . " не был найден" . PHP_EOL;
                            break;
                        }
                        else
                            echo '+'.PHP_EOL;
                    }
                    elseif($column == 18)
                    {
                        if($product_id)
                            \Oc\ocProductDescription::model()
                                ->set('product_id',$product_id)
                                ->set('description',$value)
                                ->save();
                    }
                    else            // это колонка в которой лежат значнеия параметра
                    {
                        if($product_id)
                            $pa = \Oc\ocProductAttribute::model()
                                ->set('attribute_id', $this->columns[$column])
                                ->set('text', $value)
                                ->set('product_id', $product_id)
                                ->set('language_id', 1)
                                ->save();

                           // d($pa->last_query);

                    }
                }
            }

            $row_index++;
        }
    }

    /** Делает из строки массив
     * @param $data
     * @return array
     */
    protected function getRow($data)
    {
        $field_count = 0;
        $array = [];
        foreach ($data as $key => $cell)
        {
            $val = $cell->getValue();
            $val = trim($val,'"');
            $val = trim($val);
            $val = \sDB::escape($val);
            if($val != '' && $val != 'NULL' && $val != '!NULL')
                $array[$field_count] = $val;

            $field_count++;
        }

        return $array;
    }

}
/* TRUNCATE TABLE `oc_attribute`;
TRUNCATE TABLE `oc_attribute_description`;
TRUNCATE TABLE `oc_product_attribute`;
 */
//sDb::query("");

$ia = new import_attributes();
$ia->import('/Users/seledok/Downloads/Товар для выгрузки с артикулами (2).xlsx');

