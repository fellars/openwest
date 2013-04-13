<?php

namespace bc\traits\opportunity;

use bc\traits\user\TalentTrait;
use tip\controls\ModelTrait;
use tip\screens\elements\Raw;
use tip\screens\elements\TableBlock;
use tip\screens\helpers\base\HtmlTags;
use tip\core\Util;
use tip\screens\elements\FormField;
use tip\screens\elements\Table;
use tip\screens\elements\Form;
use tip\models\Accounts;

class CorporateTrait extends ModelTrait{

	public function __construct(array $config = array()) {
		parent::__construct($config);
	}

	protected function _init() {
		parent::_init();
		$this->config('types', 'corporate');
	}

	protected $_schema = array(
		'overview'=>array(
			'fields'=>array(
				'active'=>array('type'=>'hidden'),
				'contactEmail'=>array('type'=>'email','required'=>true),
				'corporate'=>array('type'=>'hidden'),
			)
		)
	);

	protected function _prePrepareContactEmail($field){
		$account = Accounts::current();
		if(!Util::nvlA($field,'value')){
			$default = \bc\traits\account\CorporateTrait::ifHasData($account,'defaultContactEmail');
			$field['value'] = $default;
		}

		return $field;
	}

	protected function _postPrepareContactEmail($field,$action,$step,$form){
		$form->moveField($field,7);
		return $field;
	}
}
