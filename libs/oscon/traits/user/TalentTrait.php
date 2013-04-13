<?php

namespace bc\traits\user;

use tip\screens\elements\Raw;
use tip\screens\elements\TableBlock;
use tip\screens\helpers\base\HtmlTags;
use bc\traits\user\base\BCTrait;
use tip\core\Util;
use tip\screens\elements\FormField;
use tip\screens\elements\Table;
use tip\screens\elements\Form;

class TalentTrait extends BCTrait {

	public function __construct(array $config = array()) {
		parent::__construct($config);
	}

	protected function _init() {
		parent::_init();
		$this->config('types', 'talent');
	}

	protected $_schema = array(
		'preferences' => array(
			'fields' => array(
				'preferredPosition' => array(
					'type' => 'select',
					'multiple' => true,
					'optionsConfig' => array('value' => '$key', 'label' => 'name'),
					'defaultValue'=>'full_time'


				),
				'preferredRoles' => array(
					'type' => 'select',
					'multiple' => true,
					'optionsConfig' => array('value' => '$key', 'label' => 'name'),


				),
				'preferredLocations' => array(
					'type' => 'select',
					'multiple' => true,
					'optionsConfig' => array('value' => '$key', 'label' => 'name'),

				),
				'preferredMarkets' => array(
					'type' => 'select',
					'multiple' => true,
					'optionsConfig' => array('value' => '$key', 'label' => 'name'),


				),
				'preferredSalary' => array(
					'type' => 'slider',
					'size' => 8,
					'height' => 10,
					'requireSelection' => true,
					'allowCancel' => true,
					'range' => true,
					'labels' => '$50k,$75k,$100k,$125k,$150k+',
					'min' => 50,
					'max' => 150,
				),
				'preferredEquity' => array(
					'type' => 'slider',
					'size' => 8,
					'height' => 10,
					'requireSelection' => true,
					'allowCancel' => true,
					'range' => false,
					'labels' => 'Show Me the $$,It Depends,Ready to Sweat',

				),
				'preferredCompanySize' => array(
					'type' => 'slider',
					'size' => 8,
					'height' => 10,
					'requireSelection' => true,
					'allowCancel' => true,
					'range' => true,
					'label' => 'Preferred Company Size<br/><small>(num employees)</small>',
					'labels' => '1,10,25,100,1000+'
				),
			),
		),
		'skills' => array(
			'fields' => array(
				'skills' => array(
					'type'=>'fullTable',
					'addRowAction'=>'skills:table:addRow',
				),

			),
		),
		'settings' => array(
			'fields' => array(
				'emailInterval' => array(
					'type' => 'slider',
					'size' => 10,
					'height' => 10,
					'requireSelection' => true,
					'allowCancel' => true,
					'range' => false,
					'label' => 'Email Frequency',
					'help' => 'How often to receive new matches via email (at most)',
					'labels' => 'None for now,Monthly,Weekly,Daily,Real-time',
					'defaultValue' => 75,
					'step' => true

				),
				'updateInterval' => array(
					'type' => 'slider',
					'size' => 10,
					'height' => 10,
					'requireSelection' => true,
					'allowCancel' => true,
					'range' => false,
					'label' => 'Update Frequency',
					'help' => 'How often to receive updates on your tracking companies (at most)',
					'labels' => 'None for now,Monthly,Weekly,Daily',
					'defaultValue' => 66,
					'step' => true

				),
			),
		)

	);


	protected function _prePreparePreferredRoles($field) {

		$field['options'] = static::masterMap('roles');
		return $field;
	}

	protected function _prePreparePreferredPosition($field) {

		$field['options'] = static::masterMap('positions');
		return $field;
	}

	protected function _prePreparePreferredLocations($field) {
		$field['options'] = static::masterMap('locations');
		return $field;
	}

	protected function _prePreparePreferredMarkets($field) {
		$field['options'] = static::masterMap('markets');
		return $field;
	}




	protected function _postPrepareSkills($skillsField,$action,$step,$form,$fieldSet){
				//set up skills table

		$vals = Util::toArray($this->data('skills'));

		$table = Table::create('skillsTable', $skillsField);
		$table->data('control', 'skills');
		$table->data('searchBox',false);
		$table->data('showPerPage',false);
		$table->data('showInfo',false);
		$table->data('pageSize',5);
		$table->data('tableHeader',$addLink = HtmlTags::link(HtmlTags::icon('add') . 'New', '#addRow', array('class' => 'btn', 'escape' => false)));
		$table->data('tableFooter',$addLink );
		$table->addHeaders(array('_id', 'skill' => array('width' => '40%', 'render' => true), 'level' => array('width' => '50%', 'render' => true), 'actions' => array('width' => '10%')));
		$formsList = $this->skillsTableRowAdd($table,$vals);

		$skillsField->data('forms', implode(',', $formsList));
		return $skillsField;

	}
	protected function _skills(){
		$roles = Util::toArray($this->data('preferredRoles'));
		$allSkills = static::masterMap('skills');
		$skills = array();
		if ($roles) {
			foreach ($allSkills as $skillKey => $skill) {
				if ($skillRoles = Util::nvlA2A($skill, 'roles')) {
					if (array_intersect($skillRoles, $roles)) {
						$skills[$skillKey] = $skill;
					}
				}
			}
		} else {
			$skills = $allSkills;
		}
		return $skills;
	}
	public function skillsTableRowAdd($table, $rows) {
		$formsList = array();
		$skills = $this->_skills();

		$table->addRows($rows, array(
			'_id' => function ($val, $key, $row, $rowKey,$index) use ($table, $skills, &$formsList) {
				return "row_$index";
			},
			'skill' => function ($val, $key, $row, $rowKey,$index) use ($table, $skills, &$formsList) {
				$form = Form::create("skill_$index", $table, array('formLayout' => 'inline','fieldSetSize'=>12, 'submitButton' => false));
				$formsList[] = $form->name();
				FormField::field('select', 'skill', $form, array('size' => 12, 'label' => false, 'value' => $val, 'allowCustom' => true, 'customFormat' => 'underscore', 'optionsConfig' => array('value' => '$key', 'label' => 'name'), 'options' => $skills));
				return $form->renderQuick();

			},
			'level' => function ($val, $key, $row, $rowKey,$index) use ($table, &$formsList) {
				$val = Util::toArray($val);
				$form = Form::create("level_$index", $table, array('formLayout' => 'inline', 'fieldSetSize'=>12,'submitButton' => false));
				$formsList[] = $form->name();
				FormField::field('slider', 'level', $form,
					array(
						'value' => $val,
						'label' => false,
						'size' => 12,
						'height' => 10,
						'range' => false,
						'requireSelection' => true,
						'allowCancel' => false,
						'labelMarker' => false,
						'labels' => 'Beginner,Intermediate,Expert',
						'defaultValue' => array(50),
						'displayValueTemplate' => "<% var out = ''; if(value < 25) out = 'Beginner'; if (value >=25 && value < 40)out = 'Beginner/Intermediate'; if (value >=40 && value < 60)out = 'Intermediate';if (value >=60 && value < 80)out = 'Intermediate/Advanced'; if (value >=80)out = 'Advanced'; %>(<%- out %>)",

					)
				);
				return $form->renderQuick();

			},
			'actions' => function ($val, $key, $row, $rowKey,$index) use ($table) {
				return HtmlTags::link(HtmlTags::icon('remove') . 'Remove', '#removeRow', array('data-id' => "row_$index", "data-forms" => "skill_$index,level_$index", 'class' => 'btn', 'escape' => false));
			}
		));
		return $formsList;
	}


	protected function _preProcessSkills($field) {
		$skills = Util::toArray($field->value());
		$names = Util::nvlA2A($skills,'_forms');
		$value = array();
		foreach($names as $name){
			list($f,$i) = explode("_",$name);
			if($f == "skill"){
				$skill = Util::nvlA($skills,"skill_$i.default.skill");
				if($skill){
					$level = Util::nvlA($skills,"level_$i.default.level");
					$value[] = compact('skill','level');
				}
			}
		}
		$field->value($value);
		$options = $this->_skills();
		$options = array_keys($options);
		$roles = Util::toArray($field->form()->field('preferredRoles', 'preferences_main')->value());
		foreach ($skills as $skillA) {
			$skill = Util::nvlA($skillA, 'skill');
			if ($skill && !in_array($skill, $options)) {
				//add to map
				$label = Util::inflect($skill, 'h');
				$skill = Util::inflect($label, 'u');
				\bc\apps\MasterApp::addSkill($skill, $label, $roles);
			}
		}
		return $field;
	}


	public static function nextUpdateEmailTime($user) {
		$now = time();
		$day1 = (60 * 60 * 24 * 1);
		$emailInterval = static::tData($user, 'updateInterval.0');
		if ($emailInterval === null) {
			$emailInterval = 75;
		}
		if ($emailInterval >= 90) $next = $now + $day1; //daily
		else if ($emailInterval >= 60) $next = $now + (7 * $day1); //weekly
		else if ($emailInterval >= 20) $next = $now + (30 * $day1); //monthly
		else if ($emailInterval >= 0) $next = -1;
		//don't send
		return $next;
	}

	public static function nextEmailTime($user) {
		$now = time();
		$day1 = (60 * 60 * 24 * 1);
		$emailInterval = static::tData($user, 'emailInterval.0');
		if ($emailInterval === null) $emailInterval = 75;
		if ($emailInterval >= 90) $next = $now + (60 * 60);
		else if ($emailInterval >= 65) $next = $now + $day1; //daily
		else if ($emailInterval >= 40) $next = $now + (7 * $day1); //weekly
		else if ($emailInterval >= 15) $next = $now + (30 * $day1); //monthly
		else if ($emailInterval >= 0) $next = -1;
		//don't send
		return $next;
	}
}
