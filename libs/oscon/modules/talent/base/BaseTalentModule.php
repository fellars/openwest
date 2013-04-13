<?php
namespace bc\modules\talent\base;

use bc\modules\base\BaseModule;
use tip\controls\Module;
use tip\core\Util;

class BaseTalentModule extends BaseModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

	protected function _companyUrl($company){
		$url = "/bc/home/company/";
		if($company){
			$name = $company->name();
			$id = $company->id();
			$u = Util::inflect($name,'u');
			$url .= "$u/$id";
		}
		return $url;

	}

}
