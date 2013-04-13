<?php
namespace bc\modules\company;

use bc\modules\company\base\BaseCompanyModule;
use bc\traits\user\CorporateTrait;
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


class CompanyProfileModule extends BaseCompanyModule
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

//        $grid->buttons(HtmlTags::refreshButton('Import From LinkedIn','linkedin:import'));
        $this->screenAction('linkedin:import','import_linkedin');
        $this->screenAction('menu:load','load_menu');
		$screen->screenJs('wysiwyg',true);
		$screen->screenCss('wysiwyg',true);
        $screen->gridSize(12);
        $form = Form::create('profile',$grid);
        $form->disableActionBorder(false);
		$form->submitButton(false);
        $form->data('menuLocation','left');
        $form->data('navType','tabs');
        $form->data('stacked',true);
        $form->data('minHeight','200');
        $form->data('actionOffset','100');
//        $form->data('minWidth','200');
        $this->_loadForm($form);
		$this->screenAction('team:table:addRow','team_add_row');
		$this->screenAction('media:table:addRow','media_add_row');
		Raw::create('submitButton',$grid,HtmlTags::submitFormButton('Save',"profile",array(),array('style'=>'float:right;')));
        return $options['render'] ? $screen->render() : $screen;
    }

    protected function _loadForm($form){
        $account = \tip\models\Accounts::current();
		$grid = $form->parent();
		$grid->title($account->name() . ' Profile');
		$form->steps('main::Profile,team,details,match::Public Presence,tech::Technology,defaults');
		//team,media
		$account->form('edit',$form);
		return $form;


    }



    public function form_profile($form,$data){
		$account = \tip\models\Accounts::current();
        $account->formProcess('edit',$form,true);
		$this->_loadForm($form);
		Alert::create('success',$form,'Your profile has been updated.');
        return $form->screen()->blockGrid()->render();
//        $userData = $user->data();
//        echo "<pre>"; print_r( $form->fieldValues() ); print_r($userData); echo "</pre>";exit;
    }

	public function team_add_row($screen){
		$account = \tip\models\Accounts::current();
		$table = $screen->blockGrid()->element('profile')->field('team')->element('teamTable');
		$forms = \bc\traits\account\CorporateTrait::get($account)->teamTableRowAdd($table,array(false));
		$lastRow = $table->getRow(-1);
		$screen->session('to');
		return $this->renderJson(array('forms'=>$forms,'row'=>$lastRow));

	}
	public function media_add_row($screen){
		$this->dumpPostData();
		$account = \tip\models\Accounts::current();
		$table = $screen->blockGrid()->element('profile')->field('media')->element('mediaTable');
		$mode = $this->requestData('mode');
		$forms = \bc\traits\account\CorporateTrait::get($account)->mediaTableRowAdd($table,array(false),$mode);
		$lastRow = $table->getRow(-1);
		$screen->session('to');
		return $this->renderJson(array('forms'=>$forms,'row'=>$lastRow));

	}
}
