<?php
namespace bc\modules\talent;

use bc\traits\user\TalentTrait;
use tip\controls\Module;
use tip\core\Util;
use tip\models\Users;
use tip\screens\elements\Alert;
use tip\screens\elements\Raw;
use tip\screens\helpers\base\HtmlTags;
use tip\screens\elements\Block;
use tip\screens\elements\Menu;
use tip\screens\elements\Table;
use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\elements\FormFieldSet;
use bc\modules\talent\base\BaseTalentModule;

class ProfileModule extends BaseTalentModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

    public function load($screen, $options = array()){
        $grid = $screen->blockGrid();

        $grid->buttons(array(
			HtmlTags::submitFormButton('Save','form'),
//			HtmlTags::refreshButton('Import From LinkedIn','linkedin:import')
		));
        $this->screenAction('linkedin:import',	'import_linkedin');
        $this->screenAction('menu:load','load_menu');
        $screen->gridSize(10);
        $form = Form::create('form',$grid);
        $form->disableActionBorder(false);
		$form->submitButton(false);
        $form->data('menuLocation','left');
        $form->data('navType','tabs');
        $form->data('stacked',true);
        $form->data('minHeight','200');
        $form->data('actionOffset','100');
//        $form->data('minWidth','200');
        $form->steps('skills,preferences,settings,core::Profile');
        $this->_loadForm($form);
		Raw::create('submitButton',$grid,HtmlTags::submitFormButton('Save',"form",array(),array('style'=>'float:right;')));
        return $options['render'] ? $screen->render() : $screen;
    }

    protected function _loadForm($form){
        $user = \tip\models\Users::current();
        $user->core()->hideType();
		$user->core()->hideStatus();
        $user->form('edit',$form);

		$this->screenAction('skills:table:addRow','skills_table_row_add');

		return $form;


    }
	public function skills_table_row_add($screen){
		$user = Users::current();
		$table = $screen->blockGrid()->element('form')->field('skills')->element('skillsTable');
		$forms = TalentTrait::get($user)->skillsTableRowAdd($table,array(false));
		$lastRow = $table->getRow(-1);
		$screen->session('to');
		return $this->renderJson(array('forms'=>$forms,'row'=>$lastRow));

	}




    public function import_linkedin($screen){
        echo "import linkedin<pre>"; print_r( array() ); echo "</pre>";exit;
    }

    public function form_form($form,$data){
        $user = \tip\models\Users::current();
        $user->formProcess('edit',$form,true);
		$this->_loadForm($form);
		Alert::create('success',$form,'Your profile has been updated.');
        return $form->screen()->blockGrid()->render();
//        $userData = $user->data();
//        echo "<pre>"; print_r( $form->fieldValues() ); print_r($userData); echo "</pre>";exit;
    }


}
