<?php
namespace tip_github\services;

use _services\services\OauthService;

class GithubService extends OAuthService{
	public function __construct(array $config = array()) {
		$config += array('endpointMap'=>'oauthLoginUrl,oauthAccessTokenHost,oauthAccessTokenPath,cacheDir','cacheDir'=>\lithium\core\Libraries::get(true,'resources').'/tmp/github','oauthLoginUrl'=>'https://github.com/login/oauth/authorize','oauthAccessTokenHost'=>'github.com','oauthAccessTokenPath'=>'/login/oauth/access_token');
		parent::__construct($config);
	}

	protected function _init() {
		parent::_init();
	}

	public function cacheDir($val = null) {
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
		static::requireFile('Github','Client.php',true,true);
		static::requireFile('Buzz','Client\Curl.php',true,true);
		$this->_service = new \Github\Client( new \Github\HttpClient\CachedHttpClient(array('cache_dir' => $this->cacheDir())) );
		if($token = $this->accessToken()){
			$this->_service->authenticate($token,$this->accessSecret(),\Github\Client::AUTH_URL_TOKEN);
		}else if ($key = $this->consumerKey()){
			$this->_service->authenticate($key,$this->consumerSecret(),\Github\Client::AUTH_URL_CLIENT_ID);
		}
		return $this->_service;
	}

	public function oauthUrl($type,$redirect="",$scope=""){
		$redirect = urlencode($redirect);
		$state = \tip\core\Encryption::salt();
		\lithium\storage\Session::write('__oauth_state__',$state);
		$url = $this->oauthLoginUrl();
		$clientId = $this->consumerKey();
		if(!$scope)$scope = $this->scope();
		return "$url?client_id=$clientId&redirect_uri=$redirect&state=$state&scope=$scope";
    }

    public function oauthLogin($control){
		$existingState = \lithium\storage\Session::read('__oauth_state__');
		\lithium\storage\Session::delete('__oauth_state__');
		$state = $control->requestData('state');
		$code =  $control->requestData('code');
		if($existingState && $state && $existingState === $state){
			$token = $this->getAccessToken($code);
			static::currentUserToken($token);
			$this->accessToken($token);
			$this->service(false);//reset service
			$user_profile = $this->api('current_user')->show();
			try{
				$emails = $this->api('current_user')->emails()->all();
				$user_profile['emails'] = $emails;
			}catch(\Exception $e){}
			return $this->_userEndpointLogin('githubId',$user_profile,$user_profile['name']);
		}
		return $this->endpoint();


    }

	public function getAccessToken($code){
		$http = new \_services\services\base\HttpService(array('host'=>$this->oauthAccessTokenHost()));
		$path = $this->oauthAccessTokenPath();
		$client_id = $this->consumerKey();
		$client_secret = $this->consumerSecret();
		$response = $http->post($path,compact('client_id','client_secret','code'),array('Accept'=>'application/json'));
		return $response['access_token'];

	}


}
