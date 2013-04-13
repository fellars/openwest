<?php
namespace bc\modules\company;

use bc\modules\company\base\BaseCompanyModule;
use tip\core\Util;
use tip\screens\helpers\base\HtmlTags;

class CompanyDashboardModule extends BaseCompanyModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

    public function load($screen){
        $grid = $screen->blockGrid();
		$me = \tip\models\Users::current();

    }


}
