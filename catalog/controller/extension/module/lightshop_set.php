<?php
class ControllerExtensionModuleLightshopSet extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/lightshop_set');

		$data['heading_title'] = $setting['name'];

		$data['text_tax'] = $this->language->get('text_tax');
		$data['text_sale'] = $this->language->get('text_sale');
		$data['text_quantity'] = $this->language->get('text_quantity');

		$data['button_cart'] = $this->language->get('button_cart');
		$data['button_wishlist'] = $this->language->get('button_wishlist');
		$data['button_compare'] = $this->language->get('button_compare');

		$this->load->model('catalog/product');

		$this->load->model('extension/module/lightshop');

		$this->load->model('tool/image');

		$data['lazyload'] = $this->config->get('theme_lightshop_lazyload');
		
		$products = array();
		$data['sets'] = array();
		$total = 0;


		if (!isset($this->request->get['product_id']) && $this->request->get['product_id']) {
			return;
		}else{
			$product_id = $this->request->get['product_id'];
		}




		$sets = $this->model_extension_module_lightshop->getSets4Product($product_id);
	
		if (!empty($sets)) {

			foreach ($sets as $set_id => $set) {				


                $productsInfo = $this->getProductsInfo($set['products'], $set_id);
                $products = $productsInfo['products'];
                $total = $productsInfo['total'];

				if ($set['mode']) {
					$discount = $total/100*(int)$set['discount'];
				}else{
					$discount = (int)$set['discount'];
				}

				$totalf = $this->currency->format($total - $discount , $this->session->data['currency']);
				$discountf = $this->currency->format($discount, $this->session->data['currency']);

				if (count($products) < 2) { // If count of products is less then 2 , the set will not be outputed
					continue;
				}

				$data['sets'][$set_id] = array(
					'products'   => $products,
					'total'		 => $totalf,
					'discount'	 => $discountf,
					'mode'		 => $set['mode']
				);

			}

		 	$data['labelsinfo'] = $productsInfo['labelsinfo'];
		 	$data['language_id'] = $productsInfo['language_id'];
		}
		if ($data['sets']) {

			return $this->load->view('extension/module/lightshop_set', $data);

		}
	}

	public function getvariants() {
		$this->load->language('extension/module/lightshop_set');
		
		$this->load->model('catalog/product');

		$this->load->model('extension/module/lightshop');

		$this->load->model('tool/image');
		
		$data = array();
		$data['products'] = array();

		if ((!isset($this->request->get['product_id']) || !$this->request->get['product_id']) || (!isset($this->request->get['set_id']) || !$this->request->get['set_id'])) {
			return;
		}else{
			$product_id = $this->request->get['product_id'];
			$set_id = $this->request->get['set_id'];
		}

		$products = $this->model_extension_module_lightshop->getVar4SetProduct($set_id,$product_id);

        $productsInfo = $this->getProductsInfo($products);

        $data = array(
        	'set_id'	=> $set_id,
        	'products'  => $productsInfo['products'],
        	'text_quantity'  => $this->language->get('text_quantity'),
        	'text_popup_package_title'  => $this->language->get('text_popup_package_title'),
        	'text_item_add'  => $this->language->get('text_item_add'),
        	'for_product' => $product_id,
        	'labelsinfo' => $productsInfo['labelsinfo'],
        	'language_id' => $productsInfo['language_id']
        );



		$this->response->setOutput($this->load->view('extension/module/lightshop_set_var', $data));
	}


	public function getproduct() {
		$this->load->language('extension/module/lightshop_set');
		
		$this->load->model('catalog/product');

		$this->load->model('extension/module/lightshop');

		$this->load->model('tool/image');
				
		$data = array();
		$data['products'] = array();

		if (!isset($this->request->get['product_id']) || !$this->request->get['product_id']) {
			return;
		}else{
			$product_id = $this->request->get['product_id'];
		}

		if ($this->request->get['qty']) {
			$quantity = $this->request->get['qty'];
		}else{
			$quantity = 1;
		}

		$products[] = array(
			'product_id' => $this->request->get['product_id'],
			'quantity'   => $quantity,
			'sort_order' => 1
		);


        $data = $this->getProductsInfo($products);
        $data['text_quantity'] = $this->language->get('text_quantity');

//        $data['products'] = $productsInfo['products'];



		$this->response->setOutput($this->load->view('extension/module/lightshop_set_prod', $data));
	}


	public function refreshtotal() {
		$this->load->language('extension/module/lightshop_set');
		
		$this->load->model('catalog/product');

		$this->load->model('extension/module/lightshop');

		$this->load->model('tool/image');

		$data = array();
		$json = array();
		$data['products'] = array();
		$product_ids = array();


		if (!isset($this->request->post['setproducts']) || !$this->request->post['setproducts']) {
			return;
		}else{
			$product_ids = $this->request->post['setproducts'];
		}

		$set_id = $this->request->post['setid'];

		foreach ($product_ids as $id => $quantity) {
			$products[] = array(
				'product_id' => $id,
				'quantity'   => $quantity,
				'sort_order' => 1
			);
		}



        $productsInfo = $this->getProductsInfo($products);

                $total = $productsInfo['total'];
                $setInfo = $this->model_extension_module_lightshop->getSetInfo($set_id);
	
				if ($setInfo['mode']) {
					$discount = $total/100*(int)$setInfo['discount'];
				}else{
					$discount = (int)$setInfo['discount'];
				}

				$totalf = $this->currency->format($total - $discount , $this->session->data['currency']);
				$discountf = 'Скидка '.$this->currency->format($discount, $this->session->data['currency']);

				$data = array(
					'total'		 => $totalf,
					'discount'	 => $discountf
				);
 	
  		$json['success'] = $data;
        

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}


	public function getProductsInfo($items, $set_id = 0) {  

	
		$this->load->model('catalog/product');

		$this->load->model('extension/module/lightshop');

		$this->load->model('tool/image');

		$products = array();
		$data = array();

		$total = 0;

		$this->load->language('extension/theme/lightshop');
	
		// labels
			$labelsInfo = array();
			if($this->config->get('theme_lightshop_label')){
				$labelsInfo = $this->config->get('theme_lightshop_label');
			}
			$data['language_id'] = $this->config->get('config_language_id');
			$newest = array();
			$sales = false;
			if(isset($labelsInfo['new']['period']) && $labelsInfo['new']['status']){
				$newest = $this->model_catalog_product->getNewestProducts($labelsInfo['new']['period']);			
			}
			if(isset($labelsInfo['sale']['status']) && $labelsInfo['sale']['status']){
				$sales = true;				
			}	
			$data['labelsinfo'] = $labelsInfo;	
		      if (isset($labelsInfo['hit']) && $labelsInfo['hit']['status']) {
		        $hits = $this->model_extension_module_lightshop->getHitProducts($labelsInfo['hit']['period'],$labelsInfo['hit']['qty']);
		      }	
		// labels	

				foreach ($items as $product) {
					$product_info = $this->model_catalog_product->getProduct($product['product_id']);

					if ($product_info) {

						$countVariants = 0;

						if ($set_id) {
							$variantProducts = $this->model_extension_module_lightshop->getVar4SetProduct($set_id,$product['product_id']);
							$countVariants = count($variantProducts);
						}
						
			
						if ($product_info['image']) {
							$image = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
						} else {
							$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
						}

						if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
							$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$price = false;
						}

						if ((float)$product_info['special']) {
							$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$special = false;
						}

						if ($this->config->get('config_tax')) {
							$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
						} else {
							$tax = false;
						}

						if ($this->config->get('config_review_status')) {
							$rating = $product_info['rating'];
						} else {
							$rating = false;
						}
						
						
						if (in_array($product_info['product_id'], $newest)) {
							$isNewest = true;
						} else {
							$isNewest = false;
						}

				        $catch = false;
				        $nocatch = false;
				        if (isset($labelsInfo['catch']) && $labelsInfo['catch']['status'] && $product_info['quantity'] <= $labelsInfo['catch']['qty']) {
				          if($product_info['quantity'] > 0){
				            $catch = $labelsInfo['catch']['name'][$this->config->get('config_language_id')];
				          }else{
				            $catch = $labelsInfo['catch']['name1'][$this->config->get('config_language_id')];
				            $nocatch = true;
				          }
				        }

				        $popular = false;
				        if (isset($labelsInfo['popular']) && $labelsInfo['popular']['status'] && $product_info['viewed'] >= $labelsInfo['popular']['views']) {
				          $popular = $labelsInfo['popular']['name'][$this->config->get('config_language_id')];
				        }

				        $hit = false;
				        if (isset($labelsInfo['hit']) && $labelsInfo['hit']['status']) {
				          if (isset($hits[$product_info['product_id']])) {
				            $hit = $labelsInfo['hit']['name'][$this->config->get('config_language_id')];
				          }
				        }

						
						$discount = '';	
						if($sales && $special){
							if($labelsInfo['sale']['extra'] == 1){
								$discount = round((($product_info['price'] - $product_info['special'])/$product_info['price'])*100);
								$discount = $discount. ' %';
							}
							if($labelsInfo['sale']['extra'] == 2){
								$discount = $this->currency->format($this->tax->calculate(($product_info['price'] - $product_info['special']), $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							}					
						}	

						$row_id = 0;
						if (isset($product['row_id'])) {
							$row_id = $product['row_id'];
						}	

						

						if($special){
							$total += $product_info['special']*$product['quantity'];
						}else{
							$total += $product_info['price']*$product['quantity'];
						}					

					
			
						$products[] = array(
							'product_id'  => $product_info['product_id'],
							'thumb'       => $image,
							'name'        => $product_info['name'],
							'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
							'price'       => $price,
							'isnewest'       => $isNewest,
							'sales'       => $sales,
							'discount'       => $discount,
					          'catch'       => $catch,
					          'popular'   => $popular,
					          'hit'    	  => $hit,
					          'nocatch'       => $nocatch,
					          'row_id'	  => $row_id,
							'special'     => $special,
					        'countvariants' => $countVariants,
							'tax'         => $tax,
							'rating'      => $rating,
							'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
							'quantity'		  => $product['quantity'],
							'sort_order'		  => $product['sort_order']
						);
					}
				}

		$data = array(
			'products' => $products,
			'total'	   => $total,
			'labelsinfo'         => $labelsInfo,
			'language_id' => $this->config->get('config_language_id')
		);

		return $data;
	}

		
}