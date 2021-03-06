<?php

namespace bc\traits\account;


use tip\screens\elements\Raw;
use tip\screens\elements\TableBlock;
use tip\screens\helpers\base\HtmlTags;
use bc\traits\account\base\BCTrait;
use tip\core\Util;
use tip\screens\elements\FormField;
use tip\screens\elements\Table;
use tip\screens\elements\Form;

class CorporateSettingsTrait extends BCTrait{

	public function __construct(array $config = array()) {
		parent::__construct($config);
	}

	protected function _init() {
		parent::_init();
		$this->config('types', 'corporate');
	}

	protected $_schema = array(
		'main'=>array(
			'fields'=>array(

			)
		),


	);



}
