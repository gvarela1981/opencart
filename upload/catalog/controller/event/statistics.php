<?php
class ControllerEventStatistics extends Controller {
	// catalog/model/catalog/review/addReview/after
	public function addReview(&$route, &$args, &$output) {
		$this->load->model('report/statistics');

		$this->model_report_statistics->addValue('review', 1);	
	}
		
	// catalog/model/account/return/addReturn/after
	public function addReturn(&$route, &$args, &$output) {
		$this->load->model('report/statistics');

		$this->model_report_statistics->addValue('return', 1);	
	}
	
	// catalog/model/checkout/order/addOrderHistory/before
	public function addOrderHistory(&$route, &$args, &$output) {
		$this->log->write('addOrderHistory $route: ' . $route . ' $args: ' . json_encode($args) . ' $output: ' . $output);

		$this->load->model('checkout/order');
				
		$order_info = $this->model_checkout_order->getOrder($args[0]);

		if ($order_info) {
			$this->load->model('report/statistics');
			
			// If order status in complete or processing add value to sale total
			if (in_array($args[1], array_merge((array)$this->config->get('config_processing_status'), (array)$this->config->get('config_complete_status')))) {
				$this->model_report_statistics->addValue('order_sale', $order_info['total']);

				$this->log->write('complete or processing');
			}
			
			// If order status not in complete or processing remove value to sale total
			if (!in_array($args[1], array_merge((array)$this->config->get('config_processing_status'), (array)$this->config->get('config_complete_status')))) {
				$this->model_report_statistics->removeValue('order_sale', $order_info['total']);

				$this->log->write('not complete or not processing');
			}
			
			// Remove from processing status if new status is not array
			if (in_array($order_info['order_status_id'], (array)$this->config->get('config_processing_status')) && !in_array($args[1], (array)$this->config->get('config_processing_status'))) {
				$this->model_report_statistics->removeValue('order_processing', 1);

				$this->log->write('processing');
			}
			
			// Add to processing status if new status is not array		
			if (!in_array($order_info['order_status_id'], (array)$this->config->get('config_processing_status')) && in_array($args[1], (array)$this->config->get('config_processing_status'))) {
				$this->model_report_statistics->addValue('order_processing', 1);

				$this->log->write('not processing');
			}
			
			// Remove from complete status if new status is not array
			if (in_array($order_info['order_status_id'], (array)$this->config->get('config_complete_status')) && !in_array($args[1], (array)$this->config->get('config_complete_status'))) {
				$this->model_report_statistics->removeValue('order_complete', 1);

				$this->log->write('not complete');
			}
			
			// Add to complete status if new status is not array		
			if (!in_array($order_info['order_status_id'], (array)$this->config->get('config_complete_status')) && in_array($args[1], (array)$this->config->get('config_complete_status'))) {
				$this->model_report_statistics->addValue('order_complete', 1);

				$this->log->write('complete');
			}
		}
	}
}