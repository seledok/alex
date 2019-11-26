<?php

require_once "init.php";

$zippath = DIR_UPLOAD;
$xmlpath = DIR_UPLOAD.'xml';
//

$file_list = glob($zippath.'v8*.zip');

foreach ($file_list as $file) {
    shell_exec("unzip -o $file -d " . $xmlpath);
    shell_exec("cp '".$file."' ".DIR_UPLOAD.'old');
    //unlink($file);

    $xml_list = glob($xmlpath.'*.xml');

    foreach ($xml_list as $xml_file) {
        $import_time = importXml::xml()->parseXml($xml_file);
        unlink($xml_file);
    }
}