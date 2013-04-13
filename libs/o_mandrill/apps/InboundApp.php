<?php
namespace o_mandrill\apps;

use tip\controls\App;
class InboundApp extends App{
	public function __construct(array $config = array()) {
		parent::__construct($config);
	}

	protected function _init() {
		parent::_init();
		$this->action('','response');
	}


	public function response(){

	}


}