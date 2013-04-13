<?php
namespace crm\apps;

use tip\controls\App;
use tip\screens\elements\Block;
use tip\screens\elements\Raw;
use tip\screens\elements\Table;
use tip\screens\elements\Menu;
use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\elements\Alert;

use tip\core\Util;
use tip\screens\helpers\base\HtmlTags;
use crm\traits\contact\CrmTrait;

class CrmApp extends App{


    public function __construct(array $config = array()) {
        parent::__construct($config);
    }


    protected function _init() {
        parent::_init();
        $this->action('|go','contacts');


        $this->config('name','CRM App');
        $this->config('type','crm');
        $this->config('category','sales');
        $this->config('description','Simple CRM to handle leads');
        $this->configMenu(array('contacts::View Contacts','import::Import Contacts','configure'));

        $this->action('screen:module:import','module_import');
    }

    protected function _screenStart($screen, $active = '', $config = array(), $screenConfig = array())
    {
        $this->_modules = array();//reset modules with each screen refresh
        return parent::_screenStart($screen, $active, $config, $screenConfig);
    }


    public function contacts(){
        $block = $this->_screenStart('block','contacts',true);
        $views = array();
        $filters = array();
        $massActions = array();


        $filterMods = $this->modules('filter');
//        echo "<pre>"; print_r( array_keys($filterMods)); echo "</pre>";exit;
        $view = $this->propUserSetting('crmFilter.view');
        $filtersVal = Util::toArray($this->propUserSetting('crmFilter.filters'));

        foreach($filterMods as $filterMod){
            $modViews = Util::toArray($filterMod->views());
            $newViews = array();
            $filterLabel = $filterMod->label();
            foreach($modViews as $modViewKey=>$modView){

                $newViews[] = "$modViewKey::[$filterLabel] ".$modView['label'];
                if(!$view)$view = $modViewKey;
            }
            $views = array_merge($views,$newViews);


            $modFilters = Util::toArray($filterMod->filters());
            $newFilters = array();
            foreach($modFilters as $modFilterKey=>$modFilter){
                $newFilters[] = "$modFilterKey::[$filterLabel] ".$modFilter['label'];
            }
            $filters = array_merge($filters,$newFilters);

            $modMassActions = Util::toArray($filterMod->massActions());
            $newMassActions = array();
            foreach($modMassActions as $modMassActionKey=>$modMassAction){
                $newMassActions[] = "$modMassActionKey::[$filterLabel] ".$modMassAction['label'];
            }
            $massActions = array_merge($massActions,$newMassActions);




        }

        $form = Form::create('filterSelect',$block,array('formLayout'=>'inline','submitButton'=>false,'disableActionBorder'=>true));
        $form->data('fieldSetSize',12);
        FormField::field('select','view',$form,array(
           'options' => $views,
           'multiple'=>false,
            'label'=>false,
            'value'=>$view,
            'placeholder' =>"View",
           'submitOnChange'=>true,
            'closeOnSelect'=>true,
            'allowClear'=>false,
            'size'=>4,

        ));
        FormField::field('select','filters',$form,array(
           'options' => $filters,
            'value' =>$filtersVal,
           'multiple'=>true,
            'label'=>false,
            'placeholder' =>"Filters",
           'submitOnChange'=>true,
            'closeOnSelect'=>true,
            'allowClear'=>true,
            'size'=>4,

        ));
        FormField::field('select','massAction',$form,array(
           'options' => $massActions,
           'multiple'=>false,
            'label'=>false,
            'placeholder' =>"Actions",
           'submitOnChange'=>true,
            'closeOnSelect'=>true,
            'allowClear'=>true,
            'size'=>4,

        ));

        $table = Table::create('contacts',$block);
//        $table->addRows($contacts,$process);
        $this->form_filterSelect($form,array('view'=>$view,'filters'=>false,'massAction'=>''),false);
        $block->screen()->render();
    }

    public function massActionRefresh($screen,$massAction){
        return $this->form_filterSelect($screen->element('block')->element('filterSelect'),array('massAction'=>$massAction),'massAction');
    }

    public function form_filterSelect($form,$data,$submitOnChange){


        $filterMods = $this->modules('filter');

        $view = Util::nvlA($data,'view',$this->propUserSetting('crmFilter.view'));
        $filters = Util::nvlA($data,'filters');
        $filters = Util::toArray($filters===false ? $this->propUserSetting('crmFilter.filters') : $filters);
//        echo "<pre>"; print_r( $filters ); echo "</pre>";exit;
        $massAction = Util::nvlA($data,'massAction');
        $this->propUserSetting('crmFilter.view',$view);
        $this->propUserSetting('crmFilter.filters',$filters);

        $dbFilter = array();
        $memFilters = array();
        $headers = array();
        $process = array();
        $processActions = null;
        foreach($filterMods as $filterMod){
            $views = $filterMod->views();
            if($viewHeaders = Util::nvlA($views,"$view.headers")){
                $headers = array_merge($headers,$viewHeaders);
            }
            $process += Util::toArray(Util::nvlA($views,"$view.process"));
            $modRowActions = $filterMod->rowActions();
            foreach(array(Util::nvlA($modRowActions,$view),Util::nvlA($modRowActions,'*')) as $rowActions ){
                if($rowActions){

//                    echo "$view<pre>"; print_r( $process );print_r($rowActions); echo "</pre>";exit;
                    if(is_callable($processActions)){
                        $prevActions = $processActions;
                        $processActions = function($val,$col,$row,$index) use($rowActions,$prevActions){
                            return array_merge(Util::toArray(call_user_func($prevActions,$val,$col,$row,$index)), Util::toArray(call_user_func($rowActions,$val,$col,$row,$index)));
                        };
                    }else{
                        $processActions = $rowActions;

                    }
                }
            }
            $modFilters = $filterMod->filters();

            foreach($filters as $filter){
                $db = Util::nvlA($modFilters,"$filter.db");
                $f = Util::nvlA($modFilters,"$filter.filter");
                if(is_callable($f))$memFilters += array($filter=>$f);
                if(is_callable($db))$db=call_user_func($db,$filter,$filters);
                $dbFilter += Util::toArray($db);
            }
        }
        if($processActions){

            $process['actions'] = function($val,$col,$row,$index) use($processActions){
                $allActions = Util::toArray(call_user_func($processActions,$val,$col,$row,$index ));

                return HtmlTags::buttonGroup( $allActions, 3);
            };

        }

        $contacts = CrmTrait::find($dbFilter,$memFilters);

        if(!$headers)$headers = array('name');//default
        $headers = array_merge($headers,array('actions'));//always add actions

        $table = $form->parent()->element('contacts');
        $table->clearHeaders()->addHeaders($headers);
        $table->clear()->addRows($contacts,$process);
//        echo "count:<pre>"; print_r( (count($contacts))); echo "</pre>";exit;

        if($massAction && $submitOnChange =='massAction'){
            return $this->_massAction($form->screen(),$massAction,$contacts);
        }

        return $submitOnChange ? $form->parent()->render(array('status'=>'parent')) : $form;

    }

    protected function _massAction($screen,$action,$contacts){
        $filterMods = $this->modules('filter');
        foreach($filterMods as $filterMod){
            $modMassActions = $filterMod->massActions();
            $mAction = Util::nvlA($modMassActions,"$action.do");

            if(is_callable($mAction)){
                $this->action('screen:crm:massAction:refresh','massActionRefresh');
                call_user_func($mAction,$screen,$action,$contacts,"crm:massAction:refresh/$action");
                return;
            }
        }

    }

    public function import(){
        $block = $this->_screenStart('block','import',true);
        $table = Table::create('endpoints',$block)->addHeaders('module,last_update,actions');
        $importModules =  $this->modules('import');
        $lastUpdates = $this->setting('lastImports');
        $defaultActions = function($val,$key,$row){
            return HtmlTags::modalLink('Import','module:import',array('module'=>Util::nvlA($row,'module')),array('class'=>'btn'));
        };
        foreach($importModules as $importModPath=>$importMod){
            $row = array('module'=>$importModPath,'last_update'=>Util::nvlA($lastUpdates,"$importModPath.date"));
            $actions = $importMod->rowActions();
            if($actions === true)$actions = $defaultActions;
            if (is_callable($actions)){
                $actions = compact('actions');
            }else{
                $actions = array();
            }
            $table->addRow($row,array('last_update'=>Table::filterDate(null,'-'))+$actions);
        }
        $block->screen()->render();

    }



    public function configure(){
        $block = $this->_screenStart('block',__FUNCTION__,array(
            'title'=>'Settings',
            'subtitle'=>"If you haven't configured endpoints yet, <a href='/_core/settings/services'>then do so now</a>"
        ));

        $form = Form::create('config',$block);
        $form->submitButtonText('Done');
        $form->data('disableActionBorder',true);
        $settings = $this->appConfig();
        FormField::raw($section = 'importModuleSettings',$form,' ',HtmlTags::tag('b',Util::inflect($section,'h')));
        $importModules =  $this->modules('import');
        foreach($importModules as $importMod){
            $importMod->settingsForm($form,$settings);
        }
        FormField::raw($section = 'filterModuleSettings',$form,' ',HtmlTags::tag('b',Util::inflect($section,'h')));
        $importModules =  $this->modules('filter');
        foreach($importModules as $importMod){
            $importMod->settingsForm($form,$settings);
        }
        $block->screen()->render();
    }


    public function module_import($screen){
        $moduleClass = $this->requestData('module');

        $module = $this->module($moduleClass);
        $funcCreate = function ($source,$coreData,$traits,$save=true){
            $traits = is_array($traits) ? $traits : array($traits);

            $newEntity = CrmTrait::newEntity($coreData,array('importSource'=>$source),false);
            foreach($traits as $trait){
                $newEntity->addTrait($trait,'replace',false);
            }
            $saved = $newEntity->save();
        };

        set_time_limit(600);

        $results = $module->doImport($this->setting("lastImports.$moduleClass"),$funcCreate);
        $results['date'] = time();
        $this->setting("lastImports.$moduleClass",$results,true);
//        echo "<pre>"; print_r( $results ); print_r($this->setting("lastImports.$moduleClass")); echo "</pre>";exit;
        $count = Util::nvlA($results,'count','?');
        $this->renderJson(array('status'=>'silent','closeAll'=>true,'_alert_'=>Alert::create('success',null,array('text'=>"Successfully imported $count contacts"))->renderQuick()));

    }



}
?>