<?php
namespace bc\modules\crm\import;
\tip\controls\base\BaseControl::addLib('crm');


use crm\modules\base\BaseImportModule;
use tip\controls\base\BaseControl;
use tip\screens\elements\FormField;
use tip\core\Util;
use bc\traits\contact\BetacavePreLaunchTrait;



class BetacaveImportModule extends BaseImportModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();

    }

    public function settingsForm($form,$config){
        FormField::selectModel($name = 'bcMongolab',$form,'',Util::nvlA($config,"$name"),'endpoints','tip_mongolab::mongolab');
    }



    public function doImport($lastImport, $createEntityFunc){
        $mongolab = $this->app()->loadService('bcMongolab');
        $lastImportDate = Util::nvlA($lastImport,'lastCreated');
        $q = array();
        $extraMatch = $lastImportDate ? array('created'=>array('$gt'=>$lastImportDate)) : array('created'=>array('$exists'=>true));
        $q['$or'] = array(array('created'=>array('$exists'=>false)),$extraMatch);
//        echo "<pre>"; print_r( $lastImport ); echo "</pre>";exit;
        $new = $mongolab->listDocuments('users',$q);
//        echo "$lastImportDate<pre>"; print_r( $new ); echo "</pre>";exit;
        $result = array('count'=>count($new),'lastCreated'=>$lastImportDate,'lastImported'=>Util::nvlA($lastImport,'lastImported'));
//        echo "<pre>"; print_r($q);print_r( $result );print_r($new); echo "</pre>";exit;
        foreach($new as $data){
            $id = Util::nvlA($data,'_id.$oid');
            $myLastDate = Util::nvlA($data,'created');
            if(!$myLastDate){
                //need to update
                $mId = new \MongoId($id);
                $myLastDate = $mId->getTimestamp();
                set_time_limit(100);
                $data['created']=$myLastDate;
//                echo "$id<pre>"; print_r( date('m/d/y',$time) ); echo "</pre>";exit;
                $mongolab->updateDocById('users',$id,array('$set'=>array('created'=>$myLastDate,'updated'=>$myLastDate)));
            }
            $name = Util::nvlA($data,'name','-not provided-');
            $createEntityFunc('betacave',array('name'=>$name,'type'=>'pre-launch','email'=>Util::nvlA($data,'email')),new BetacavePreLaunchTrait(compact('data')));
            $allLastDate = $result['lastCreated'] = max($result['lastCreated'],$myLastDate);
            if($myLastDate == $allLastDate)$result['lastImported'] = $id;
        }
        return $result;
    }


}


?>