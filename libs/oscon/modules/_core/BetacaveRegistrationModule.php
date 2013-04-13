<?php
namespace bc\modules\_core;

use _core\modules\_core\DefaultRegistrationModule;
use bc\traits\user\TalentTrait;
use tip\core\Util;
use tip\models\Accounts;
use tip\models\Users;
use tip\screens\elements\Alert;
use tip\screens\elements\ButtonGrid;
use tip\screens\elements\FormField;
use tip\screens\elements\FormFieldSet;
use tip\traits\CoreUserTrait;

class BetacaveRegistrationModule extends DefaultRegistrationModule{
	public function __construct(array $config = array()) {
		parent::__construct($config);
	}

	protected function _init() {
		parent::_init();

	}

	public function registrationForm($screen, $settings = array()) {
		$u = CoreUserTrait::findFirst(array('email'=>'danny@fellars.com'));
		if($u){
			Accounts::deleteById($u->account);
			Users::deleteById($u);
		}


		$form = $settings['form'];
		$fs = FormFieldSet::create('typeFs',$form,array('step'=>'type'));
		FormField::hidden('userType',$fs);

		$fs = FormFieldSet::create('company',$form,array('step'=>'company'));
		FormField::reqField('text','companyName',$fs);
		FormField::reqField('text','companyDomain',$fs,array('placeholder'=>'example.com'));

		$fs = FormFieldSet::create('recruiter',$form,array('step'=>'recruiter'));

		$form->steps('type');
		$form->submitButton(false);
		$block = $form->parent();
		$buttons = ButtonGrid::create('type',$block);
		$buttons->boxWidth(200);
		$buttons->boxHeight(100);
		$buttons->iconSize(60);
		$buttons->addIconButton('Talent','#submitForm','pencil',false,array('form'=>'register','type'=>'talent'));
		$buttons->addIconButton('Company','#submitForm','pencil',false,array('form'=>'register','type'=>'company'));
//		$buttons->addIconButton('Recruiter','#submitForm','pencil',false,array('form'=>'register','type'=>'recruiter'));
		return parent::registrationForm($screen, $settings);
	}

	public function registrationFormSubmit($screen, $settings = array()) {
		$grid = $screen->blockGrid();
		$form = $settings['form'];
		$data = $settings['data'];
		$step = $data['activeStep'];
		$form->activeStep(false);
		if($step == 'type'){
			$type = $data['type'];
			$form->field('userType')->value($type);
			if($type == 'company'){
				$form->steps('company');
				$form->submitButtonText('Next');
			}else if ($type == 'talent'){
				$form->steps('core');
				$form->submitButtonText('Register');
			}else if ($type == 'recruiter'){
				$form->steps('recruiter');
				$form->submitButtonText('Register');
			}

			$grid->title("Register - " . Util::inflect($type,'h'));
			$form->submitButton(true);
			$grid->removeElement('type');
			$grid->render();

		}else if($step == 'company'){
			$form->steps('core');
			$form->submitButtonText('Register');
			$grid->title("Register - " . $data['companyName']);
			$grid->render();

		}else if($step == 'recruiter'){

		}else if ($step == 'core'){
			return true;
		}else if ($step == 'preferences'){
			//this is after user saved
			$user = Users::current();
			$talent = TalentTrait::get($user);
			$talent->formStepProcess('create','preferences',$form);
			$user->save();
			$talent->formStepPrepare('create','skills',$form);
			$form->steps('skills');
			$this->screenAction('skills:table:addRow','skills_table_row_add');
			$screen->control($this);
			$grid = $screen->blockGrid();
			$grid->title('Your Skills');
			$form->submitButtonText('Done');
			$grid->render();

		}else if($step == 'skills'){
			$user = Users::current();
			$talent = TalentTrait::get($user);
			$talent->formStepProcess('create','skills',$form);
			$user->save();
			return $this->redirect('/');
		}else if($step == 'details'){
			$account = Accounts::current();
			$trait = \bc\traits\account\CorporateTrait::get($account);
			$trait->formStepProcess('create','details',$form);
			$account->save();
			return $this->redirect('/');

		}
		return false;
	}

	public function postRegistration($screen, $settings = array()) {
		$form = $settings['form'];
		$data = $settings['data'];
		$type = $data['userType'];
		$account = Accounts::current();
		$user = Users::current();
		$form->activeStep(false);
		switch($type){
			case 'recruiter':
				$account->type = array('recruiter');
				$account->addTrait(new \bc\traits\account\RecruiterTrait());
				$account->addTrait(new \bc\traits\account\RecruiterSettingsTrait());

				$user->type = array('recruiter');
				$user->addTrait(new \bc\traits\user\RecruiterTrait());
				break;
			case 'company':
				$account->type = array('corporate');
				$account->addTrait(new \bc\traits\account\CorporateTrait());
				$account->addTrait(new \bc\traits\account\CorporateSettingsTrait());
				$account->name = $data['companyName'];
				\bc\traits\account\CorporateTrait::tData($account,'companyDomain',$data['companyDomain']);

				$user->type = array('corporate');
				$user->addTrait(new \bc\traits\user\CorporateTrait());
				break;
			case 'talent':
				$account->type = array('standard');

				$user->type = array('talent');
				$user->addTrait(new \bc\traits\user\TalentTrait());
				break;
		}


		$user->save();
		$account->save();

		switch($type){
			case 'talent':
				$talent = TalentTrait::get($user);
				$talent->formStepPrepare('create','preferences',$form);
				$form->steps('preferences');
				$grid = $screen->blockGrid();
				$grid->title('Your Preferences');
				Alert::create('success',$form,'Welcome '. $user->firstName() . '. To get started, we need to know just a little about you.');
				$form->submitButtonText('Save');
				$this->sendConfirmation();
				return $grid->render();
				break;
			case 'company':
				$trait = \bc\traits\account\CorporateTrait::get($account);
				$trait->formStepPrepare('create','details',$form);
				$form->steps('details');
				$grid = $screen->blockGrid();
				$grid->title($account->name().' Details');
				Alert::create('success',$form,'Welcome '. $user->firstName() . '. To get started, we need to know just a little about your company.');
				$form->submitButtonText('Save');
				$this->sendConfirmation();
				return $grid->render();


				break;
			case 'recruiter':
				break;
		}
		return parent::postRegistration($screen,$settings);
//		$this->sendConfirmation();
//
//		//ask more info
//
//		//take control of callbacks
//		$screen->control($this);


	}

	public function form_register($form,$data){
		return $this->registrationFormSubmit($form->screen(),compact('data','form'));
	}

	public function skills_table_row_add($screen){
		$user = Users::current();
		$table = $screen->blockGrid()->element('register')->field('skills')->element('skillsTable');
		$forms = TalentTrait::get($user)->skillsTableRowAdd($table,array(false));
		$lastRow = $table->getRow(-1);
		$screen->session('to');
		return $this->renderJson(array('forms'=>$forms,'row'=>$lastRow));

	}



}