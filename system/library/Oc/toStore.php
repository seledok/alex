<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-24
 * Time: 10:36
 */

namespace Oc;


class toStore
{
    static function link($what,$id)
    {
        \sDB::query("INSERT IGNORE INTO gotti.`" . DB_PREFIX . $what."_to_store`  SET "
            . "`".$what."_id` = '".$id."', "
            . "`store_id` = '0' ");

        \sDB::query("INSERT IGNORE INTO gotti.`" . DB_PREFIX . $what."_to_layout`  SET "
            . "`".$what."_id` = '".$id."', "
            . "`store_id` = '0',"
            . "layout_id = '0' ");
    }

    static function unlink($what,$id)
    {
        \sDB::query("DELETE FROM gotti.`" . DB_PREFIX . $what."_to_store`  WHERE "
            . "`".$what."_id` = '".$id."' ");

        \sDB::query("DELETE FROM gotti.`" . DB_PREFIX . $what."_to_layout`  WHERE "
            . "`".$what."_id` = '".$id."' ");
    }



}