<?php
namespace bc_crunchbase\traits\endpoint;

use \tip\traits\base\BaseEndpointTrait;

class CrunchbaseTrait extends BaseEndpointTrait
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
           'secretKey' => array('required'=>true,'encrypt'=>true),

       )
   );
}
