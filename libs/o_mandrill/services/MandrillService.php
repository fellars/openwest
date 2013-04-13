<?php
namespace o_mandrill\services;

use _services\services\base\BaseEmailService;
use tip\core\Util;

class MandrillService extends BaseEmailService{
	public function __construct(array $config = array()) {
		$defaults = array('host'=>'mandrillapp.com','base'=>'api/1.0','endpointMap'=>'apiKey,from,replyTo');
		parent::__construct($config + $defaults);
	}

	protected function _init() {
		parent::_init();
	}

	public function base($val = null) {
		return $this->config(__FUNCTION__, $val);
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
		$message = array('key'=>$this->apiKey());
		$html = is_array($msg) ? Util::nvlA($msg,'html') : $msg;
		$text = is_array($msg) ? Util::nvlA($msg,'text') : $msg;
		$from_email = $this->from();
		if(is_string($to)){
			$toList = Util::toArray($to);
			$to = array();
			foreach($toList as $toIter){
				$to[] = array('email'=>$toIter);
			}

		}
		$message['message'] = $options + compact('subject','html','text','to','from_email','attachments');
		return $this->post("messages/send",$message);
	}

	public function send($type, $path = null, $data = array(), array $options = array()) {
		$base = $this->base();
		return parent::send($type, "$base/$path.json", $data, $options);
	}


}