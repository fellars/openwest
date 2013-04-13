<?php
namespace bc\traits\opportunity;

use tip\traits\base\CoreBaseTrait;
use bc\traits\user\TalentTrait;
use tip\core\Util;
use tip\screens\elements\Table;
use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\helpers\base\HtmlTags;
use tip\models\Accounts;

class CoreTrait extends CoreBaseTrait
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

    protected $_schema = array(
	  'main'=>array(
		  'fields'=>array(

			  '_dataSource'=>array(),
			  'company'=>array(),

		  )
	  ),
  	  'overview'=>array(
			'fields'=>array(
				'listingName'=>array('required'=>true),
				'position' => array(
					'type' => 'select',
					'multiple' => true,
					'optionsConfig' => array('value' => '$key', 'label' => 'name'),
					'defaultValue'=>'full_time'


				),
				'roles' => array(
					'type' => 'select',
					'multiple' => true,
					'optionsConfig' => array('value' => '$key', 'label' => 'name'),


				),
				'locations' => array(
					'type' => 'select',
					'multiple' => true,
					'optionsConfig' => array('value' => '$key', 'label' => 'name'),

				),
				'salary' => array(
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
				'equity' => array(
					'type' => 'slider',
					'size' => 8,
					'height' => 10,
					'requireSelection' => true,
					'allowCancel' => true,
					'range' => false,
					'label'=> 'Equity/Options',
					'labels' => 'No Equity/Options,Somewhere in between,Founder\'s Equity',

				),
				'skills' => array(
					'type'=>'fullTable',
					'addRowAction'=>'skills:table:addRow',
					'template'=>'tip-form-raw',
					'subSelector'=>'.skillsTableInsert',
					'html'=>'<div class="row-fluid"><div class="span8 skillsTableInsert"></div></div>'
				),
			)
		),
		'listing'=>array(
			'fields'=>array(
				'description'=>array(
					'type'=>'textArea',
					'wysiwyg'=>true,
					'size'=>12,
					'rows'=>400,
					'required'=>true,
					'label'=>'Job Description'
				),
				'benefits'=>array(
					'type'=>'textArea',
					'wysiwyg'=>true,
					'size'=>12,
					'rows'=>400,
					'required'=>false,
					'label'=>'Benefits/Perks'
				),
				'overview'=>array(
					'type'=>'textArea',
					'wysiwyg'=>true,
					'size'=>12,
					'rows'=>400,
					'required'=>false,
					'label'=>'Company Overview'
				),


			)
		)

    );

	protected function _prePrepareListingName($field){
		$field['value'] = $this->entityName();
		return $field;
	}

	protected function _preProcessListingName($field){
		$val = $field->value();
		if($val)$this->entity()->name = $val;
		return null;
	}

	protected function _prePrepareRoles($field) {

		$field['options'] = TalentTrait::masterMap('roles');
		return $field;
	}

	protected function _prePreparePosition($field) {

		$field['options'] = TalentTrait::masterMap('positions');
		return $field;
	}

	protected function _prePrepareLocations($field) {
		$account = Accounts::current();
		if(!Util::nvlA($field,'value')){
			$default = \bc\traits\account\CorporateTrait::ifHasData($account,'locations');
			$field['value'] = Util::toArray($default);
		}

		$field['options'] = TalentTrait::masterMap('locations');
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
		$table->data('tableHeader',$addLink = HtmlTags::link(HtmlTags::icon('add') . 'New', '#addRow', array('data-opportunity'=>$this->entityId(),'class' => 'btn', 'escape' => false)));
//		$table->data('tableFooter',$addLink );
		$table->addHeaders(array('_id', 'skill' => array('width' => '40%', 'render' => true), 'level' => array('width' => '50%', 'render' => true), 'actions' => array('width' => '10%')));
		$formsList = $this->skillsTableRowAdd($table,$vals);

		$skillsField->data('forms', implode(',', $formsList));
		return $skillsField;

	}
	protected function _skills(){
		$roles = Util::toArray($this->data('roles'));
		$allSkills = TalentTrait::masterMap('skills');
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
		$roles = Util::toArray($field->form()->field('roles')->value());
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


	protected function _prePrepareBenefits($field){
		$account = Accounts::current();
		if(!Util::nvlA($field,'value')){
			$default = \bc\traits\account\CorporateTrait::ifHasData($account,'defaultBenefits');
			$field['value'] = $default;
		}

		return $field;
	}

	protected function _prePrepareOverview($field){
		$account = Accounts::current();
		if(!Util::nvlA($field,'value')){
			$default = \bc\traits\account\CorporateTrait::ifHasData($account,'defaultOverview');
			$field['value'] = $default;
		}

		return $field;
	}

}
