<?php
namespace o_postmark\services;

use _services\services\base\BaseEmailService;
class PostmarkService extends BaseEmailService{
	public function __construct(array $config = array()) {
		$defaults = array('host'=>'api.postmarkapp.com','endpointMap'=>'apiKey,from,replyTo');
		parent::__construct($config + $defaults);
	}

	protected function _init() {
		parent::_init();
	}



	public function from($val = null) {
		return $this->config(__FUNCTION__, $val);
	}

	public function replyTo($val = null) {
		return $this->config(__FUNCTION__, $val);
	}

	public function apiKey($val = null) {
		return $this->config(__FUNCTION__, $val);
	}

	public function sendMail($to, $subject, $msg, $attachments = array(), $options = array()) {

	}


}