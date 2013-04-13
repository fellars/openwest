<?php
namespace bc\modules\hitch;

use \tip\controls\Module;
use bc\modules\hitch\base\BaseHitchModule;
use tip\screens\elements\FormField;
use tip\screens\elements\FormFieldSet;
use tip\screens\helpers\base\HtmlTags;

class CompaniesModule extends BaseHitchModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
       
    }

    public function load($screen, $options=array()){
        $this->addLib('bc');
        $screen->initDurcModel(\bc\models\Companies::modelClass());
		$screen->allowClone(false);
		$screen->allowEdit(false);
		$screen->updateUrl("/bc/hitch/companies");
		$screen->callbackLabel('companies');
		$screen->screenJs('jsoneditor',true);
		$screen->screenCss('jsoneditor',true);
		$this->screenAction("companies:item:post_view",true);
        $id = $this->requestData('company');
		if($id){
			$screen->view_item($id,false);
		}
        return $options['render'] ? $screen->render() : $screen;
    }
	public function companies_item_post_view($screen,$viewBlock,$entity){
		$form = $viewBlock->element('item');
		$form->addSteps('raw');
		$rawFs = FormFieldSet::create('raw',$form,array('step'=>'raw'));
		FormField::raw('saveTop',$rawFs,HtmlTags::submitFormButton('Save Changes','item'));
		$json = FormField::json('raw',$rawFs,false,$entity->data(),'',false,array('readOnly'=>false,'expand'=>true, 'width'=>1000,'height'=>1500));
		FormField::raw('saveBottom',$rawFs,HtmlTags::submitFormButton('Save Changes','item'));
		$json->data('formLayout',false);
	}



}


?>