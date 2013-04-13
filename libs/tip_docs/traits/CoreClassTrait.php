<?php
namespace tip_docs\traits;

use tip\traits\base\CoreBaseTrait;

class CoreClassTrait extends CoreBaseTrait{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

    protected $_schema = array(
       'core'=>array(
           'fieldSets'=>array(
               'main'=>array(
                    'name'=>array('type'=>'raw'),
                    'status'=>array('type'=>'hidden'),
                    'class'=>array('type'=>'raw'),
                    'library'=>array('type'=>'raw'),
                    'checksum'=>array('type'=>'raw'),
                    'parent'=>array('type'=>'raw'),
                    'properties'=>array('type'=>'raw'),
                    'methods'=>array('type'=>'raw'),
                    'start'=>array('type'=>'raw'),
                    'end'=>array('type'=>'raw'),
                    'file'=>array('type'=>'raw'),
                    'comment'=>array('type'=>'raw'),
                    'namespace'=>array('type'=>'raw'),
                    'shortName'=>array('type'=>'raw'),
                    'length'=>array('type'=>'raw'),
                    'text'=>array('type'=>'raw'),
                    'tags'=>array('type'=>'raw'),
               ),
               'other'=>array(
                    'fields'=>array(
                        'description'=>array('type'=>'raw'),
                    )
                )
           )
       ),
    );


}
