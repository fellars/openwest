<?php

namespace bc\traits\account;


use tip\models\Users;
use tip\screens\elements\Raw;
use tip\screens\elements\TableBlock;
use tip\screens\helpers\base\HtmlTags;
use bc\traits\account\base\BCTrait;
use tip\core\Util;
use tip\screens\elements\FormField;
use tip\screens\elements\Table;
use tip\screens\elements\Form;
use tip\models\Accounts;

class CorporateTrait extends BCTrait{

	public function __construct(array $config = array()) {
		parent::__construct($config);
	}

	protected function _init() {
		parent::_init();
		$this->config('types', 'corporate');
	}

	protected $_schema = array(
		'main'=>array(
			'fields'=>array(
				'companyName'=>array('required'=>true),
				'companyDomain'=>array(),
				'offices'=>array(
					'type'=>'list',
					'newItem'=>array(
						'type'=>'address',
						'addressTypes'=>'headquarter,remote,other',
						'typeConfig' => array('defaultValue'=>'headquarter')
					)
				)
			)
		),
		'details'=>array(
			'fields'=>array(
				'locations' => array(
					'type' => 'select',
					'multiple' => true,
					'optionsConfig' => array('value' => '$key', 'label' => 'name'),

				),
				'markets' => array(
					'type' => 'select',
					'multiple' => true,
					'optionsConfig' => array('value' => '$key', 'label' => 'name'),


				),
				'companySize' => array(
					'type' => 'slider',
					'size' => 8,
					'height' => 10,
					'requireSelection' => true,
					'allowCancel' => true,
					'range' => false,
					'label' => 'Company Size<br/><small>(num employees)</small>',
					'labels' => '1,10,25,100,1000+'
				),
			)
		),
		'tech'=>array(
			'fields'=>array(
				'technologyStack'=>array(
					'type'=>'text',
					'placeholder'=>'Technology stack',
					'help'=>'comma separated list of your technology stack'
				),
				'technologyDescription'=>array(
					'type'=>'textArea',
					'help'=>'Describe your technology',
					'label'=>'Description'
				)
			)
		),
		'media'=>array(
			'fields'=>array(
				'media'=>array(
					'type'=>'fullTable',
					'addRowAction'=>'media:table:addRow',
				)
			)
		),
		'match'=>array(
			'fields'=>array(
				'company'=>array('type'=>'hidden'),
//				'crunchbase'=>array(
//					'type'=>'text',
//					'label'=>'Your Crunchbase Shortcode',
//					'help'=>'What appears here http://crunchbase.com/<shortcode here>'
//				),
//				'angel'=>array(
//					'type'=>'text',
//					'label'=>'Your AngelList Shortcode',
//					'help'=>'What appears here http://crunchbase.com/<shortcode here>'
//				),
//				'twitter'=>array(
//					'type'=>'text',
//					'label'=>'Your Twitter Handle',
//					'help'=>'What appears here http://twitter.com/<handle here>'
//				),
			)
		),
		'team'=>array(
			'fields'=>array(
				'team'=>array(
					'type'=>'fullTable',
					'addRowAction'=>'team:table:addRow',
				)
			)
		),
		'defaults'=>array(
			'fields'=>array(
				'defaultContactEmail'=>array(
					'type'=>'email',
					'required'=>true,
				),
				'defaultBenefits'=>array(
					'type'=>'textArea',
					'wysiwyg'=>true,
					'size'=>12,
					'rows'=>400,
					'required'=>false,
					'label'=>'Default Benefits'

				),
				'defaultOverview'=>array(
					'type'=>'textArea',
					'wysiwyg'=>true,
					'size'=>12,
					'rows'=>400,
					'required'=>false,
					'label'=>'Company Overview'

				)
			)
		)



	);

	public function company($db=null){
		$company = $this->entity()->entityVar('company');
		if($company)return $company;
		$id = $this->data('company');
		if($id && $db){
			$company = \bc\models\Companies::byIdRemote($db,$id);
			$this->entity()->entityVar('company',$company);
		}
		return $company;
	}

	protected function _prePrepareCompanyName($field){
		$field['value'] = $this->entityName();
		return $field;
	}
	protected function _preProcessCompanyName($field){
		$name = $field->value();
		$this->entity()->name = $name;
		return null;
	}
	protected function _prePrepareLocations($field) {
		$field['options'] = static::masterMap('locations');
		return $field;
	}

	protected function _prePrepareMarkets($field) {
		$field['options'] = static::masterMap('markets');
		return $field;
	}

	protected function _prePrepareCompany($field){
		if(!Util::nvlA($field,'value')){
			$field['type']='raw';
			$field['label']=false;
			$field['html']= "<h2>This section is being generated and will be available for review within 48 hrs.<br/><br/>We will notify you via email when ready.</h2>";
		}
		return $field;
	}
	protected function _prePrepareCrunchbase($field){
		if(!$this->data('company')){
			$field['type']='hidden';
		}
		return $field;
	}

	protected function _prePrepareAngel($field){
		if(!$this->data('company')){
			$field['type']='hidden';
		}
		return $field;
	}

	protected function _prePrepareTwitter($field){
		if(!$this->data('company')){
			$field['type']='hidden';
		}
		return $field;
	}
	protected function _prePrepareFacebook($field){
		if(!$this->data('company')){
			$field['type']='hidden';
		}
		return $field;
	}
	protected function _prePrepareGooglePlus($field){
		if(!$this->data('company')){
			$field['type']='hidden';
		}
		return $field;
	}


	protected function _postPrepareTeam($field,$action,$step,$form,$fieldSet){
		//set up team

		$vals = Util::toArray($this->data('team'));

		$table = Table::create('teamTable', $field);
		$table->data('control', 'team');
		$table->data('searchBox',false);
		$table->data('showPerPage',false);
		$table->data('showInfo',false);
		$table->data('pageSize',5);
		$table->data('tableHeader',$addLink = HtmlTags::link(HtmlTags::icon('add') . 'New', '#addRow', array('class' => 'btn', 'escape' => false)));
//		$table->data('tableFooter',$addLink );
		$table->addHeaders(array('_id', 'name' => array('width' => '10%', 'render' => true), 'icon' => array('width' => '10%', 'render' => true),'title' => array('width' => '20%', 'render' => true),'bio' => array('width' => '20%', 'render' => true),'social' => array('width' => '30%', 'render' => true), 'actions' => array('width' => '10%')));
		$formsList = $this->teamTableRowAdd($table,$vals);

		$field->data('forms', implode(',', $formsList));
		return $field;

	}
	public function teamTableRowAdd($table, $rows) {
		$formsList = array();


		$table->addRows($rows, array(
			'_id' => function ($val, $key, $row, $rowKey,$index)  {
				return "row_$index";
			},
			'name' => function ($val, $key, $row, $rowKey,$index) use ($table,  &$formsList) {
				$form = Form::create("{$key}_$index", $table, array('formLayout' => 'inline', 'fieldSetSize'=>12,'submitButton' => false));
				$formsList[] = $form->name();
				FormField::field('text', $key, $form, array('size' => 12, 'label' => false, 'value' => $val, ));
				return $form->renderQuick();

			},
			'title' => function ($val, $key, $row, $rowKey,$index) use ($table,  &$formsList) {
				$form = Form::create("{$key}_$index", $table, array('formLayout' => 'inline', 'fieldSetSize'=>12,'submitButton' => false));
				$formsList[] = $form->name();
				FormField::field('text', $key, $form, array('size' => 12, 'label' => false, 'value' => $val, ));
				return $form->renderQuick();

			},
			'bio' => function ($val, $key, $row, $rowKey,$index) use ($table,  &$formsList) {
				$form = Form::create("{$key}_$index", $table, array('formLayout' => 'inline', 'fieldSetSize'=>12,'submitButton' => false));
				$formsList[] = $form->name();
				FormField::field('textArea', $key, $form, array('size' => 12, 'label' => false, 'value' => $val, ));
				return $form->renderQuick();

			},
			'icon' => function ($val, $key, $row, $rowKey,$index) use ($table,  &$formsList) {
				$form = Form::create("{$key}_$index", $table, array('formLayout' => 'inline', 'fieldSetSize'=>12,'submitButton' => false));
				$formsList[] = $form->name();
				FormField::field('media', $key, $form, array('size' => 12, 'label' => false, 'value' => $val, ));
				return $form->renderQuick();

			},
			'social' => function ($val, $key, $row, $rowKey,$index) use ($table,  &$formsList) {
				$form = Form::create("{$key}_$index", $table, array('formLayout' => 'inline', 'fieldSetSize'=>12,'submitButton' => false));
				$formsList[] = $form->name();
				FormField::field('list', $key, $form, array(
					'size' => 12, 'label' => false, 'value' => $val,
					'newItemLabel'=>'New Social Connection',
					'editItemLabel'=>'Edit Social Connection',
					'newItem'=>array(
						'type'=>'object',
						'label'=>"&nbsp;",
						'fields'=>array(
							'source'=>array(
								'type'=>'select',
								'options'=>'twitter,facebook,google+,github,linkedIn,dribble',
								'allowCustom'=>true,
								'required'=>true,
								'label'=>'Source',
							),
							'val'=>array(
								'label'=>'Username/Handle',
								'required'=>true
							)
						)
					)
				));
				return $form->renderQuick();

			},
			'actions' => function ($val, $key, $row, $rowKey,$index) use ($table) {
				return HtmlTags::link(HtmlTags::icon('remove') . 'Remove', '#removeRow', array('data-id' => "row_$index", "data-forms" => "name_$index,icon_$index,title_$index,bio_$index,social_$index", 'class' => 'btn', 'escape' => false));
			}
		));
		return $formsList;
	}

	protected function _preProcessTeam($field) {
		$team = Util::toArray($field->value());
		$names = Util::nvlA2A($team,'_forms');
		$value = array();
		foreach($names as $name){
			list($f,$i) = explode("_",$name);
			if($f == "name"){
				$name = Util::nvlA($team,"name_$i.default.name");
				if($name){
					$icon = Util::nvlA($team,"icon_$i.default.icon");
					$title = Util::nvlA($team,"title_$i.default.title");
					$social = Util::nvlA($team,"social_$i.default.social");
					$bio = Util::nvlA($team,"bio_$i.default.bio");

					$value[] = compact('name','icon','title','bio','social');
				}
			}
		}
		$field->value($value);
		return $field;
	}

	protected function _postPrepareMedia($field,$action,$step,$form,$fieldSet){
		//set up team

		$vals = Util::toArray($this->data('media'));

		$table = Table::create('mediaTable', $field);
		$table->data('control', 'media');
		$table->data('searchBox',false);
		$table->data('showPerPage',false);
		$table->data('showInfo',false);
		$table->data('pageSize',5);
		$table->data('tableHeader',$addLink = HtmlTags::buttonGroup(array(
			HtmlTags::link(HtmlTags::icon('add') . 'New Image', '#addRow', array('data-mode'=>'image','class' => 'btn', 'escape' => false)),
			HtmlTags::link(HtmlTags::icon('add') . 'New Video', '#addRow', array('data-mode'=>'video','class' => 'btn', 'escape' => false)),
		)));
//		$table->data('tableFooter',$addLink );
		$table->addHeaders(array('_id', 'type' => array('width' => '20%', 'render' => true), 'icon' => array('width' => '20%', 'render' => true),'description' => array('width' => '50%', 'render' => true), 'actions' => array('width' => '10%')));
		$formsList = $this->mediaTableRowAdd($table,$vals);

		$field->data('forms', implode(',', $formsList));
		return $field;

	}
	public function mediaTableRowAdd($table, $rows,$mode=false) {
		$formsList = array();


		$table->addRows($rows, array(
			'_id' => function ($val, $key, $row, $rowKey,$index)  {
				return "row_$index";
			},
			'type' => function ($val, $key, $row, $rowKey,$index) use ($table,  &$formsList) {
				$form = Form::create("{$key}_$index", $table, array('formLayout' => 'inline', 'submitButton' => false));
				$formsList[] = $form->name();
				FormField::field('text', $key, $form, array('size' => 12, 'label' => false, 'value' => $val, ));
				return $form->renderQuick();

			},
			'description' => function ($val, $key, $row, $rowKey,$index) use ($table,  &$formsList) {
				$form = Form::create("{$key}_$index", $table, array('formLayout' => 'inline', 'submitButton' => false));
				$formsList[] = $form->name();
				FormField::field('text', $key, $form, array('size' => 12, 'label' => false, 'value' => $val, ));
				return $form->renderQuick();

			},
			'icon' => function ($val, $key, $row, $rowKey,$index) use ($table,  &$formsList) {
				$form = Form::create("{$key}_$index", $table, array('formLayout' => 'inline', 'submitButton' => false));
				$formsList[] = $form->name();
				FormField::field('media', $key, $form, array('size' => 12, 'label' => false, 'value' => $val, ));
				return $form->renderQuick();

			},
			'actions' => function ($val, $key, $row, $rowKey,$index) use ($table) {
				return HtmlTags::link(HtmlTags::icon('remove') . 'Remove', '#removeRow', array('data-id' => "row_$index", "data-forms" => "type_$index,icon_$index,description_$index", 'class' => 'btn', 'escape' => false));
			}
		));
		return $formsList;
	}

	protected function _preProcessMedia($field) {
		$media = Util::toArray($field->value());
		$names = Util::nvlA2A($media,'_forms');
		$value = array();
		foreach($names as $name){
			list($f,$i) = explode("_",$name);
			if($f == "type"){
				$type = Util::nvlA($media,"type_$i.default.type");
				if($type){
					$icon = Util::nvlA($media,"icon_$i.default.icon");
					$description = Util::nvlA($media,"description_$i.default.description");
					$value[] = compact('type','icon','description');
				}
			}
		}
		$field->value($value);
		return $field;
	}

	protected function _prePrepareDefaultContactEmail($field){
		$user = Users::current();
		if(!Util::nvlA($field,'value')){
			$default = $user->tData('/email');
			$field['value'] = $default;
		}

		return $field;
	}
}
