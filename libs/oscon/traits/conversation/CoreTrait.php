<?php
namespace bc\traits\conversation;

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
		    'corporate'=>array(),
			'opportunity'=>array(),

		    'user'=>array(),
		    'topic'=>array(),
		    'thread'=>array(
				'type'=>'list',
				'newItem'=>array(
					'type'=>'object',
					'fields'=>array(
						'who'=>array(),
						'msg'=>array('type'=>'textArea'),
						'time'=>array(),
					)
				)
			)
      )
    );


}
