<?php
namespace o_mandrill\traits\endpoint;

use tip\traits\base\BaseEndpointTrait;

class MandrillTrait extends BaseEndpointTrait{
	public function __construct(array $config = array()) {
		parent::__construct($config);
	}

	protected $_schema = array(
		'fields'=>array(
			'apiKey'=>array('type'=>'encrypted','required'=>true),
			'from'=>array('type'=>'text','label'=>'Default From'),

		)
	);


}