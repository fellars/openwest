<?php
namespace bc\modules\base;

use tip\controls\Module;
use tip\core\Util;

class BaseModule extends Module
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }
    protected function _db(){
        $bcdb = \bc\apps\MasterApp::masterSetting('config.bcdb');
        $self = $this;
        static::addLib('tip_databases');
        $db = $this->_runAsMasterAccount(function() use($bcdb,$self){
            return \tip_databases\services\MongoDbService::byId($bcdb);
        });
        return $db;
    }


}
