<?php
namespace tip_stackexchange\services;

use _services\services\OauthService;

class StackExchangeService extends OAuthService{
	public function __construct(array $config = array()) {
		$config += array('image'=>'/tip_stackexchange/img/se-icon.png','endpointMap'=>'site,oauthLoginUrl,oauthAccessTokenHost,oauthAccessTokenPath','site'=>'stackoverflow','scope'=>'read_inbox,no_expiry','oauthLoginUrl'=>'https://stackexchange.com/oauth','oauthAccessTokenHost'=>'stackexchange.com','oauthAccessTokenPath'=>'/oauth/access_token');
		parent::__construct($config);
	}

	protected function _init() {
		parent::_init();
	}

	public function site($val = null) {
		return $this->config(__FUNCTION__, $val);
	}
	public function oauthLoginUrl($val = null) {
		return $this->config(__FUNCTION__, $val);
	}

	public function oauthAccessTokenHost($val = null) {
		return $this->config(__FUNCTION__, $val);
	}

	public function oauthAccessTokenPath($val = null) {
		return $this->config(__FUNCTION__, $val);
	}
	protected function _loadService() {
		static::requireFile('stackphp','api.php',true,true);
		$key = $this->additionalKey();
		if($key){
			\API::$key = $key;
		}else if($token = $this->accessToken()){
			\API::$key = $token;
		}else if ($key = $this->consumerKey()){
			\API::$key = $key;
		}
		$this->_service = \API::Site($this->site());
		return $this->_service;
	}

	public function oauthUrl($type,$redirect="",$scope=""){
		$redirect = urlencode($redirect);
		$state = \tip\core\Encryption::salt();
		\lithium\storage\Session::write('__oauth_state__',$state);
		\lithium\storage\Session::write('__oauth_redirect__',$redirect);
		$url = $this->oauthLoginUrl();
		$clientId = $this->consumerKey();
		if(!$scope)$scope = $this->scope();
		return "$url?client_id=$clientId&redirect_uri=$redirect&state=$state&scope=$scope";
    }

    public function oauthLogin($control){
		$existingState = \lithium\storage\Session::read('__oauth_state__');
		\lithium\storage\Session::delete('__oauth_state__');
		$redirect = \lithium\storage\Session::read('__oauth_redirect__');
		\lithium\storage\Session::delete('__oauth_redirect__');
		$state = $control->requestData('state');
		$code =  $control->requestData('code');
		if($existingState && $state && $existingState === $state){
			$token = $this->getAccessToken($code,$redirect);
			static::currentUserToken($token);
			$this->accessToken($token);
			$this->service(false);//reset service
			$user_profile = $this->service()->Me($token)->Exec()->Fetch();
			return $this->_userEndpointLogin('seId',$user_profile,$user_profile['display_name'],'user_id');
		}
		return $this->endpoint();


    }

	public function getAccessToken($code,$redirect_uri){
		$http = new \_services\services\base\HttpService(array('host'=>$this->oauthAccessTokenHost()));
		$path = $this->oauthAccessTokenPath();
		$client_id = $this->consumerKey();
		$client_secret = $this->consumerSecret();
		$response = $http->post($path,compact('client_id','client_secret','code','redirect_uri'));
		$token = str_replace("access_token=","",$response);
		return $token;

	}


}
