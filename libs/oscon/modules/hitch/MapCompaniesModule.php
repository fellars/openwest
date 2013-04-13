<?php
namespace bc\modules\hitch;

use bc\models\Opportunities;
use \tip\controls\Module;
use tip\core\Util;
use tip\screens\elements\Alert;
use tip\screens\elements\Raw;
use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\helpers\base\HtmlTags;
use tip\screens\elements\Table;
use bc\modules\hitch\base\BaseHitchModule;
use tip\screens\elements\Block;
use tip\screens\elements\Menu;
use bc\models\Companies;

class MapCompaniesModule extends BaseHitchModule
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
		$this->screenAction('maps:menu','selectMapMenu');

        $options += array('render'=>true);
        $grid = $screen->blockGrid();
		$block = Block::create('top',$grid);
		$block->box(false);
		$block->border(false);
		$menu = Menu::create('menu',$block);
		$menu->data('minWidth',100);
		$block->data('tabbable',true);
		$block->data('loadUrl','maps:menu');
		$block->menuLocation("left");
		$block->data('alwaysReload',true);

		$menu->data("stacked",true);
		$menu->navType('pills');

		$div = 1;
		$menu->createNav('blank', " ",array('icon'=>''));
		$menu->createNav('new', "New Signups",array('icon'=>''));
		$menu->createNav('core', "Go Live",array('icon'=>''));
		$menu->createNav('indeed', "",array('icon'=>''));
		$menu->createNav('crunchbase', "",array('icon'=>''));
		$menu->createNav('github', "",array('icon'=>''));
		$menu->createNav('glassdoor', "",array('icon'=>''));
		$menu->createNav('angelList', "",array('icon'=>''));
		$menu->createNav('blogs', "",array('icon'=>''));
		$menu->createNav('twitter', "",array('icon'=>''));
		$menu->createNav('facebook', "",array('icon'=>''));
		$menu->createNav('googlePlus', "",array('icon'=>''));
		$menu->createNav('linkedIn', "",array('icon'=>''));
		$menu->createNav('quora', "",array('icon'=>''));
		$menu->createNav('media', "",array('icon'=>''));
		$menu->createNav('newsFeed', "",array('icon'=>''));
		$menu->createNav('pinterest', "",array('icon'=>''));
		$menu->createNav('stackoverflow', "",array('icon'=>''));

		$menu->setActive('blank','name');
		$this->selectMapMenu($grid->screen(),'blank',false);

//        $this->_mapCompany($block);
        return $options['render'] ? $block->render() : $screen;
    }

	public function selectMapMenu($screen, $menu, $render=true){
		$grid = $screen->blockGrid();
		$topBlock = $screen->element('blockGrid.top');
		$block = Block::create($menu,$topBlock);
		$block->box(false);
		$block->border(true);
		$block->data('tabbable',true);
		$func = "_map".Util::inflect($menu,'c');
		if(method_exists($this,$func)){
			$this->$func($block);
		}else{
			Raw::create('error',$block,'No Mapping Function found for: ' . $menu);
		}
		return $render ? $block->render() : $block;
	}

	protected function _mapSubMenu($block,$options=true){
		$subMenu = Menu::create('menu',$block);
		$block->data('loadUrl','maps:submenu/'.$block->name());
		$block->data('alwaysReload',true);
		$this->screenAction('maps:submenu','selectMapSubMenu');
		if($options == true){
			$subMenu->createNav('new', "",array('icon'=>''));
			$subMenu->createNav('notFound', "",array('icon'=>''));
		}
		return $this->selectMapSubMenu($block->screen(),$block->name(),$subMenu->getActive(), false);
	}

	public function selectMapSubMenu($screen,$menu,$subMenu, $render=true){
		$topBlock = $screen->element("blockGrid.top.$menu");
		$block = Block::create($subMenu,$topBlock);
		$block->box(false);
		$block->border(false);
		$func = "_map".Util::inflect($menu,'c');
		if(method_exists($this,$func)){
			$this->$func($block,$subMenu);
		}else{
			Raw::create('error',$block,"No Sub Mapping Function found for:  $menu  - $subMenu");
		}
		return $render ? $block->render() : $block;
	}

	protected function _whichToMap($trait, $menu){
		$toMap = array();
		if($menu === 'new'){
			if($trait == \bc\traits\company\CoreTrait::class_path()){
				$conditions = array(
					'ready'=>array('$exists'=>false),
					'priority'=>true);

				$toMap = $trait::findFirst($conditions);
				if(!$toMap){
					unset($conditions['priority']);
					$toMap = $trait::findFirst($conditions);
				}
			}else if ($trait === false){
				$db = $this->_db();
				$conditions = \bc\traits\account\CorporateTrait::conditions(array(
					'$or'=>array(
						array('company'=>array('$exists'=>false)),
						array('company'=>''),
					)));
				$toMapAccnt = $db->collection('accounts')->get1($conditions);
				if($toMapAccnt){
					$toMap = Companies::newEntity();
					$toMap->tData('/name',Util::nvlA($toMapAccnt,'name'));
					$toMap->tData('/url',Util::nvlA($toMapAccnt,'traits.1.companyDomain'));
					$toMap->tData('/description',Util::nvlA($toMapAccnt,'description'));
					$toMap->tData('/locations',Util::nvlA2A($toMapAccnt,'traits.1.locations'));
					$toMap->tData('/markets',Util::nvlA2A($toMapAccnt,'traits.1.markets'));
					$toMap->tData('/size',Util::nvlA($toMapAccnt,'traits.1.companySize'));
					$toMap->tData('/_corporate_',(string)$toMapAccnt['_id']);
				}
			}else{
				$conditions = \bc\traits\company\CoreTrait::conditions(array('priority'=>true));
				$without = $trait::withoutCondition();
				$conditions = array('$and'=>array(
					$conditions,
					$without
				));
				$toMap = Companies::find('first',compact('conditions'));
				if(!$toMap)$toMap = $trait::findWithout('first');

			}
		}else if ($menu === 'not_found' && $trait){
			$sub = $trait == \bc\traits\company\CoreTrait::class_path() ?
					array('ready'=>false) :
					array(
						'$or'=>array(
							array('found'=>array('$exists'=>false)),
							array('found'=>false)
						),
					);
			$conditions = array(
					'$and'=>array(
							$sub,
							array(
								'$or'=>array(
									array('nextCheck'=>array('$exists'=>false)),
									array('nextCheck'=>array('$lte'=>(time())))
								),
							),
							array(
								'nextCheck'=>array('$ne'=>-1),
							)
					)

							);
			$toMap = $trait::findFirst($conditions);

		}
		//if a core trait, then we want one with atleast 1 other valid trait
		if($toMap && $trait == \bc\traits\company\CoreTrait::class_path()){
			//make sure atleast 1 other trait has found = true
			$traits = $toMap->loadedTraits();
			$ok = count($traits) > 6;
//			foreach($traits as $t){
//				if($t->data('found') === true){
//					$ok = true;
//					break;
//				}
//			}
			if(!$ok){
				$toMap->tData('/ready', false);
				$priority = $toMap->tData('/priority');
				$checkIn = (60 * 60) * ($priority ? 1 : 24 );//check in a day (or 1 hour if priority)
				$toMap->tData('/nextCheck', time() + $checkIn);
				$toMap->save();
				set_time_limit(100);
				$toMap = $this->_whichToMap($trait,$menu);
			}
		}

		return $toMap;

	}

	protected function _buildForm($menu,$block,$trait,$subMenu,$func, $lookupUrl, $options=array()){
		if(strpos($menu,'_map') === 0){
			$menu = Util::inflect(str_replace("_map","",$menu),'u');
		}
		if(!$subMenu)return $this->_mapSubMenu($block);
		$options += array('nextCheck'=>180,'manualEntry'=>true);
		$toMap = $this->_whichToMap($trait,$subMenu);
		if( $toMap ){
			$company = $toMap->name();
			$isPriority = $toMap->tData('/priority');
			if($isPriority){
				$options['nextCheck'] = 7;
			}
			$form = Form::create($formName="{$menu}Form",$block, array('submitButton'=>false));
			FormField::hidden('id',$form,$toMap->id());
			FormField::hidden('menu',$form,$menu);
			FormField::hidden('subMenu',$form,$subMenu);
			FormField::hidden('trait',$form,$trait);
			$_label = $company;
			if($toMap->tData('/priority'))$_label .= '<br/>**PRIORITY**';
			$_label = HtmlTags::tag('span',$_label,array('style'=>'color:red'));
			FormField::raw('topSubmit',$form,$_label,HtmlTags::submitFormButton('Next',$formName));
			$js = " window.prompt ('Copy to clipboard: Ctrl+C, Enter', '$company'); return false;";
			FormField::raw('addToClipboard',$form,'',HtmlTags::link("Add To Clipboard","#",array('onclick'=>$js)));
			if($lookupUrl)FormField::raw('searchYourself',$form,'',HtmlTags::link("Try to Find Yourself",is_callable($lookupUrl) ? $lookupUrl($company) : $lookupUrl.$company,array('target'=>'_blank')));

			FormField::raw('name',$form,'',HtmlTags::link($company ?: "---","/bc/hitch/companies/".$toMap->id()));
			FormField::raw('description',$form,'',$toMap->tData('/description'));
			FormField::raw('url',$form,'',HtmlTags::link($url = $toMap->tData('/url'),$url,array('target'=>'_blank')));
			$table = Table::create('results');
			$add = $func($company,$table, $this, $toMap,$form);
			if($add !== false){
				FormField::field('table','matchedCompany',$form,Util::toArray($add) + array(
				   'label' => 'Select Matching Company',
				   'table'=>$table,
					'limit'=>1,

				));
			}
			if($options['nextCheck'])FormField::radio('nextCheck',$form,'',$options['nextCheck'],'7::Next Week,30::Next Month,90::3 Months,180::6 Months,365::1 Year,-1::Never');
			if($options['manualEntry'])FormField::text('manualEntry',$form);
			FormField::raw('bottomSubmit',$form,$_label,HtmlTags::submitFormButton('Next',$formName));
		}else{
			$block->removeAllElements();
			Raw::create('congrats',$block,HtmlTags::tag('h3','Congrats! No Companies to Map'));
		}
		return $block;
	}


	protected function _formProcess($form,$data,$func, $options=array()){
		$screen = $form->screen();
		$options += array('checkMatch'=>true,'setFound'=>true);
		$match = true;
		if($options['checkMatch']){
			$field = $form->field('matchedCompany');
			if($field){
				$single = Util::nvl($field->data('limit'),1) === 1;
				$matchKey = $single ?  "matchedCompany.0" : "matchedCompany";
				$match = Util::nvlA($data,$matchKey);
			}else{
				$match = false;
			}
		}
		$trait = $data['trait'];
		$manual = Util::nvlA($data,'manualEntry');
		if(!$match && $manual){
			$match = $manual;
		}
		$company = false;
		$id = $data['id'];
		$c = Companies::byId($id);
		if($match){
			$company = $func($match,$this, $c);
		}
		$setFound = $options['setFound'];
		if($company){
			if($setFound)$company['found'] = true;
		}else{
			$company = array();
			if($setFound)$company['found']=false;
			$nextCheck = Util::nvlA($data,'nextCheck');
			$company['nextCheck'] = $nextCheck == -1 ? -1 : strtotime("+ $nextCheck Days");
		}
		if($company && $c){
			$coTrait = new $trait();
			if(!$c->hasTrait($coTrait))$c->addTrait($coTrait);
			$trait::tData($c,$company,null,true);

		}
		$menu = $data['menu'];
		$subMenu = $data['subMenu'];
		return $this->selectMapSubMenu($screen,$menu,$subMenu);

	}

	protected function _mapBlank($block){
		Raw::create('blank',$block,HtmlTags::tag('h3',"Select an Option from Menu"));
		return $block;
	}
	protected function _mapCore($block, $subMenu=false){
		return $this->_buildForm(
			__FUNCTION__,
			$block,
			\bc\traits\company\CoreTrait::class_path(),
			$subMenu,
			function($company,$table, $me, $toMap, $form){
				$_map = function($name,$list,$limit=1) use($toMap,$form){
					$result = array();
					$default = "";
					foreach($list as $i){
						list($k,$i) = explode('/',$i);
						if($toMap->hasTrait($k)){
							$val = $toMap->tData($k,$i);
							if($val){
								if(!$default)$default = "$k/$i";
								list($bc,$source) = explode('::',$k);
								if($source == 'core')$source = 'existing';
								$source = Util::inflect($source,'h');
								$result[] = array('id'=>"$k/$i",'source'=>$source,'val'=>$val);

							}
						}
					}
					$table = Table::create('results');
					$table->addHeaders('id,source,val');
					$table->addRows($result);
					$tField = FormField::field('table',$name,$form,array(
					   'table'=>$table,
					   'limit'=>$limit,
					   'value'=>$default


					));
					return $result;
				};
				$descOptions = array('bc::core/description','bc::crunchbase/overview','bc::angel_list/product_desc');
				$urlOptions = array('bc::core/url','bc::crunchbase/homepage_url','bc::angel_list/company_url');
				$zipOptions = array('bc::core/zipCode','bc::crunchbase/offices.0.zip_code');

				if($users = \bc\traits\company\TwitterFeedTrait::ifHasData($toMap,'users')){
					foreach($users as $uk => $u){
						if($desc = Util::nvlA($u,'description'))$descOptions[] = "bc::twitter_feed/users.$uk.description";
						if($desc = Util::nvlA($u,'url'))$urlOptions[] = "bc::twitter_feed/users.$uk.url";
						if($desc = Util::nvlA($u,'location'))$zipOptions[] = "bc::twitter_feed/users.$uk.location";
					}
				}
				$ready = true;
				$ready = $_map('description',$descOptions) && $ready;
				$ready = $_map('url',$urlOptions) && $ready;
				$_map('zipCode',$zipOptions);
				$isPriority = $toMap->tData('/priority');
				$locations = \bc\traits\company\CoreTrait::tData2A($toMap,'locations');
				$markets = \bc\traits\company\CoreTrait::tData2A($toMap,'markets');
				$size = \bc\traits\company\CoreTrait::tData($toMap,'size');
				$maps = $me->setting("mappings");
				$locOptions = Util::nvlA2A($maps,'locations');
				$marketOptions = Util::nvlA2A($maps,'markets');
				$locHelp = $marketHelp = $sizeHelp = array();
				$sizeHelp[] = "Existing: " . Util::nvlA($size,"0",$size);
				if(\bc\traits\company\AngelListTrait::hasTrait($toMap)){
					$data = \bc\traits\company\AngelListTrait::tData($toMap);
					$cat = 'Angel';
					$companyRanker = new \bc\hitch\angel\AngelCompanyRanker(array('app' => $me, 'mappings' => $maps));
					$unMatchedLoc = $unMatchedMarket = $intoLoc = $intoMarket = array();
					$locations = array_unique(array_merge($locations,$companyRanker->sumLocations($data,$unMatchedLoc,$toMap)));
					$locHelp[] = "$cat: " . implode(', ', Util::copyInto($intoLoc,$unMatchedLoc,'display_name', false));
					$markets = array_unique(array_merge($markets,$companyRanker->sumMarkets($data,$unMatchedMarket,$toMap)));
					$marketHelp[] = "$cat: " . implode(', ', Util::copyInto($intoMarket,$unMatchedMarket,'display_name', false));
					$sizeHelp[] = "$cat: " . \bc\hitch\CompanyRanker::convertToNumEmployees($companyRanker->sumCompanySize($data,$size,$toMap));
				}
				if(\bc\traits\company\CrunchbaseTrait::hasTrait($toMap)){
					$data = \bc\traits\company\CrunchbaseTrait::tData($toMap);
					$cat = 'CB';
					$companyRanker = new \bc\hitch\crunchbase\CrunchbaseCompanyRanker(array('app' => $me, 'mappings' => $maps));
					$unMatchedLoc = $unMatchedMarket = $intoLoc = $intoMarket = array();
					$locations = array_unique(array_merge($locations,$companyRanker->sumLocations($data,$unMatchedLoc,$toMap)));
					$locHelp[] = "$cat: " . implode(', ', Util::copyInto($intoLoc,$unMatchedLoc,'display_name', false));
					$markets = array_unique(array_merge($markets,$companyRanker->sumMarkets($data,$unMatchedMarket,$toMap)));
					$marketHelp[] = "$cat: " . implode(', ', Util::copyInto($intoMarket,$unMatchedMarket,'display_name', false));
					$sizeHelp[] = "$cat: " . \bc\hitch\CompanyRanker::convertToNumEmployees($companyRanker->sumCompanySize($data,$size,$toMap));
				}
				$ready = $locations && $ready;
				$ready = $markets && $ready;
				$ready = $ready || $isPriority;

				FormField::select('locations',$form,'',$locations,$locOptions,implode('; ',$locHelp),true,false,array('optionsConfig'=>array('label'=>'name','value'=>'$key')));
				FormField::select('markets',$form,'',$markets,$marketOptions,implode('; ',$marketHelp),true,false,array('optionsConfig'=>array('label'=>'name','value'=>'$key')));
				FormField::slider('size',$form,'Size (num employees)',Util::toArray($size),implode('; ',$sizeHelp),false,array('range'=>false,'labels'=>'1,10,25,100,1000+'));
				FormField::radio('ready',$form,'Ready For Prime Time?',$ready ? 'yes':'no','yes,no');
				$traits = $toMap->loadedTraits();
				$traitsList = "";
				foreach($traits as $trait){
					if($trait->sName() !== "bc::core"){
						list($lib,$tName) = explode('::',$trait->sName());
						$traitsList .= Util::inflect($tName,'h') . " = " . ($trait->data('found') === true ? "yes" : ($trait->data('found') === false ? "no" : "-")) . "<br/>";
					}
				}
				FormField::raw('traits',$form,'',$traitsList);


				return false;
			},
			"",
			array('manualEntry'=>false,'nextCheck'=>7)
		);

	}
	public function form_coreForm($form,$data){
		$ready = Util::isTruthy(Util::nvlA($data,'ready'),true);
		$locations = Util::nvlA2A($data,'locations');
		$markets = Util::nvlA2A($data,'markets');
		$description = Util::nvlA($data,'description.0');
		$url = Util::nvlA($data,'url.0');
		if($ready && ( !$locations || !$markets || !$description || !$url)){
			Alert::create('error',$form,"Can't go live without location,market,description,url");
			return $form->render();
		}
		return $this->_formProcess($form,$data,function($match,$me,$c) use($data,$ready,$locations,$markets,$description,$url){
			$company = array();

			if(!$ready){
				$nextCheck = Util::nvlA($data,'nextCheck');
				$company['nextCheck'] = $nextCheck == -1 ? -1 : strtotime("+ $nextCheck Days");
			}
			$size = Util::nvlA($data,'size');

			if($description)$description = $c->tData($description);
			if($url)$url = $c->tData($url);
			$zipCode = Util::nvlA($data,'zipCode.0');
			if($zipCode)$zipCode = $c->tData($zipCode);


			$company += compact('ready','locations','markets','size','description','url','zipCode');
			//make all their opportunities ready
			\bc\models\Opportunities::update(array('traits.0.ready'=>$ready),\bc\traits\opportunity\CoreTrait::conditions(array('company'=>$c->id())));
			return $company;
		},array('checkMatch'=>false,'setFound'=>false));
	}


	protected function _mapNew($block, $subMenu=false){
		$menu = $subMenu = 'new';//only new here

		$toMap = $this->_whichToMap(false,$subMenu);
		if( $toMap ){
			$company = $toMap->name();
			$form = Form::create($formName="{$menu}Form",$block, array('submitButton'=>false));
			FormField::hidden('id',$form,$toMap->id());
			FormField::hidden('_corporate_',$form,$toMap->tData('/_corporate_'));
			FormField::hidden('locations',$form,$toMap->tData('/locations'));
			FormField::hidden('markets',$form,$toMap->tData('/markets'));
			FormField::hidden('size',$form,$toMap->tData('/size'));
			FormField::hidden('menu',$form,$menu);
			FormField::hidden('subMenu',$form,$subMenu);
			FormField::hidden('trait',$form,false);
			FormField::raw('topSubmit',$form,false,HtmlTags::submitFormButton('Create',$formName));
			$js = " window.prompt ('Copy to clipboard: Ctrl+C, Enter', '$company'); return false;";
			FormField::raw('addToClipboard',$form,'',HtmlTags::link("Add To Clipboard","#",array('onclick'=>$js)));
//			if($lookupUrl)FormField::raw('searchYourself',$form,'',HtmlTags::link("Try to Find Yourself",is_callable($lookupUrl) ? $lookupUrl($company) : $lookupUrl.$company,array('target'=>'_blank')));

			FormField::text('name',$form,'',$company);
			FormField::textArea('description',$form,'',$toMap->tData('/description'));
			FormField::text('url',$form,'',$toMap->tData('/url'));

			$me = $this;
			$addTable = function($name,$label,$manualEntry=true) use($toMap,$me,$form,$company){

				$funcName = $name . "Table";
				$table = Table::create($funcName);
				$add = method_exists($me,$funcName) ? $me->$funcName($company,$table,$me,$form) : false;
				if($add !== false){
					FormField::field('table',$name,$form,Util::toArray($add) + array(
					   'label' => "Select Matching $label",
					   'table'=>$table,
						'limit'=>1,

					));
					if($manualEntry){
						$help = is_string($manualEntry) ? "<a href='{$manualEntry}{$company}' target=_blank>Look up here<a/>" : "";
						FormField::text($name.'ManualEntry',$form,$label . " Manual Entry",'','',$help);
					}
				}
			};
			$addTable('cb','Crunchbase',"http://www.crunchbase.com/search?query=");
			$addTable('al', 'AngelList',"https://angel.co/search?q=");
			$addTable('indeed', 'Indeed',"http://api.indeed.com/ads/apisearch?publisher=603706705610129&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2&q=company:");

			FormField::raw('bottomSubmit',$form,false,HtmlTags::submitFormButton('Create',$formName));
		}else{
			$block->removeAllElements();
			Raw::create('congrats',$block,HtmlTags::tag('h3','Congrats! No Companies to Map'));
		}
		return $block;
	}



	public function form_newForm($form,$data){
		$_get = function($name) use($data){
			return Util::nvlA($data,"$name.0",Util::nvlA($data,"{$name}ManualEntry"));;
		};


		if(Util::nvlA($data,'finished')){

			$corpAccount = Util::nvlA($data,'_corporate_');
			$name = $data['name'];
			$url = $data['url'];
			$description = $data['description'];
			$match = Util::nvlA($data,'matches.0');
			$newCompany = $match ? Companies::byId($match) : Companies::newEntity($name,'','active',$description,array('save'=>false));
			$newCompany->tData('/corporateAccount',$corpAccount);
			$newCompany->tData('/priority',true);
			$newCompany->tData('/locations',Util::nvlA2A($data,'locations'));
			$newCompany->tData('/markets',Util::nvlA2A($data,'markets'));
			$newCompany->tData('/size',Util::nvlA($data,'size'));
			$newCompany->type=array('managed');
			if($url)$newCompany->tData('/url',$url);
			$newCompany->save();
			$id = $newCompany->id();
			$db = $this->_db();
			$db->collection('accounts')->update(array('_id'=>Companies::toMongoId($corpAccount)),array('traits.1.company'=>$id));
			//now update any of their opportunities
			Opportunities::update(array('traits.0.company'=>$id),\bc\traits\opportunity\CorporateTrait::conditions(array('corporate'=>$corpAccount)),array('multiple'=>true));
			//now update any that were set to this company but don't have the corporate trait
			$conditions = array('$and'=>array(
				\bc\traits\opportunity\CoreTrait::conditions(array('company'=>$id)),
				\bc\traits\opportunity\CorporateTrait::withoutCondition()
			));
			$opps = Opportunities::find('all',compact('conditions'));
			foreach($opps as $opp){
				$opp->addTrait(new \bc\traits\opportunity\CorporateTrait());
				\bc\traits\opportunity\CorporateTrait::tData($opp,'active',false);
				\bc\traits\opportunity\CorporateTrait::tData($opp,'corporate',$corpAccount);
				$opp->type = array('managed');
				$opp->save();
			}
			return $this->selectMapSubMenu($form->screen(),'new','new');
		}else{
			$cb = $_get('cb');
			$al = $_get('al');
			$indeed = $_get('indeed');
			$matches = array();
			if($cb){
				$existing = \bc\traits\company\CrunchbaseTrait::findFirst(array('permalink'=>$cb));
				if($existing)$matches[$existing->id()] = $existing;
			}
			if($al){
				$existing = \bc\traits\company\AngelListTrait::findFirst(array('id'=>$al));
				if($existing)$matches[$existing->id()] = $existing;
			}
			if($indeed){
				$existing = \bc\traits\company\IndeedFeedTrait::findFirst(array('company'=>$indeed));
				if($existing)$matches[$existing->id()] = $existing;
			}


			if($matches){
				$table = Table::create('matches');
				$table->addHeaders('_id','name','description');
				$table->addRows($matches);
				FormField::field('table','matches',$form,array(
					   'label' => "Select Matching Companies",
					   'table'=>$table,
						'limit'=>1,
						'value'=>$matches[0]->id()
					));
				if(count($matches)>1){
					Alert::create("error",$form,"Looks like we have multiple matches.  Look into these ids: ". implode(',',array_keys($matches)));
				}

			}else{
				Alert::create("success",$form,"No existing matches. Click 'Create' to Save Company.");

			}
			FormField::hidden('finished',$form,true);
			$form->field('cb')->removeFromParent();
			$form->field('al')->removeFromParent();
			$form->field('indeed')->removeFromParent();
			$form->field('cbManualEntry')->removeFromParent();
			$form->field('alManualEntry')->removeFromParent();
			$form->field('indeedManualEntry')->removeFromParent();
			FormField::hidden('cb',$form,$cb);
			FormField::hidden('al',$form,$al);
			FormField::hidden('indeed',$form,$indeed);
			FormField::raw('instructions',$form,false,'FYI - You still need to go through each section (even the ones here) to incorporate but this company will come up as high priority.');

			return $form->render();
		}
	}

	protected function _mapCrunchbase($block, $subMenu=false){

		return $this->_buildForm(
		__FUNCTION__,
		$block,
		\bc\traits\company\CrunchbaseTrait::class_path(),
		$subMenu,
		function($company,$table, $me){
			$me->cbTable($company,$table);
		},
		"http://www.crunchbase.com/search?query="
		);

	}
	public function cbTable($company,$table){
		$cb = $this->loadService('crunchbase');
		$page=1;
		$results = $cb->search($company,compact('page'));
		//            echo "$company<pre>"; print_r( $results ); echo "</pre>";exit;
		$table->addHeaders('id::::permalink,name,office,overview');
		$table->addRows(Util::nvlA($results,'results'),array(
			'overview'=>function($val){return substr($val,0,250); },
				'name' => function($val,$key,$row){
				return HtmlTags::link($val,$row['crunchbase_url'],array('target'=>'_blank'));
			},
			'office' => function($val,$key,$row){
				return Util::nvlA($row,"offices.0.city") . " " . Util::nvlA($row,"offices.0.state_code");
			}
		),function($row){return Util::nvlA($row,'namespace')==='company';});//only return company entities

	}
    public function form_crunchbaseForm($form,$data){
		return $this->_formProcess($form,$data,function($match,$me){
			$cb = $me->loadService('crunchbase');
			$company = $cb->company($match);
			$found = Util::nvlA($company,'name');
			return $found ? $company : false;
	    });
	}

	protected function _mapGithub($block, $subMenu=false){
		static::addLib('tip_github');
		$this->screenAction('github:show',true);
		return $this->_buildForm(
			__FUNCTION__,
			$block,
			\bc\traits\company\GithubFeedTrait::class_path(),
			$subMenu,
			function($company,$table, $me){
				$me->ghTable($company,$table);
			},
			"https://github.com/search?q="
		);

	}

	public function ghTable($company,$table){
		$gh = $this->appConfig('github');
		$github = \tip_github\services\GithubService::byConsumerEndpoint($gh);
		$results = $github->api('user')->find($company);
		$results = Util::nvlA2A($results,'users');
//				echo "$company<pre>"; print_r( $results ); echo "</pre>";exit;
		$table->addHeaders('id,username,name,language,show');
		$table->addRows($results,array(
		'id'=>function($val,$key,$row) {
			return $row['username'];
		},
		'name' => function($val,$key,$row){
			return HtmlTags::link(Util::nvl($val,$row['username']),"https://github.com/".$row['username'],array('target'=>'_blank'));
		},
		'show' => function($val,$key,$row){
			$user = $row['username'];
			return HtmlTags::modalButton('Show',"github:show/$user");
		}

		));

	}

	public function github_show($screen,$username){

		static::addLib('tip_github');
		$gh = $this->appConfig('github');
		$github = \tip_github\services\GithubService::byConsumerEndpoint($gh);
		$show = $github->api('user')->show($username);
		$name = Util::nvlA($show,'name');
		return $this->renderJson(array('fullSize'=>true,'title'=>$name,'text'=>"<pre>".print_r($show,true)."</pre>"));
	}
	public function form_githubForm($form,$data){
		static::addLib('tip_github');
		return $this->_formProcess($form,$data,function($match,$me){
			$gh = $me->appConfig('github');
			$github = \tip_github\services\GithubService::byConsumerEndpoint($gh);
			$company  = $github->api('user')->show($match);
			$name = Util::nvlA($company ,'name');
			return $name ? $company : false;
		  });
	}


	protected function _mapGlassdoor($block, $subMenu=false){
		$search = "http://www.glassdoor.com/GD/Reviews/company-reviews.htm?sc.keyword=";
		return $this->_buildForm(
			__FUNCTION__,
			$block,
			\bc\traits\company\GlassdoorFeedTrait::class_path(),
			$subMenu,
			function($company,$table, $me, $toMap, $form){
				return false;
			},
			$search
		);

	}

	public function form_glassdoorForm($form,$data){
		return $this->_formProcess($form,$data,function($match,$me){
			$remaining = str_replace("http://www.glassdoor.com/Overview/Working-at-","",$match);
			list($slug,$remaining) = explode("-",$remaining)+array("","");
			$remaining = str_replace("EI_IE","",$remaining);
			list($id) = explode(".",$remaining)+array("");
			//todo: get this via regex
			return array('slug'=>$slug,'id'=>$id,'url'=>$match);
		  },array('checkMatch'=>true));

	}
	protected function _mapAngelList($block, $subMenu=false){
		$search = "https://angel.co/search?q=";
		$this->screenAction('angel:show',true);
		return $this->_buildForm(
			__FUNCTION__,
			$block,
			\bc\traits\company\AngelListTrait::class_path(),
			$subMenu,
			function($company,$table, $me, $toMap, $form){
				$me->alTable($company,$table,$toMap,$form);
			},
			$search
		);

	}

	public function alTable($company,$table,$toMap,$form){
		$al = $this->loadService('angellist');
		$results = $al->search($company);
		$table->addHeaders('id,name,pic,show');
		$table->addRows($results,array(
			'name'=>function($val,$key,$row){
				return HtmlTags::link($val,$row['url'],array('target'=>'_blank'));
			},
			'pic'=>function($val){
				return $val ? HtmlTags::image($val) : "";
			},
			'show'=>function($val,$key,$row){
				$user = $row['id'];
				return HtmlTags::modalButton('Show',"angel:show/$user");
			}
		));

	}

	public function angel_show($screen,$id){
		$al = $this->loadService('angellist');
		$show = $al->company($id);
		$name = Util::nvlA($show,'name');
		return $this->renderJson(array('fullSize'=>true,'title'=>$name,'text'=>"<pre>".print_r($show,true)."</pre>"));

	}

	public function form_angel_listForm($form,$data){
		return $this->_formProcess($form,$data,function($match,$me,$toMap){
				$al = $me->loadService('angellist');
				$company = $al->company($match);
				$found = Util::nvlA($company,'id');
				return $found ? $company : false;
		  });

	}

	protected function _mapBlogs($block, $subMenu=false){
		$search = "";
		return $this->_buildForm(
			__FUNCTION__,
			$block,
			\bc\traits\company\BlogFeedTrait::class_path(),
			$subMenu,
			function($company,$table, $me, $toMap, $form){
				return $me->blogTable($company,$table,$toMap);

			},
			$search
		);
	}

	public function blogTable($company,$table,$toMap){
		$table->addHeaders('id,blog');
		$blogs = $existing = Util::toArray( \bc\traits\company\BlogFeedTrait::ifHasData($toMap,'blogs') );
		if($blog = \bc\traits\company\AngelListTrait::ifHasData($toMap,'blog_url')){
			$blogs[] = $blog;
		}
		if($blog = \bc\traits\company\CrunchbaseTrait::ifHasData($toMap,'blog_url')){
			$blogs[] = $blog;
		}
		$blogs = array_unique($blogs);
		$table->addRows($blogs,array(
			'id'=>function($val,$key,$row){
				return $row;
			},
			'blog'=>function($val,$key,$row){
				return HtmlTags::link($row,$row,array('target'=>'_blank'));
			}
		));
		return array('value'=>$existing,'limit'=>false);

	}

	public function form_blogsForm($form,$data){
		//make sure valid blog
		$manual = Util::nvlA2A($data,'manualEntry');
		$match = Util::nvlA2A($data,"matchedCompany");
		$match = array_unique(array_merge(Util::toArray($match),$manual));
		$rss = \_services\services\RssService::create();
		$notValid = array();
		foreach($match as $blog){
			$rss->feedUrl($blog);
			$validFeed = $rss->init();
			if(!$validFeed){
				//try adding /feed to end of it
				$try = $blog . (strrpos($blog,'/') !== (strlen($blog)-1) ? '/' : '') . 'feed/';
				$rss->feedUrl($try);
				$validFeed = $rss->init();
			}
			if(!$validFeed){
				$notValid[] = HtmlTags::link($blog,$blog,array('target'=>'_blank'));
			}
		}
		if($notValid){
			\tip\screens\elements\Alert::create('error',$form,'Following Blogs not valid: ' . implode(', ',$notValid));
			return $form->render();
		}
		$blogs = $match;
		return $this->_formProcess($form,$data,function($match,$me) use($data, $blogs){

			return $blogs ? array('blogs'=>$blogs) : false;
	  });
	}

	protected function _mapTwitter($block, $subMenu=false){
		$search = "";
		return $this->_buildForm(
			__FUNCTION__,
			$block,
			\bc\traits\company\TwitterFeedTrait::class_path(),
			$subMenu,
			function($company,$table, $me, $toMap, $form){
				return $me->twitterTable($company,$table,$toMap);
			},
			$search
		);
	}

	public function twitterTable($company,$table,$toMap){
		$table->addHeaders('id,handle');
		$handles = $existing = Util::toArray( \bc\traits\company\TwitterFeedTrait::ifHasData($toMap,'handles') );
		if($handle = \bc\traits\company\AngelListTrait::ifHasData($toMap,'twitter_url')){
			$handle = explode('/',$handle);
			$handle = array_pop($handle);
			if(!$handle)$handle = array_pop($handle);
			$handles[] = str_replace(array('@','/'),'',$handle);

		}
		if($handle = \bc\traits\company\CrunchbaseTrait::ifHasData($toMap,'twitter_username')){
			$handles[] = str_replace(array('@','/'),'',$handle);
		}
		$handles = array_unique($handles);
		$table->addRows($handles,array(
			'id'=>function($val,$key,$row){
				return $row;
			},
			'handle'=>function($val,$key,$row){
				return HtmlTags::link("@".$row,"http://twitter.com/".$row,array('target'=>'_blank'));
			}
		));
		return array('value'=>$existing,'limit'=>false);

	}
	public function form_twitterForm($form,$data){
		return $this->_formProcess($form,$data,function($match,$me) use($data){
			$manual = Util::nvlA2A($data,'manualEntry');
			$match = array_unique(array_merge(Util::toArray($match),$manual));
			return $match ? array('handles'=>$match) : false;
	  });
	}
	protected function _mapIndeed($block, $subMenu=false){
		$search = "http://api.indeed.com/ads/apisearch?publisher=603706705610129&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2&q=company:";

		return $this->_buildForm(
			__FUNCTION__,
			$block,
			\bc\traits\company\IndeedFeedTrait::class_path(),
			$subMenu,
			function($company,$table, $me, $toMap, $form){
				return $me->indeedTable($company,$table);
			},
			$search
		);
	}
	public function indeedTable($company,$table){
		$service = $this->loadService('indeed');
		$results = $service->search("company:".urlencode($company));
		$table->addHeaders('id,company,location,snippet');
		$table->addRows(Util::nvlA2A($results,'results'),array(
			'id'=>function($val,$key,$row){
				return $row['company'];
			},
			'company'=>function($val,$key,$row){
				return HtmlTags::link($val,$row['url'],array('target'=>'_blank'));
			},
			'location'=>function($val,$key,$row){
				return $row['city'] . " " . $row['state'];
			},

		));

	}
	public function form_indeedForm($form,$data){
		return $this->_formProcess($form,$data,function($match,$me) use($data){
			return $match ? array('company'=>$match) : false;
	  });
	}

	protected function _mapNewsFeed($block, $subMenu=false){
	}

	protected function _mapFacebook($block, $subMenu=false){
	}

	protected function _mapGooglePlus($block, $subMenu=false){
	}
	protected function _mapLinkedIn($block, $subMenu=false){

	}
	protected function _mapQuora($block, $subMenu=false){

	}
	protected function _mapMedia($block, $subMenu=false){
	}
	protected function _mapPinterest($block, $subMenu=false){
	}
	protected function _mapStackOverflow($block, $subMenu=false){
	}
	protected function _mapMeetup($block, $subMenu=false){
	}
	protected function _mapForesquare($block, $subMenu=false){
	}


}


?>