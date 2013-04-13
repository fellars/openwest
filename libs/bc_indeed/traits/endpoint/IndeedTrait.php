<?php
namespace bc_indeed\traits\endpoint;

use \tip\traits\base\BaseEndpointTrait;

class IndeedTrait extends BaseEndpointTrait
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
           'publisherId' => array('required'=>true,'encrypt'=>true),

       )
   );
}
