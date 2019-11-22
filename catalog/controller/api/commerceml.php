<?php
// *	@copyright	OPENCART.PRO 2011 - 2015.
// *	@forum	http://forum.opencart.pro
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerApiCommerceml extends Controller {

    static $vendor_id = 0;
    private $order_list = array();

    private $login = 'alex';
    private $password = 'asdvrxe3543';

    public function index() {

        list($login, $password) =
           $data = explode(':', base64_decode(substr($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6)));

        mail('seledkov@itresh.com','commercML',$login.'- '.$password . PHP_EOL.print_r($_SERVER,true));
        header('WWW-Authenticate: Basic realm="My Realm"');
        if(isset($_SERVER['PHP_AUTH_USER'])) {
             $username = $_SERVER['PHP_AUTH_USER'];
             $password = $_SERVER['PHP_AUTH_PW'];
        } else {
            if(isset($_GET['user'])) {
                $username = $_GET['user'];
                $password = $_GET['pass'];
            } else {
                header("HTTP/1.0 401 Unauthorized");
                exit;
            }
        }


        if($username != $this->login || $password !=  $this->password){
            header("HTTP/1.0 403 Forbidden");
            exit;
        }


        $mode = $_GET['type'].'_'.$_GET['mode'];

        call_user_func(array($this,$mode));

        exit;
    }

    private function catalog_checkauth() {

        die ('success
exchsession
'.time());
    }

    private function catalog_init() {
        die ('zip=yes
file_limit=2000000');
    }

    private function PostPrices()
    {
        $id = $_COOKIE['exchsession'];
        ob_start();
        $filename = $_GET['filename'];
        $data = file_get_contents('php://input');
        $path = DIR_UPLOAD .$filename;
        $handle = fopen($path, 'a');
        fwrite($handle, $data);
        fclose($handle);
        $errors = ob_get_contents();
        ob_end_clean();
        die('success');
    }

    private function catalog_file() {
        $id = $_COOKIE['exchsession'];
        ob_start();
        $filename = $_GET['filename'];
        $data = file_get_contents('php://input');
        $path = DIR_UPLOAD .$filename;
        $handle = fopen($path, 'a');
        fwrite($handle, $data);
        fclose($handle);
        $errors = ob_get_contents();
        ob_end_clean();
        die('success');
    }

    private function catalog_import() {
        die('success');

    }


    private function sale_checkauth()
    {
        die ('success
exchsession
'.time());
    }

    private function sale_init()
    {
        die ('zip=yes
file_limit=2000000');
    }

    private function sale_query()
    {
        //die('success');
        echo $this->make_order_xml();
    }

    private function sale_success()
    {

        die('success');
    }

    private function sale_file()
    {
        $id = $_COOKIE['exchsession'];
        ob_start();
        $filename = $_GET['filename'];
        $data = file_get_contents('php://input');
        $path = '/var/www/gotti/data/sync/vendors/'.self::$vendor_id.'/in/'.$filename;
        $handle = fopen($path, 'a');
        fwrite($handle, $data);
        fclose($handle);
        $errors = ob_get_contents();
        ob_end_clean();
        die('success');
    }

    private function get_vendor_orders($vendor_id)
    {
        return  $this->db->get_rows("SELECT * FROM gotti.1c_order_log WHERE order_status_id = '5' && replic_id = '0' && vendor_id = '$vendor_id'");
    }

     function make_order_xml()
    {


        // для тестов
        if(!self::$vendor_id) self::$vendor_id = 7;


        $base = $this->base;
        $sync = new product_sync($this->registry);
        $orders = $this->get_vendor_orders(self::$vendor_id);

        $dom = new domDocument("1.0", "windows-1251");
        //$dom = new domDocument("1.0", "utf-8");
        $dom->formatOutput = true;
        $root = $dom->createElement("КоммерческаяИнформация");
        $root->setAttribute("ВерсияСхемы", '2.07');
        $root->setAttribute("ДатаФормирования", date('Y-m-dТH:m:s'));
        $dom->appendChild($root);


        if($orders) {

            $this->db->query("INSERT INTO gotti.1c_replic_out SET filename = 'order.xml' ");
            $replic_id = $this->db->getLastId();

            foreach ($orders as $order) {
                // проверка что заказ был в статусе 5 // передан на склад
                $is_work = $this->db->query("SELECT * FROM " . $order['shop'] . ".oc_order_history WHERE order_id = '" . $order['order_id'] . "' && order_status_id =  '5' ");

                if (!$is_work->num_rows && $order['order_status_id'] != 5) {
                    echo 'Note: Заказ ' . $order['order_id'] . " из магазина " . $order['shop'] . " не выгружкен - его не пердавали на склад\n\r";
                    continue;
                }
                // проверка что товар не локальный будет ниже

                // что делать с заказами где локальный и не локальный товар?

                /** если заказ из оптового магазина то возьмеме ИНН покупателя
                 *   если заказ от пратенра то используем его ИНН
                 */
                if ($base->shops[$order['shop']]['tariff'] > 4) {
                    $order_customer_info = $this->db->get_row("SELECT * FROM " . $order['shop'] . ".oc_order as o 
                LEFT JOIN " . $order['shop'] . ".oc_customer as c ON c.customer_id = o.customer_id 
                LEFT JOIN " . $order['shop'] . ".oc_address as a ON a.customer_id = o.customer_id 
                WHERE o.order_id = '" . $order['order_id'] . "' ");

                    // var_dump($order_customer_info);
                    // если это база готти получим INN

                    if (!$order_customer_info['fax'] || !stat::is_valid_inn($order_customer_info['fax'])) {
                        //echo 'Note: Заказ ' . $order['order_id'] . " из магазина " . $order['shop'] . " не выгружкен - он сделан без регистрации или ИНН указан неверно\n\r";
                        $not[] = $order['global_order_id'];
                        continue;
                    } else {
                        $inn = $order_customer_info['fax'];
                        $org_name = addslashes($order_customer_info['company']);

                    }

                } else {
                    $inn = $base->shops[$order['shop']]['inn'];
                    $set = $sync->getSetting('config', $order['shop']);
                    $org_name = $set['config_owner'];
                    if (!$inn) {
                        echo 'Note: Заказ ' . $order['order_id'] . " из магазина " . $order['shop'] . " не выгружкен - нет ИНН партнера\n\r";
                        $not[] = $order['global_order_id'];
                        continue;
                    }
                }

                $summ = 0;
                $products = $this->db->query("SELECT op.order_id, o.date_added, vp.guid as model, p.sku, op.quantity, op.price_in as price, pd.name, o.store_name, o.shipping_zone 
    FROM " . $order['shop'] . ".oc_order_product op
    LEFT JOIN " . $order['shop'] . ".oc_order o ON o.order_id = op.order_id
    LEFT JOIN gotti.oc_product p ON p.product_id = op.product_id
    LEFT JOIN gotti.1c_vendor_product vp ON vp.vendor_id = " . self::$vendor_id . " && vp.product_id = p.product_id
    LEFT JOIN gotti.oc_product_description pd ON pd.product_id = op.product_id
     
    WHERE op.order_id = '" . $order['order_id'] . "' && p.product_id IS NOT NULL");

                // echo 'Выгружен заказ № '.$order['order_id'].' из базы '.$order['shop'].' ИНН:'.$inn."\n\r";


                if ($products->num_rows == 0) continue;


                foreach ($products->rows as $item) {
                    $summ += $item['price'] * $item['quantity'];
                }
                unset($doc);
                unset($docdata);
                $doc = $dom->createElement("Документ");
                $docdata[] = $dom->createElement("Ид", 'itr-order-' . $order['global_order_id']);
                $docdata[] = $dom->createElement("Номер", $order['global_order_id']);
                $docdata[] = $dom->createElement("ХозОперация", 'Заказ товара');
                $docdata[] = $dom->createElement("Роль", 'Продавец');
                $docdata[] = $dom->createElement("Валюта", 'Руб');
                $docdata[] = $dom->createElement("Дата", date('Y-m-d', strtotime($order['date_added'])));
                $docdata[] = $dom->createElement("Курс", 1);
                $docdata[] = $dom->createElement("Сумма", $summ);
                // $docdata[] = $dom->createElement("org_inn", '541014835226');


                $docdata['users'] = $dom->createElement("Контрагенты");
                unset($user);
                unset($userdata);
                $user = $dom->createElement("Контрагент");

                $userdata[] = $dom->createElement("Наименование", $org_name);
                $userdata[] = $dom->createElement("ОфициальноеНаименование", $org_name);

                $userdata[] = $dom->createElement("ИНН", $inn);
                $userdata[] = $dom->createElement("Роль", 'Покупатель');


                foreach ($userdata as $item) {
                    $user->appendChild($item);
                }
                $docdata['users']->appendChild($user);


                $zone = ' /' . $products->row['shipping_zone'] . '/';
                $gorod = isset($sync->shops[$order['shop']]['gorod']) ? $sync->shops[$order['shop']]['gorod'] . $zone : 'Новосибирск-ОПТ';

                $docdata[] = $dom->createElement("Комментарий", $gorod . ' - ' . addslashes($products->row['store_name']) . ' / ');

                $docdata['taxes'] = $dom->createElement("Налоги");
                unset($tax);
                unset($taxdata);
                $tax = $dom->createElement("Налог");
                $taxdata[] = $dom->createElement('Наименование', "НДС");
                $taxdata[] = $dom->createElement("УчтеноВСумме", 'true');
                $taxdata[] = $dom->createElement("Сумма", round($summ * 0.18, 2));
                foreach ($taxdata as $item) {
                    $tax->appendChild($item);
                }
                $docdata['taxes']->appendChild($tax);


                $docdata['products'] = $dom->createElement("Товары");
                foreach ($products->rows as $product) {
                    unset($oneproduct);
                    unset($oneproductdata);
                    $oneproduct = $dom->createElement("Товар");
                    $oneproductdata[] = $dom->createElement("Ид", $product['model']);
                    $oneproductdata[] = $dom->createElement("Артикул", $product['sku']);
                    $oneproductdata[] = $dom->createElement("Наименование", htmlspecialchars($product['name']));
                    $oneproductdata[] = $dom->createElement("Количество", $product['quantity']);
                    $oneproductdata[] = $dom->createElement("ЦенаЗаЕдиницу", $product['price']);
                    $oneproductdata[] = $dom->createElement("Сумма", $product['price'] * $product['quantity']);

                    unset($tax);
                    unset($taxdata);
                    $oneproductdata['taxes'] = $dom->createElement("Налоги");
                    $tax = $dom->createElement("Налог");
                    $taxdata[] = $dom->createElement('Наименование', "НДС");
                    $taxdata[] = $dom->createElement("УчтеноВСумме", 'true');
                    $taxdata[] = $dom->createElement("Сумма", round($product['price'] * $product['quantity'] * 0.18, 2));
                    foreach ($taxdata as $item) {
                        $tax->appendChild($item);
                    }
                    $oneproductdata['taxes']->appendChild($tax);

                    foreach ($oneproductdata as $item) {
                        $oneproduct->appendChild($item);
                    }
                    $docdata['products']->appendChild($oneproduct);

                }
                unset($status);
                unset($status_name);
                unset($status_zn);
                unset($sait);
                unset($sait_zn);
                $docdata['r'] = $dom->createElement("ЗначенияРеквизитов");
                $status = $dom->createElement("ЗначениеРеквизита");
                $status_name = $dom->createElement("Наименование", 'СтатусДокумента');
                $status_zn = $dom->createElement("Значение", $order['order_status_id']);

                $sait = $dom->createElement("ЗначениеРеквизита");
                $sait_name = $dom->createElement("Наименование", 'Сайт');
                $sait_zn = $dom->createElement("Значение", '[s1] ГОТТИ Регион');

                $otm = $dom->createElement("ЗначениеРеквизита");
                $otm_name = $dom->createElement("Наименование", 'Отменен');
                $otm_zn = $dom->createElement("Значение", 'false');

                $otm->appendChild($status_name);
                $otm->appendChild($status_zn);

                $status->appendChild($status_name);
                $status->appendChild($status_zn);

                $sait->appendChild($sait_name);
                $sait->appendChild($sait_zn);
                $sait->appendChild($otm);

                $docdata['r']->appendChild($status);
                $docdata['r']->appendChild($sait);


                foreach ($docdata as $item) {
                    $doc->appendChild($item);
                }

                $root->appendChild($doc);

                $orders_in_replic[$order['shop']][] = $order['global_order_id'];
            }



            //
            foreach ($orders as $order) {
                // если заказ не надо выгружать
                if(isset($not) && in_array($order['global_order_id'],$not))
                    $this->db->query("UPDATE gotti.1c_order_log SET replic_id = NULL WHERE order_id = '" . $order['order_id'] . "' && shop = '".$order['shop']."' ");
                else {
                    // ид реплики
                    $this->db->query("UPDATE gotti.1c_order_log SET replic_id = '$replic_id' WHERE global_order_id = " .  $order['global_order_id'] . " ");
                    // статус заказа
                    $this->db->query("UPDATE ".$order['shop'].".oc_order SET order_status_id = '7' WHERE order_id = ".$order['order_id']." ");
                    // история
                    $this->db->query("INSERT INTO ".$order['shop'].".oc_order_history SET order_status_id = '7', order_id = ".$order['order_id'].", `comment` = 'Заказ был передан в учетную систему склада', date_added = NOW()  ");
                }

            }


            //$orders = implode(',',$orders_in_replic);
            //$this->db->query("UPDATE gotti.1c_order_log SET replic_id = '$replic_id' WHERE global_order_id IN (" . $orders . ") ");
            /** todo: смнить статус заказа на Принят в работу! */
            //
        }
            //echo $dom->saveXML();
            return $dom->saveXML();


    }

    function order()
    {
        echo $this->make_order_xml();
    }
}
