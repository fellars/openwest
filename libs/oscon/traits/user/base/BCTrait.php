<?php

namespace bc\traits\user\base;

use tip\screens\elements\Raw;
use tip\screens\elements\TableBlock;
use tip\screens\helpers\base\HtmlTags;
use tip\traits\base\BaseUserTrait;
use tip\core\Util;
use tip\screens\elements\FormField;
use tip\screens\elements\Table;
use tip\screens\elements\Form;

class BCTrait extends BaseUserTrait {
	protected static $_masterMapping = null;

	public function __construct(array $config = array()) {
		parent::__construct($config);
	}

	protected function _init() {
		parent::_init();
	}


	public static function masterMap($path) {
		if (static::$_masterMapping === null) {
			static::$_masterMapping = Util::toArray(\bc\apps\MasterApp::masterSetting('mappings'));
		}
		return Util::nvlA2A(static::$_masterMapping, $path);
	}

}
