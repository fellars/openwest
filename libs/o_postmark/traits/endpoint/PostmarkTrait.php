<?php
namespace o_postmark\traits\endpoint;

use tip\traits\base\BaseEndpointTrait;

class PostmarkTrait extends BaseEndpointTrait{
	public function __construct(array $config = array()) {
		parent::__construct($config);
	}

	protected $_schema = array(
		'fields'=>array(
			'apiKey'=>array('type'=>'encrypted','required'=>true),

		)
	);


}