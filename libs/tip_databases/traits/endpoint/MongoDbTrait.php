<?php
namespace tip_databases\traits\endpoint;

use tip\traits\base\BaseEndpointTrait;

class MongoDbTrait extends BaseEndpointTrait
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

    protected $_schema = array(
      'fields'=>array(
          'host'=>array(),
          'port'=>array(),
          'database'=>array(),
          'username'=>array(),
          'password'=>array('type'=>'encrypted'),

      )
    );


}
