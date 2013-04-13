<?php
namespace crm\modules\base;

use \tip\controls\Module;
use tip\screens\elements\FormField;
use tip\core\Util;

class BaseImportModule extends Module
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();

    }

    public function settingsForm($form,$settings){
    }

    public function doImport($lastImport, $createEntityFunc){
    }

    public function rowActions(){return true;}

}


?>