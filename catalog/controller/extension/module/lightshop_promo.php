<?php
class ControllerExtensionModuleLightshopPromo extends Controller {
	public function index($setting) {
		static $module = 0;		

		$this->load->model('design/banner');
		$this->load->model('tool/image');
		$data['lazyload'] = $this->config->get('theme_lightshop_lazyload');

		$data['banners'] = array();

		if(isset($setting['promo_image'])){
			foreach ($setting['promo_image'] as  $result) {
				if(!isset($result['language'][$this->config->get('config_language_id')])){ continue; }
				$result = $result['language'][$this->config->get('config_language_id')];
				if (is_file(DIR_IMAGE . $result['image'])) { 
					$data['banners'][$result['sort_order']][] = array(
					'title' 			=> $result['title'],
					'link'  			=> $result['link'],
					'text_big' 		    => html_entity_decode($result['text_big'], ENT_QUOTES, 'UTF-8'),
					'text_small'  		=> html_entity_decode($result['text_small'], ENT_QUOTES, 'UTF-8'),
					'width'  		    => $result['width'],
					'text_position' 	=> $result['text_position'],
					'image' 			=> $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				);
				}
			}			
		}
		
		ksort($data['banners']);
		$data['module'] = $module++;

		return $this->load->view('extension/module/lightshop_promo', $data);
		
	}
}
?>
