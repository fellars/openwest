<?php
namespace bc\modules\company;

use bc\modules\company\base\BaseCompanyModule;
use o_payments\services\BalancedService;
use tip\core\Util;
use tip\models\Accounts;
use tip\models\Endpoints;
use tip\models\Users;
use tip\screens\elements\Alert;
use tip\screens\elements\ButtonGrid;
use tip\screens\elements\CheckoutForm;
use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\elements\FormFieldSet;
use tip\screens\elements\Raw;
use tip\screens\helpers\base\HtmlTags;
use tip\traits\account\MasterTrait;
use tip\screens\elements\Table;

class CompanySettingsModule extends BaseCompanyModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

    public function load($screen,$options){
        $grid = $screen->blockGrid();
		$screen->gridSize(12);
		$form = Form::create('settings',$grid);
		$form->disableActionBorder(false);
		$form->submitButton(false);
		$form->data('menuLocation','left');
		$form->data('navType','tabs');
		$form->data('stacked',true);
		$form->data('minHeight','200');
		$form->data('actionOffset','100');
		//        $form->data('minWidth','200');
		$this->_loadForm($form);
		Raw::create('submitButton',$grid,HtmlTags::submitFormButton('Save',"settings",array(),array('style'=>'float:right;')));
		return $options['render'] ? $screen->render() : $screen;
    }

	protected function _loadForm($form){
		$account = \tip\models\Accounts::current();
		$grid = $form->parent();
		$grid->title($account->name() . ' Settings');
		$form->steps('plan::Your Plan,cc::Manage Credit Card,payments');

		$fs = FormFieldSet::create($step = 'plan',$form,array('step'=>$step));
		$plan = FormField::field('raw','plan',$fs,array('label'=>false,));
		$plans = ButtonGrid::create('plans',$plan);
		$plans->addIconButton("Starter", "starter", 'pencil');
		$plans->addIconButton("Corporate", "corporate", 'pencil');
		$plans->addIconButton("Enterprise", "enterprise", 'pencil',HtmlTags::icon('star'));
		$fs = FormFieldSet::create($step = 'cc',$form,array('step'=>$step));
		$a = Accounts::current();
		static::addLib('o_payments');
		$trait = BalancedService::traitPath('account');
		$hasCC = $trait::hasTrait($a);
		$msg = "No Card on File";
		if($hasCC){
			$card =  $trait::getCard($a);
			if($card){
				$last4 = Util::nvlA($card,'last4');
				$msg = "Current Card on File: $last4";
			}else{
				$hasCC = false;
			}
		}
		FormField::raw('ccRaw',$fs,false,$msg."<br/>".HtmlTags::modalButton($hasCC ? 'Update Your CC' : 'Enter Your CC','cc:update'));
		$this->screenAction('cc:update',true);

		$fs = FormFieldSet::create($step = 'payments',$form,array('step'=>$step));
		$pRaw = FormField::raw('pRaw',$fs,false,"");
		$table = Table::create('payments',$pRaw);
		$table->addHeaders('date,card,subject,amount');
		if($hasCC){
			$debits = $trait::tData2A($a,'debits');
			$table->addRows($debits,array(
				'date'=>function($val,$key,$row){

				},
				'card'=>function($val,$key,$row){

				},
				'subject'=>function($val,$key,$row){

				},
				'amount'=>function($val,$key,$row){

				},
			));
		}

		return $form;


	}

	public function cc_update($screen){
		$user = Users::current();
		$a = Accounts::current();
		static::addLib('o_payments');
		$hasCC = \o_payments\traits\account\BalancedTrait::hasTrait($a);
		$ccInfo = array();
		$ccForm = Form::create('cc',$screen,array('submitButtonText'=>$hasCC ? 'Update' : 'Save'));

		CheckoutForm::insertCCFields($ccForm,array('value'=>$ccInfo));
		FormField::email('email',$ccForm,'Associated Email',Util::nvlA($ccInfo,'email'),'','',true);
		return $ccForm->render(array('buttons'=>false,'title'=> $hasCC ? 'Update Your CC' : 'Enter Your CC'));
	}

	public function form_cc($form,$data){
		$account = Accounts::current();
		static::addLib('o_payments');
		$master = MasterTrait::masterFindFirst($this);
		$balanced = MasterTrait::tData($master,'paymentService');
		$service = $this->_runAsMasterAccount(function() use($balanced, $data){
			return Endpoints::serviceById($balanced);
		});
		$action = 'createCard';
		$result = $service->process($data+compact('account','action'));
		if($result){
			$topForm = $form->screen()->blockGrid()->element('settings');
			$this->_loadForm($topForm);
			Alert::create('success',$topForm,'Credit Card Updated.');
			return $topForm->render();
		}else{
			Alert::create('error',$form,'There was an error updating the Credit Card.');
			return $form->render();
		}
	}

	public function form_settings($form,$data){

		$this->_loadForm($form);
		Alert::create('success',$form,'Your settings has been updated.');
		return $form->screen()->blockGrid()->render();
	}
}
