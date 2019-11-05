<?php
class ControllerExtensionModuleLightshopBlogMod extends Controller {
	public function index($settings) {
		$this->load->language('extension/module/lightshop_blog_mod');

		$data['heading_title'] = $settings['name'];
		$data['text_all_blogs'] = $this->language->get('text_all_blogs');

		$this->load->model('extension/module/lightshopblog');
		$this->load->model('tool/image');
		
		$data['lazyload'] = $this->config->get('theme_lightshop_lazyload');
		$data['blog_href'] = $this->url->link('extension/module/lightshopcat_blog/getcat&lbpath=0');

		$limit = $settings['limit'];
		$page = 1;
		$order = 'DESC';

		$category_id = 0;
		if($settings['main_category_id']){
			$category_id = $settings['main_category_id'];
		}
		
			$filter_data = array(
				'filter_category_id'  => $category_id,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);


		$data['blogs'] = array();

		foreach ($this->model_extension_module_lightshopblog->getBlogs($filter_data) as $result) {

				if ($this->config->get('theme_lightshop_image_blog_cat_resize')) {
					if ($result['image']) {
						$image = $this->model_tool_image->lightshop_resize($result['image'], 300, 200);
					} else {
						$image = $this->model_tool_image->lightshop_resize('placeholder.png', 300, 200);
					}
				} else {
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], 300, 200);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', 300, 200);
					}
				}


			$data['blogs'][] = array(
				'title' => $result['title'],
				'image'       => $image,
				'date_added' => $this->rus_date("j F, Y ", strtotime($result['date_added'])),
				'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 200) . '...',
				'href'  => $this->url->link('extension/module/lightshop_blog/getblog', 'blog_id=' . $result['blog_id'])
			);
		}

		if(isset($settings['layout']) && strpos($settings['layout'],'column_') !== false){
			return $this->load->view('extension/module/lightshop_blog_mod_column', $data);
		}else{
			return $this->load->view('extension/module/lightshop_blog_mod', $data);
		}

		
	}


	public function rus_date() {
		$this->load->language('extension/module/lightshop_blog_mod');
			// Перевод
			 $translate = array(
					 'am' => $this->language->get('text_am'),
					 'pm' => $this->language->get('text_pm'),
					 'AM' => $this->language->get('text_AM'),
					 'PM' => $this->language->get('text_PM'),
					 'Monday' => $this->language->get('text_Monday'),
					 'Mon' => $this->language->get('text_Mon'),
					 'Tuesday' => $this->language->get('text_Tuesday'),
					 'Tue' => $this->language->get('text_Tue'),
					 'Wednesday' => $this->language->get('text_Wednesday'),
					 'Wed' => $this->language->get('text_Wed'),
					 'Thursday' => $this->language->get('text_Thursday'),
					 'Thu' => $this->language->get('text_Thu'),
					 'Friday' => $this->language->get('text_Friday'),
					 'Fri' => $this->language->get('text_Fri'),
					 'Saturday' => $this->language->get('text_Saturday'),
					 'Sat' => $this->language->get('text_Sat'),
					 'Sunday' => $this->language->get('text_Sunday'),
					 'Sun' => $this->language->get('text_Sun'),
					 'January' => $this->language->get('text_January'),
					 'Jan' => $this->language->get('text_Jan'),
					 'February' => $this->language->get('text_February'),
					 'Feb' => $this->language->get('text_Feb'),
					 'March' => $this->language->get('text_March'),
					 'Mar' => $this->language->get('text_Mar'),
					 'April' => $this->language->get('text_April'),
					 'Apr' => $this->language->get('text_Apr'),
					 'May' => $this->language->get('text_May'),
					 'June' => $this->language->get('text_June'),
					 'Jun' => $this->language->get('text_Jun'),
					 'July' => $this->language->get('text_July'),
					 'Jul' => $this->language->get('text_Jul'),
					 'August' => $this->language->get('text_August'),
					 'Aug' => $this->language->get('text_Aug'),
					 'September' => $this->language->get('text_September'),
					 'Sep' => $this->language->get('text_Sep'),
					 'October' => $this->language->get('text_October'),
					 'Oct' => $this->language->get('text_Oct'),
					 'November' => $this->language->get('text_November'),
					 'Nov' => $this->language->get('text_Nov'),
					 'December' => $this->language->get('text_December'),
					 'Dec' => $this->language->get('text_Dec'),
					 'st' => $this->language->get('text_st'),
					 'nd' => $this->language->get('text_nd'),
					 'rd' => $this->language->get('text_rd'),
					 'th' => $this->language->get('text_th'),
			 );
			 // если передали дату, то переводим ее
			 if (func_num_args() > 1) {
				$timestamp = func_get_arg(1);
			 return strtr(date(func_get_arg(0), $timestamp), $translate);
			 } else {
			// иначе текущую дату
				return strtr(date(func_get_arg(0)), $translate);
			 }
	}


}
