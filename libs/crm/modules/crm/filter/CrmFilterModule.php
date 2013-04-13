<?php
namespace crm\modules\crm\filter;

use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\elements\Table;
use tip\core\Util;
use crm\modules\base\BaseFilterModule;
use tip\screens\elements\Alert;
use tip\screens\helpers\base\HtmlTags;

class CrmFilterModule extends BaseFilterModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
        $this->screenAction('crm:edit:single','editSingle');
        $this->screenAction('crm:delete:single','deleteSingle');

    }

    public function settingsForm($form,$settings){

    }

    public function filters(){
        return array(
            "crm_active"=>array(
                "label"=>"Active Contacts",
                "filter"=>function($contact){
                    return ($contact->status == 'active');
                },
            ),
            "crm_inactive"=>array(
                "label"=>"Inactive Contacts",
                "filter"=>function($contact){
                    return ($contact->status == 'inactive');
                },
            ),
        );
    }

    public function views(){
        return array(
            "crm_standard"=>array(
                "label"=>"Standard View",
                "headers"=>array(
                    'name','status'
                ),
                "process"=>array(
                )
            ),
        );
    }

    public function massActions(){
        return array(
            'crm_deleteAll'=>array(
                "label"=>"Mass Delete",
                "do"=>array($this,'massDelete')
            )
        );
    }
    public function rowActions(){
        return array(
            "*"=>function($val,$col,$row){return array(
                HtmlTags::modalLink('Edit','/screen/_module_/crm:edit:single/'.$row->id(),array(),array('class'=>'btn')) ,
                HtmlTags::modalLink('Delete','/screen/_module_/crm:delete:single/'.$row->id(),array(),array('class'=>'btn'))
            );}

        );
    }

    public function deleteSingle($screen,$id){
        return $this->massDelete($screen,'massDelete',array(\tip\models\Contacts::byId($id)),true);
    }

    public function massDelete($screen,$action,$contacts,$refreshUrl){

        $form = Form::create('massDelete',$screen,array('submitButtonText'=>'Delete All','cancelButton'=>true));
        FormField::field('raw','heading',$form,array('label'=>false,'html'=>'Are you Sure you want to delete these contacts?'));
        FormField::field('radio','delete',$form,array('value'=>'full','label'=>'How should we delete these?','options'=>'full::Delete entire contact,hide::Just remove CRM trait(will hide from this app but contact still exist in database and other apps)'));
        $contactTable = Table::create('contactTable');
        $contactTable->addHeader(array('name'=>'id','valPath'=>'_id'));
        $contactTable->addHeaders('name,email::::/email');
        $contactTable->data('selectAll',true);
        $contactTable->data('selectCol','id');

        $contactTable->addRows($contacts);

        FormField::field('table','contacts',$form,array(
           'table'=>$contactTable,

        ));
        return $form->render(array('status'=>'modal','maxHeight'=>5000,'minWidth'=>1000,'server'=>true,'close'=>false,'closeButton'=>false,'refreshOnAppear'=>true,'refreshUrl'=>$refreshUrl));

    }

    public function form_massDelete($form,$data){
        $delete = $data['delete'];
        $contacts = Util::nvlA2A($data,'contacts');
        $count = count($contacts);
        foreach($contacts as $contact){
            if($delete == 'full'){
                \tip\models\Contacts::deleteById($contact);
            }else{//just remove crm trait
                $c = \tip\models\Contacts::byId($contact);
                $c->removeTrait('crm::crm',true);
            }
        }
        return $form->render(array('status'=>'silent','_alert_'=>Alert::create('success',null,"Successfully Deleted $count contacts. Click <a href=\"/crm/crm\">here to refresh</a>")->renderQuick()));
    }
}


?>