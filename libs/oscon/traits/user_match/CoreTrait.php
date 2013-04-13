<?php
namespace bc\traits\user_match;

use tip\core\Util;
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
          'user'=>array(),
          'company'=>array(),
		  'companyName'=>array(
		  ),
          'companyScore'=>array(
             ),
          'companyDetails'=>array(),
          'companyInterest'=>array(
                'type'=>'slider',
                'size'=>6,
                'height'=>10,
                'requireSelection'=>true,
                'allowCancel'=>true,
                'range'=>false
            ),
		  'share'=>array(
			'type'=>'radio',
			'options'=>'no,yes',
			'defaultValue'=> 'no'
		  ),
		  'opportunities'=>array(
			  'type'=>'list',
			  'newField'=>array(
				  'type'=>'object',
				  'fields'=>array(
					  'title'=>array(),
					  'score'=>array(),
					  'details'=>array(),
					  'interest'=>array(
						  'type'=>'slider',
						  'size'=>6,
						  'height'=>10,
						  'requireSelection'=>true,
						  'allowCancel'=>true,
						  'range'=>false
					  ),
				  )
			  )
	     ),

      )
    );

	protected function _prePrepareShare($field){
		$field['value'] = Util::isTruthy($field['value'],true) ? 'yes' : 'no';
		return $field;
	}
	protected function _preProcessShare($field){
		$field->value(Util::isTruthy($field->value(),true));
		return $field;
	}

}
