<?php
namespace bc\apps;

use tip\controls\App;
use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\core\Util;

class MasterApp extends App
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
        $this->action('','configure');
        $this->config('name','Master App');
        $this->config('type','3rdParty');
        $this->config('category','betacave');
        $this->config('description','Master App for Talent/Company Install');
        $this->config('menuTitle', " ");
        $this->configMenu(array('configure'));

    }




    public function configure(){
        if(!\tip\models\Accounts::currentIsMaster())$this->redirect('/_core/door/login');
        $block = $this->_screenStart('block',__FUNCTION__,array(
            'title'=>'Configure',
            'subtitle'=>"If you haven't configured endpoints yet, <a href='/_core/settings/services'>then do so now</a>"
        ));

        $form = Form::create('config',$block);
        $form->submitButtonText('Done');
        $form->data('disableActionBorder',true);

        $settings = $this->appConfig();
        FormField::selectModel($name = 'bcdb',$form,'BetaCave Global DB',Util::nvlA($settings,"$name"),'endpoints',"tip_databases::mongo_db");
        if(Util::nvlA($settings,"bcdb")){
            $bcdb = $this->loadService('bcdb');
            $options = $bcdb->collection('apps')->get(array('traits.0.app'=>'bc::hitch'),array('name'=>true));
            FormField::field('select',$name = 'masterAppId',$form,array(
                'value'=>Util::nvlA($settings,"$name"),
                'label'=>'Select the Hitch App',
                'options'=>$options,
                'optionsConfig'=>array('value'=>'_id','label'=>'name')
                )
            );
        }else{
            FormField::raw('masterAppId',$form,'Please select the Database connection to Hitch DB First');
        }
		FormField::selectModel($name = 'balanced',$form,'Balanced Payments',Util::nvlA($settings,"$name"),'endpoints',"o_payments::balanced");
        $block->render();
    }


    public static function addSkill($key,$name,$roles,$synonyms=''){
        $me = static::class_path();
        $me = new $me();
        $me->_runAsMasterAccount(function() use($me,$key,$name,$roles,$synonyms){
            $service = $me->loadService('bcdb');
            $masterAppId = $me->appConfig('masterAppId');
            $payload = array('name'=>$name,'roles'=>Util::toArray($roles),'synonyms'=>$synonyms,'ug'=>true);//ug==user generated
            $service->collection('apps')->update(array('_id'=>\tip\models\Apps::toMongoId($masterAppId)),array('$set'=>array("traits.0.settings.mappings.skills.$key"=>$payload)),array('w'=>true));
            //dont' update here, so we can monitor new skills before approving
//            $me->setting("mappings.skills.$key",$payload);
        });

    }








}
