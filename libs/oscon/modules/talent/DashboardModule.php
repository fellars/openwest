<?php
namespace bc\modules\talent;

use tip\core\Util;
use tip\screens\helpers\base\HtmlTags;
use bc\modules\talent\base\BaseTalentModule;

class DashboardModule extends BaseTalentModule
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
		$companies = \bc\models\Companies::currentUserSavedCompanies($this->_db());
		$grid->buttons(HtmlTags::link('View Next Match','/bc/home/company/next',array('class'=>'btn')));
		$grid->data('buttonsLeft',true);
		if(count($companies) == 0 ){
			\tip\screens\elements\Raw::create('test',$grid,"<h1>Welcome</h1><h4>Let's get you hooked up.</h4>");

		}else{

			$buttons = \tip\screens\elements\ButtonGrid::create('companies',$grid);
			$buttons->boxWidth(135);
			$buttons->boxHeight(90);
			$buttons->iconSize(42);
			foreach($companies as $company){
				$buttons->addImgButton($company->name(),$this->_companyUrl($company),'','');
			}
		}

    }


}
