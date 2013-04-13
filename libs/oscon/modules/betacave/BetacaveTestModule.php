<?php
namespace bc\modules\betacave;

use \tip\controls\Module;

class BetacaveTestModule extends Module
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
        $this->config('modType','import');

    }


}


?>