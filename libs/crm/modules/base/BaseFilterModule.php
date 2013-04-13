<?php
namespace crm\modules\base;

use \tip\controls\Module;
use tip\screens\elements\FormField;
use tip\core\Util;


class BaseFilterModule extends Module
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
        $this->label(str_replace(" Filter","",$this->label()));//remove Filter

    }

    public function settingsForm($form,$settings){

    }

    public function filters(){
        return array();
    }

    public function views(){
        return array();
    }

    public function massActions(){
        return array();
    }
    public function rowActions(){
        return array();
    }
}


?>