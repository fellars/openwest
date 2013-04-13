<?php
namespace tip_docs\traits;

use tip\traits\base\CoreBaseTrait;

class CoreMethodTrait extends CoreBaseTrait{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

    protected $_schema = array(
        'fields' => array(
            'class'=>array('type'=>'raw'),
            'checksum'=>array('type'=>'raw'),
            'modifiers'=>array('type'=>'raw'),
            'params'=>array('type'=>'raw'),
            'start'=>array('type'=>'raw'),
            'end'=>array('type'=>'raw'),
            'length'=>array('type'=>'raw'),
            'text'=>array('type'=>'raw'),
            'tags'=>array('type'=>'raw'),

        ),
    );


}
