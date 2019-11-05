<?php
class ControllerExtensionModuleLightshopShippayMethod extends Controller {
	public function index() {
		$this->load->language('checkout/checkout');

//var_dump($this->session->data['order_id']);	

		// lightshop
		$this->load->language('extension/theme/lightshop');
		$data['button_back'] = $this->language->get('button_back');
		$data['st4_title_status'] = $this->config->get('theme_lightshop_checkout_st4_title');
		$data['st4_col'] = $this->config->get('theme_lightshop_checkout_st4_col');
		// lightshop end

		if (isset($this->session->data['shipping_address'])) {
			$addressMain = $this->session->data['shipping_address'];
		}elseif(isset($this->session->data['payment_address'])){
			$addressMain = $this->session->data['payment_address'];
			$this->session->data['shipping_address'] = $this->session->data['payment_address'];
		}

		if (isset($addressMain) && !isset($this->session->data['shipping_methods'])) {

			// Shipping Methods
			$method_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('shipping');

			foreach ($results as $result) {
				if ($this->config->get('shipping_' . $result['code'] . '_status')) {
					$this->load->model('extension/shipping/' . $result['code']);

					$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($addressMain);

					if ($quote) {
						$method_data[$result['code']] = array(
							'title'      => $quote['title'],
							'quote'      => $quote['quote'],
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						);
					}
				}
			}

			$sort_order = array();

			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $method_data);

			$this->session->data['shipping_methods'] = $method_data;

			if(!isset($this->session->data['shipping_method'])){
				$shipKey = key($this->session->data['shipping_methods']);
				$curMetod = current($this->session->data['shipping_methods']); 
				$this->session->data['shipping_method'] = $curMetod["quote"][$shipKey];
			}
		}

		if (isset($this->session->data['payment_address']) && !isset($this->session->data['payment_methods'])) {
			// Totals
			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array.
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);
			
			$this->load->model('setting/extension');

			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) { 
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);
				
					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			// Payment Methods
			$method_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('payment');

			$recurring = $this->cart->hasRecurringProducts();

			foreach ($results as $result) {
				if ($this->config->get('payment_' . $result['code'] . '_status')) {
					$this->load->model('extension/payment/' . $result['code']);

					$method = $this->{'model_extension_payment_' . $result['code']}->getMethod($this->session->data['payment_address'], $total);

					if ($method) {
						if ($recurring) {
							if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
								$method_data[$result['code']] = $method;
							}
						} else {
							$method_data[$result['code']] = $method;
						}
					}
				}
			}

			$sort_order = array();

			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $method_data);

			$this->session->data['payment_methods'] = $method_data;
			if(!isset($this->session->data['payment_method'])){
				$this->session->data['payment_method'] = current($this->session->data['payment_methods']);
			}
		}


		$data['text_shipping_method'] = $this->language->get('text_shipping_method');
		$data['text_payment_method'] = $this->language->get('text_payment_method');
		$data['text_comments'] = $this->language->get('text_comments');
		$data['text_loading'] = $this->language->get('text_loading');
		$data['text_lightshop_ship'] = $this->language->get('text_lightshop_ship');
		$data['text_lightshop_pay'] = $this->language->get('text_lightshop_pay');


		$this->load->model('catalog/information');
		$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));
		$data['error_agree'] = '';
		if ($information_info) {
			$data['error_agree'] = sprintf($this->language->get('error_agree'), $information_info['title']);
		}



		$data['button_continue'] = $this->language->get('entry_confirm');

		if (empty($this->session->data['shipping_methods'])) {
			$data['error_warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['shipping_methods'])) {
			$data['shipping_methods'] = $this->session->data['shipping_methods'];
		} else {
			$data['shipping_methods'] = array();
		}

		if (isset($this->session->data['shipping_method']['code'])) {
			$data['shipping_code'] = $this->session->data['shipping_method']['code'];
		} else {
			$data['shipping_code'] = '';
		}

		if (isset($this->session->data['comment'])) {
			$data['comment'] = $this->session->data['comment'];
		} else {
			$data['comment'] = '';
		}

//payment

		if (empty($this->session->data['payment_methods'])) {
			$data['error_warning'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact'));
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['payment_methods'])) {
			$data['payment_methods'] = $this->session->data['payment_methods'];
//			if(!isset($this->session->data['payment_method'])){
//				$this->session->data['payment_method'] = current($this->session->data['payment_methods']);
//			}
//			$data['payment'] = $this->load->controller('extension/payment/' . $this->session->data['payment_method']['code']);
		} else {
			$data['payment_methods'] = array();
		}

		if (isset($this->session->data['payment_method']['code'])) {
			$data['payment_code'] = $this->session->data['payment_method']['code'];
		} else {
			$data['payment_code'] = '';
		}
		
		$data['scripts'] = $this->document->getScripts();

		if ($this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

			if ($information_info) {
				$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_checkout_id'), true), $information_info['title'], $information_info['title']);
			} else {
				$data['text_agree'] = '';
			}
		} else {
			$data['text_agree'] = '';
		}

		if (isset($this->session->data['agree'])) {
			$data['agree'] = $this->session->data['agree'];
		} else {
			$data['agree'] = '';
		}
		$data['payment'] = '';

		if (isset($this->session->data['payment_method'])) {	
			$data['payment'] = $this->load->controller('extension/payment/' . $this->session->data['payment_method']['code']);	
		}
//payment		


		$this->response->setOutput($this->load->view('checkout/shippay_method', $data));
	}

	public function save() {
		$this->load->language('checkout/checkout');

		$json = array();

		// Validate if shipping is required. If not the customer should not have reached this page.
		if (!$this->cart->hasShipping()) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
		}

		// Validate if shipping address has been set.
		if (!isset($this->session->data['shipping_address'])) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
		}
		
		// Validate if payment address has been set.
		if (!isset($this->session->data['payment_address'])) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
		}

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');
		}


		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$json['redirect'] = $this->url->link('checkout/cart');

				break;
			}
		}

		if (!isset($this->request->post['shipping_method'])) {
			$json['error']['warning'] = $this->language->get('error_shipping');
		} else {
			$shipping = explode('.', $this->request->post['shipping_method']);

			if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
				$json['error']['warning'] = $this->language->get('error_shipping');
			}
		}
		
		if (!isset($this->request->post['payment_method'])) {
			$json['error']['warning'] = $this->language->get('error_payment');
		} elseif (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
			$json['error']['warning'] = $this->language->get('error_payment');
		}

		if ($this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$json['error']['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}


		if (!$json) {
			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
			
			$this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];

			$this->session->data['comment'] = strip_tags($this->request->post['comment']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function refreshmetods() {

		if (isset($this->request->post['shipping_method'])) {
			$shipping = explode('.', $this->request->post['shipping_method']);
			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
		}	
		if (isset($this->request->post['payment_method'])) {			
			$this->session->data['payment_method'] =  $this->session->data['payment_methods'][$this->request->post['payment_method']];
		}	
		if (isset($this->request->post['comment'])) {			
			$this->session->data['comment'] = strip_tags($this->request->post['comment']);
		}			
	}
}