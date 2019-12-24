<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-05
 * Time: 11:08
 */

namespace Base;

use Vendor\vBuffer;
use Vendor\vVendor;

abstract class BufferAdd
{
    //protected $vendor_info;
    protected $vendor_id;
    protected $delimiter;
    protected $encoding;

    public static function buffer($vendor_id)
    {
        return new static($vendor_id);
    }

    public function __construct($vendor_id)
    {
        $this->vendor_id = $vendor_id;
        $vendor_info = vVendor::model()->where('vendor_id',$vendor_id)->get();
        $this->delimiter = $vendor_info->getField('delimiter');
        $this->encoding = $vendor_info->getField('encoding');
    }

    protected function checkBuffer()
    {
        if(
        vBuffer::model()
            ->where('vendor_id',$this->vendor_id)
            ->where('status',1)
            ->count()
        )
        {
            \stat::log('Warning: У поставщика уже есть товары в буфере');
            return false;
        }
        else
        {
            vBuffer::model()
                ->where('vendor_id',$this->vendor_id)
                ->delete();
            return true;
        }
    }

    protected function make_json($data,$encoding)
    {
        $field_count = 0;
        $array = [];
        foreach ($data as $key => $val)
        {

            if($encoding=='cp1251') $val = iconv('cp1251', 'utf8', $val);
            $val = trim($val,'"');
            $val = trim($val);
            $val = \sDB::escape($val);
            if($val != '' && $val != 'NULL' && $val != '#NULL!')
                $array[$field_count] = $val;

            $field_count++;
        }

        return json_encode($array);
    }


}