<?php
namespace bc_crunchbase\services;

use _services\services\base\HttpService;

class CrunchbaseService extends HttpService
{


    public function __construct(array $config = array()) {
        $defaults = array(
            'scheme'=>'http',
            'port'=>80,
            'host'=> 'api.crunchbase.com/v/1/',
            'endpointMap' => array('secretKey'),
        );
        parent::__construct($config + $defaults);
    }

    protected function _init() {
        parent::_init();
        $this->_config('category','company');
        $this->_config('description','Connects to Crunchbase API');
    }

    public function secretKey($val = null)
    {
        return $this->config(__FUNCTION__, $val);
    }



    public function search($query,$options=array()){
        return $this->get('search.js',compact('query')+$options);
    }

    public function company($entity){
        return $this->get(__FUNCTION__."/$entity.js");
    }

    public function person($entity){
        return $this->get(__FUNCTION__."/$entity.js");
    }

    public function financialOrg($entity){
        return $this->get("financial-organization/$entity.js");
    }

    public function product($entity){
        return $this->get(__FUNCTION__."/$entity.js");
    }
    public function serviceProvider($entity){
        return $this->get("service-provider/$entity.js");
    }

    public function send($type, $path = null, $data = array(), array $options = array())
    {
        $data += array('api_key'=>$this->secretKey());
//        echo "<pre>"; print_r( $data ); echo "</pre>";exit;
        return json_decode(parent::send($type, $path, $data, $options),true);
    }


}
