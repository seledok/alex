<?php
class ModelExtensionTotalLightshopsets extends Model {


	public function getTotal($total) {
		$this->load->language('extension/total/lightshopsets');

		$this->load->model('extension/module/lightshop');

		$setids = array();
		if (isset($this->session->data['lightshopsetid']) && $this->session->data['lightshopsetid']) {
			$setids = $this->session->data['lightshopsetid'];
		}

		$cartProducts = array();

		foreach ($this->cart->getProducts() as $product) {
			$cartProducts[$product['product_id']]['quantity'] = $product['quantity'];
			$cartProducts[$product['product_id']]['price'] = $product['price'];
		}



		foreach ($setids as  $setid) { 

			$lightshopsets = $this->model_extension_module_lightshop->getSetDiscount($total,$setid,$cartProducts);

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$lightshopsets += $voucher['amount'];
				}
			}

			if ($lightshopsets['discount']) {

				$setInfo = $this->model_extension_module_lightshop->getSetInfo($setid);

				$total['totals'][] = array(
					'code'       => 'lightshopsets',
					'title'      => $this->language->get('text_lightshopsets').' - '.$setInfo['title'],
					'value'      => -$lightshopsets['discount'],
					'sort_order' => $this->config->get('total_lightshopsets_sort_order')
				);

				$total['total'] -= $lightshopsets['discount'];
				$cartProducts = $lightshopsets['cartproducts'];
			}	
		}

	}

}
