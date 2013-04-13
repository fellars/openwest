<?php
namespace bc\apps;

use tip\controls\App;
use tip\screens\BlockPartyScreen;
use tip\screens\elements\Block;
use tip\screens\elements\Raw;
use tip\screens\elements\Table;
use tip\screens\elements\Menu;
use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\elements\Alert;

use tip\core\Util;
use tip\screens\helpers\base\HtmlTags;

class HitchApp extends App{


    public function __construct(array $config = array()) {
        parent::__construct($config);
    }


    protected function _init() {
        parent::_init();
        $this->action('','talentUsers');
        $this->action('cron:update',true);
		$this->action('cron:test',false);

        $this->config('name','Betacave Admin');
        $this->config('type','business');
        $this->config('category','business');
        $this->config('description','Managing the Betacave Platform');
        $this->configMenu('companies,talentUsers::Talent,mapCompanies::Map Companies,searchJobs::Search Jobs,schedule::Cron Schedules,configure,maps::Configure Maps');

    }

    public function companies($id=""){
        $screen = $this->_screenStart('durc',__FUNCTION__,array(
                        'title'=>'Companies',

                    ));
		if($id)$this->requestData('company',$id);
        return $this->loadModule( new \bc\modules\hitch\CompaniesModule(), $screen);
    }

    public function talentUsers(){
    $block = $this->_screenStart('block',__FUNCTION__,array(
                    'title'=>'Find Users',
                    'subtitle'=>"Users pulled from BC Database"
                ));

        return $this->loadModule(new \bc\modules\hitch\TalentModule(),$block->screen());
    }

    public function schedule(){
        $screen = $this->_screenStart('durc',__FUNCTION__,array(
                        'title'=>'Manage Cron Schedules',
        ));
        return $this->loadModule( new \bc\modules\hitch\CronUpdateModule(), $screen,array('load'=>'schedule'));
    }

    public function cron_update(){
        $screen = $this->_screenStart('block',__FUNCTION__,array(
                        'title'=>'Update Cache',
                    ));
        return $this->loadModule( new \bc\modules\hitch\CronUpdateModule(), $screen, array());

    }

	public function test_cron(){
		$task = \_core\traits\CoreSchedulerTaskTrait::findFirst(array('action'=>'cron:update'));
		$app = new \_core\apps\SchedulerApp();
		return $app->runTask($task);
	}

    public function mapCompanies(){
        $block = $this->_screenStart('block',__FUNCTION__,array(
            'title'=>'Map Companies',
            'subtitle'=>"Map these Companies"
        ));
        return $this->loadModule( new \bc\modules\hitch\MapCompaniesModule(), $block);
    }


    public function searchJobs(){
        $block = $this->_screenStart('block',__FUNCTION__,array(
            'title'=>'Search Jobs',
            'subtitle'=>"Jobs Performed against 3rd Party APIs"
        ));
        return $this->loadModule( new \bc\modules\hitch\SearchJobsModule(), $block,array());
    }

    public function configure(){
        $grid = $this->_screenStart('block',__FUNCTION__,array(
            'title'=>'Configure',
            'subtitle'=>"If you haven't configured endpoints yet, <a href='/_core/settings/services'>then do so now</a>"
        ));

        $form = Form::create('config',$grid);
        $form->submitButtonText('Done');
        $form->data('disableActionBorder',true);

        $settings = $this->appConfig();
        FormField::selectModel($name = 'indeed',$form,'',Util::nvlA($settings,"$name"),'endpoints',"bc_$name::$name");
        FormField::selectModel($name = 'crunchbase',$form,'',Util::nvlA($settings,"$name"),'endpoints',"bc_$name::$name");
        FormField::selectModel($name = 'angellist',$form,'',Util::nvlA($settings,"$name"),'endpoints',"bc_$name::$name");
		FormField::selectModel($name = 'twitter',$form,'',Util::nvlA($settings,"$name"),'endpoints',\_services\traits\endpoint\OauthTrait::clientServiceCondition("_services::$name"));
		FormField::selectModel($name = 'github',$form,'',Util::nvlA($settings,"$name"),'endpoints',\_services\traits\endpoint\OauthTrait::clientServiceCondition("tip_github::$name"));
		FormField::selectModel($name = 'facebook',$form,'',Util::nvlA($settings,"$name"),'endpoints',"_services::$name");
        FormField::selectModel($name = 'bcDatabase',$form,'',Util::nvlA($settings,"$name"),'endpoints',"tip_databases::mongo_db");
        if(Util::nvlA($settings,"bcDatabase")){
            $bcdb = $this->loadService('bcDatabase');
            $options = $bcdb->collection('apps')->get(array('traits.0.app'=>'bc::master'),array('name'=>true));
            FormField::field('select',$name = 'masterAppId',$form,array(
                'value'=>Util::nvlA($settings,"$name"),
                'label'=>'Select the Master App',
                'options'=>$options,
                'optionsConfig'=>array('value'=>'_id','label'=>'name')
                )
            );
        }else{
            FormField::raw('masterAppId',$form,'Please select the Database connection to Client DB First');
        }
        FormField::raw('createCronJobs',$form,'Cron Jobs',HtmlTags::refreshButton('Click to Create Cron Jobs','cronjobs:create'));
        $this->screenAction('cronjobs:create',true);
        $grid->render();
    }



	public function cronjobs_create($screen){
        $form = $screen->element('blockGrid.config');
        $jobs = array(
            'daily_refresh'=>array(
                'action'=>'cron:update',
                'cronExpression'=>'@daily',
                'postData'=>'',
            ),
            'weekly_refresh'=>array(
                'action'=>'cron:update',
                'cronExpression'=>'@weekly',
                'postData'=>'',

            )
        );
        \_core\models\SchedulerTasks::modifyAppJobs($this->app(),$jobs);
        Alert::create('success',$form,'Cron Jobs Created');

        return $form->render();
    }


    public function maps(){
        $this->screenAction('hitch:maps:change','selectMapMenu');
        $this->screenAction('hitch:map:new','newMapping');
        $this->screenAction('hitch:map:edit','editMapping');
        $this->screenAction('hitch:map:delete','deleteMapping');
        $this->screenAction('hitch:maps:push','pushMappings');
        $grid = $this->_screenStart('block',__FUNCTION__,array(
            'title'=>'Configure Mappings',
        ));

        $grid->buttons(array(
            HtmlTags::refreshButton("Push Mappings","hitch:maps:push",array('confirm'=>true,'text'=>'Are you sure you want to push mappings to other DB?'))
        ));

        $block = Block::create('top',$grid);
        $block->box(false);
        $block->border(false);

        $menu = Menu::create('menu',$block);
        $menu->data('minWidth',100);
        $block->data('tabbable',true);
        $block->data('loadUrl','hitch:maps:change');
        $block->menuLocation("left");
        $block->data('alwaysReload',true);

        $menu->data("stacked",true);
        $menu->navType('pills');

        $div = 1;
        $menu->createNav('header', "Maps",array('header'=>true));
        $menu->createNav('positions', "Position Type",array('icon'=>''));
        $menu->createNav('roles', "Roles",array('icon'=>''));
        $menu->createNav('regions', "Regions",array('icon'=>''));
        $menu->createNav('locations', "Locations",array('icon'=>''));
        $menu->createNav('skills', "Skills",array('icon'=>''));
        $menu->createNav('markets', "Markets",array('icon'=>''));
        $menu->createNav('colleges', "Colleges",array('icon'=>''));
		$menu->createNav('zipCodes', "Zip Codes",array('icon'=>''));
        $menu->setActive('locations','name');
        $this->selectMapMenu($grid->screen(),'locations',false);

        return $grid->render();
    }
    public function selectMapMenu($screen,$menu,$render=true){
        $topBlock = $screen->element('blockGrid.top');
        $block = Block::create($menu,$topBlock);
        $block->box(false);
        $block->border(false);

        Raw::create('newButton',$block,HtmlTags::buttonGroup(HtmlTags::modalButton('Create New',"hitch:map:new/$menu",array('closeButton'=>false)),true,array('style'=>'float:right')));
        $table = Table::create("table$menu",$block);
        $this->__addMapsTable($table,$menu);

        return $render ? $block->render() : $block;
    }

    private function __addMapsTable($table,$type){
        $maps = Util::toArray($this->setting("mappings.$type"));
        $table->clear();
        $table->clearHeaders();
        $table->addHeader('name');
        switch($type){
            case 'skills':
                $table->addHeaders('roles,synonyms');
                break;
			case 'locations':
				$table->addHeaders('zipCodes,angelMappings');
				break;
			case 'zip_codes':
				$table->addHeaders('area_codes,primary_city,county,state');
				set_time_limit(250);
				break;
			case 'positions':
				echo "$type<pre>"; print_r( $this->setting("mappings") ); echo "</pre>";exit;
				break;
            default:
                $table->addHeaders('angelMappings,indeedMappings');
        }
        $table->addHeader('actions');
        $table->addRows($maps,array(
            'roles'=>Table::filterList(),
            'angel_mappings'=>Table::filterList(null,null,100),
            'indeed_mappings'=>Table::filterList(null,null,100),
			'zip_codes'=>Table::filterList(null,null,60),
            'actions'=>function($val,$key,$row,$rowKey) use($type){

                return HtmlTags::buttonGroup(array(
                    HtmlTags::modalButton('Edit',"hitch:map:edit/$type/$rowKey")
                    . HtmlTags::refreshButton('Delete',"hitch:map:delete/$type/$rowKey",array('confirm'=>true))
                ));
            }
        ));
    }

    public function deleteMapping($screen,$type,$key){
        $maps = Util::toArray($this->setting("mappings.$type"));
        unset($maps[$key]);
        $this->setting("mappings.$type",$maps);
        $block = $screen->element("block.top.$type");
        $table = $block->element("table$type");
        $this->__addMapsTable($table,$type);
        Alert::create('success',$block,Util::inflect($key,'h').' successfully deleted');
        return $block->render(array('who'=>$type));
    }

    public function newMapping($screen,$type){
        return $this->editMapping($screen,$type,false);
    }
    public function editMapping($screen,$type,$key=0){
        $existing = $key !== false ? $this->setting("mappings.$type.$key") : array();
        $form = Form::create('mapForm',$screen,array('cancelButton'=>true,'submitButtonText'=>$existing ? 'Update': 'Create New'));
        FormField::field('raw','typeOfMapping',$form,array('value'=>Util::inflect($type,'h')));
        FormField::field('hidden','type',$form,array('value'=>$type));
        FormField::field('hidden','oriKey',$form,array('value'=>$key));

        $nameField = FormField::field('text',$name = 'name',$form,array('value'=>Util::nvlA($existing,$name)));
        $shortCodeField = FormField::field('text',$name = 'shortcode',$form,array('value'=>$key,'help'=>'Leave blank for underscore version of name'));
        $angelFields = $crunchbaseFields = $indeedFields = array(FormField::field('text','name'));
        $roles = $this->setting("mappings.roles");

        switch($type){
            case 'roles':
                $angelFields[] = FormField::field('text','tagId');
				$crunchbaseFields = $indeedFields = false;
                break;
			case 'zip_codes':
				$shortCodeField->removeFromParent();
				FormField::field('text',$name='primary_city',$form,array('value'=>Util::nvlA($existing,$name)));
				FormField::field('text',$name='acceptable_cities',$form,array('value'=>Util::nvlA($existing,$name)));
				FormField::field('text',$name='state',$form,array('value'=>Util::nvlA($existing,$name)));
				FormField::field('text',$name='county',$form,array('value'=>Util::nvlA($existing,$name)));
				FormField::field('text',$name='tz',$form,array('value'=>Util::nvlA($existing,$name)));
				FormField::field('text',$name='area_codes',$form,array('value'=>Util::nvlA($existing,$name)));
				FormField::field('text',$name='lat',$form,array('value'=>Util::nvlA($existing,$name)));
				FormField::field('text',$name='long',$form,array('value'=>Util::nvlA($existing,$name)));
				FormField::field('text',$name='estimated_pop',$form,array('value'=>Util::nvlA($existing,$name)));
				$angelFields = $crunchbaseFields = $indeedFields = false;
				break;
            case 'locations':
                $regions = $this->setting("mappings.regions");
                if($regions){
                    FormField::field('select',$name = 'region',$form,array('options'=>$regions,'value'=>Util::nvlA($existing,$name),
                        'multiple'=>false,'closeOnSelect'=>true,
                        'optionsConfig'=>array('label'=>'name','value'=>'$key'),
                    ));
					FormField::textarea($name='zipCodes',$form,'',Util::nvlA($existing,$name));
                }else{
                    FormField::raw('regions',$form,' ','!!!You need to create some Regions first!!!');
                }
                $angelFields[] = FormField::field('text','tagId');
                $angelFields[] = FormField::field('radio','root',null,array('options'=>'yes,no','value'=>'no','inline'=>true));
				$crunchbaseFields = $indeedFields = false;
                break;
            case 'skills':
                //add the role these skills are associated with
                FormField::field('select',$name = 'roles',$form,array('options'=>$roles,'value'=>Util::nvlA($existing,$name),
                    'optionsConfig'=>array('label'=>'name','value'=>'$key'),
                    'multiple'=>true,'closeOnSelect'=>true
                ));
                FormField::field('text',$name = 'synonyms',$form,array('value'=>Util::nvlA($existing,$name)));
                FormField::field('radio',$name = 'ug',$form,array('inline'=>true,'label'=>'User Generated','options'=>'yes,no','value'=>Util::isTruthy(Util::nvlA($existing,$name))?'yes':'no'));
                $crunchbaseFields = $angelFields = $indeedFields = false;
                break;
            case 'markets':
                $angelFields[] = FormField::field('text','tagId');
				FormField::text($name='crunchbaseMappings',$form,'',Util::nvlA($existing,$name));
				$crunchbaseFields = $indeedFields = false;

                break;
            case 'colleges':
                break;
        }


        if($angelFields){
            $angelMap = FormField::field('object','angelMap',null,array('fields'=>$angelFields));
            FormField::field('list',$name = 'angelMappings',$form,array('value'=>Util::nvlA($existing,$name),'newItem'=>$angelMap));
        }
        if($indeedFields){
            $indeedMap = FormField::field('object','indeedMap',null,array('fields'=>$indeedFields));
            FormField::field('list',$name = 'indeedMappings',$form,array('value'=>Util::nvlA($existing,$name),'newItem'=>$indeedMap));
        }
		if($crunchbaseFields){
			  $cbMap = FormField::field('object','crunchbaseMap',null,array('fields'=>$angelFields));
			  FormField::field('list',$name = 'crunchbaseMappings',$form,array('value'=>Util::nvlA($existing,$name),'newItem'=>$cbMap));
		  }

        return $form->render(array('fullSize'=>true,'title'=>($key === false ? "Create " : "Edit ") . Util::inflect($type,'h,s'),'buttons'=>false));
    }

    public function form_mapForm($form,$data){
//        echo "<pre>"; print_r( $data ); echo "</pre>";exit;
        $name = $data['name'];
        $key = $data['shortcode'];
        $oriKey = $data['oriKey'];
        if(!$key)$key = Util::inflect($name,'u');

        $type = $data['type'];

        $allMaps = Util::toArray($this->setting("mappings.$type"));
        unset($data['typeOfMapping'],$data['type'],$data['shortcode'],$data['oriKey']);
        $allMaps[$key] = $data;
        //see if key has changed
        if($oriKey && $oriKey !== $key){
            //need to delete old key
            unset($allMaps[$oriKey]);
        }
        $this->setting("mappings.$type",$allMaps);
        $block = $form->screen()->element("blockGrid.top.$type");
        $table = $block->element("table$type");
        $this->__addMapsTable($table,$type);
        return $block->render(array('who'=>$type));
    }


    public function pushMappings($screen){
        $grid = $screen->blockGrid();
        $bcdb = $this->loadService('bcDatabase');
        $mappings = $this->setting("mappings");
        $masterId = $this->appConfig("masterAppId");
        $response = $bcdb->collection('apps')->update( array('_id'=>\tip\models\Apps::toMongoId($masterId)), array('traits.0.settings.mappings'=>$mappings), array('w'=>true,'upsert'=>false,'multiple'=>false,));
        Alert::create('success',$grid,'Maps pushed: ' . print_r($response,true));
        return $grid->render();
    }



}
?>