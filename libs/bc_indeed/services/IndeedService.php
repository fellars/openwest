<?php
namespace bc_indeed\services;

use _services\services\base\HttpService;

class IndeedService extends HttpService
{


    public function __construct(array $config = array()) {
        $defaults = array(
            'scheme' => 'http',
            'port' => 80,
            'host'=> 'api.indeed.com/ads/',
            'format'=>'json',
            'userIp'=>$_SERVER['REMOTE_ADDR'],
            'userAgent'=>$_SERVER['HTTP_USER_AGENT'],
            'endpointMap' => array('publisherId'),
        );
        parent::__construct($config + $defaults);
    }

    protected function _init() {
        parent::_init();
        $this->_config('category','jobs');
        $this->_config('description','Connects to Indeed API');
    }

    public function publisherId($val = null)
    {
        return $this->config(__FUNCTION__, $val);
    }

    public function format($val = null)
    {
        return $this->config(__FUNCTION__, $val);
    }

    public function channel($val = null)
    {
        return $this->config(__FUNCTION__, $val);
    }

    public function userIp($val = null)
    {
        return $this->config(__FUNCTION__, $val);
    }

    public function userAgent($val = null)
    {
        return $this->config(__FUNCTION__, $val);
    }

    protected function _defaults(){
        return array('v'=>2,'publisher'=>$this->publisherId(),'format'=>$this->format(),'chnl'=>$this->channel(),'userip'=>$this->userIp(),'useragent'=>$this->userAgent());
    }
    public function search($q,$options=array()){
        if(is_array($q))$options = $q;
        else if($q)$options = compact('q')+$options;

        $options = $options + $this->_defaults();
        return $this->get('apisearch',$options);
    }

    public function jobs($jobkeys){
        $options = array('jobkeys'=> implode(',',\tip\core\Util::toArray($jobkeys))) + $this->_defaults();
        return $this->get('apigetjobs',$options);
    }

    public function send($type, $path = null, $data = array(), array $options = array())
    {
        $result = parent::send($type, $path, $data, $options);
		return \tip\core\Util::checkJson($result);
    }


}
