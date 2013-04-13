<?php
namespace crm\traits\contact;

use \tip\traits\base\BaseContactTrait;

class CrmTrait extends BaseContactTrait
{


    public function __construct(array $config = array()) {
        $defaults = array(
        );
        parent::__construct($config + $defaults);
    }

    protected function _init() {
        parent::_init();
    }

    protected $_schema = array(
       'fields' => array(

       )
   );
}
?>