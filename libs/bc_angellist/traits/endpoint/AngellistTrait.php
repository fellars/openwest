<?php
namespace bc_angellist\traits\endpoint;

use \tip\traits\base\BaseEndpointTrait;

class AngellistTrait extends BaseEndpointTrait
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
//           'secretKey' => array('required'=>true,'encrypt'=>true),

       )
   );
}
