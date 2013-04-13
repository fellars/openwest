<?php
namespace bc\modules\crm\filter;

use tip\screens\elements\FormField;
use tip\core\Util;
use crm\modules\base\BaseFilterModule;
use tip\screens\elements\Table;

class BetacaveFilterModule extends BaseFilterModule
{
    public function __construct(array $config = array())
    {
        $defaults = array();
        parent::__construct($config+$defaults);
    }

    protected function _init()
    {
        parent::_init();

    }

    public function settingsForm($form,$settings){

    }

    public function filters(){
        return array(
            "bc_named"=>array(
                "label"=>"Named Contacts",
                "filter"=>function($contact){
                    return ($contact->name && $contact->name != '-not provided-');
                },
            ),
            "bc_talent"=>array(
                "label"=>"Individual Talent Types",
                "filter"=>function($contact){
                    return ($contact->traitData('betacave::betacave_pre_launch/type') === 'talent');
                },
            ),
            "bc_company"=>array(
                "label"=>"Company Types",
                "filter"=>function($contact){
                    return ($contact->traitData('betacave::betacave_pre_launch/type') === 'company');
                },
            ),
            "bc_confirmed"=>array(
                "label"=>"Confirmed Contacts",
                "filter"=>function($contact){
                    return (bool)$contact->traitData('betacave::betacave_pre_launch','status.emailConfirmed');
                },
            ),
            "bc_unconfirmed"=>array(
                "label"=>"Unconfirmed Contacts",
                "filter"=>function($contact){
                    return !(bool)$contact->traitData('betacave::betacave_pre_launch','status.emailConfirmed');
                },
            ),
        );
    }

    public function views(){
        return array(
            "bc_standard"=>array(
                "label"=>"Standard View",
                "headers"=>array(
                    'email::::/email','name','confirmed', 'type::::betacave::betacave_pre_launch/type','date::::betacave::betacave_pre_launch/created'
                ),
                "process"=>array(
                    'confirmed'=>function($val,$col,$row){
                        return $row->traitData('betacave::betacave_pre_launch','status.emailConfirmed') ? 'Confirmed' : 'Not Confirmed';
                    },
                    'date'=>Table::filterDate(),
                )
            ),
            "bc_company"=>array(
                "label"=>"Companies View",
                "headers"=>array(
                    'email::::/email','name','companyName::::betacave::betacave_pre_launch/companyName','confirmed', 'type::::betacave::betacave_pre_launch/type'
                ),
                "process"=>array(
                    'confirmed'=>function($val,$col,$row){
                        return $row->traitData('betacave::betacave_pre_launch','status.emailConfirmed') ? 'Confirmed' : 'Not Confirmed';
                    }
                )
            ),
        );
    }

    public function massActions(){
        return array(

        );
    }




    public function rowActions(){
        return array(


        );
    }
}


?>