<?php
namespace bc\traits\company;

use tip\traits\base\CoreBaseTrait;

class CoreTrait extends CoreBaseTrait
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
          '_dataSource'=>array(),
          'size'=>array(),
          'locations'=>array(
              'type'=>'select',
              'multiple'=>true
          ),
          'markets'=>array(
                'type'=>'select',
                'multiple'=>true
           ),
          'url' => array(),
		  'corporateAccount'=>array('type'=>'hidden'),
		  'priority'=>array('type'=>'hidden'),
		  'ready'=>array('type'=>'hidden'),
      )
    );


}
