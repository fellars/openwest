<?php
namespace bc\hitch;

use tip\core\Util;

class Ranker extends \tip\core\base\BaseObject
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }



    public function mappings($val = null)
    {
        return $this->config(__FUNCTION__, $val);
    }

    public function map($key){
        return Util::nvlA2A($this->mappings(),$key);
    }

    public function app($val = null)
    {
        return $this->config(__FUNCTION__, $val);
    }


}
