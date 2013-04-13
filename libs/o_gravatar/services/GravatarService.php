<?php
namespace o_gravatar\services;

use _core\apps\MediaApp;
use tip\services\base\BaseService;
use tip\traits\CoreMediaTrait;

class GravatarService extends BaseService{
	public function __construct(array $config = array()) {
		parent::__construct($config);
	}

	protected function _init() {
		parent::_init();
	}

	public function defaultImage($val = null) {
		return $this->config(__FUNCTION__, $val);
	}

	public function url($email,$options=array()){
		$options += array('secure'=>true,'image'=>false,'default'=>$this->defaultImage(),
		'size'=>80,'rating'=>'pg','imgAtts'=>array());
		$url = $options['secure'] ? 'https://secure.gravatar.com/' : 'http://www.gravatar.com/';
		$url .= 'avatar/';
		$url .= md5( strtolower( trim( $email ) ) );
		$s = $options['size'];
		$d = $options['default'];
		$r = $options['rating'];
		$img = $options['image'];

		$url .= "?s=$s&d=$d&r=$r";
		if ( $img ) {
			$url = '<img src="' . $url . '"';
			$atts = $options['imgAtts'];
			foreach ( $atts as $key => $val )
				$url .= ' ' . $key . '="' . $val . '"';
			$url .= ' />';
		}
		return $url;
	}

	public function img($email,$options=array()){
		return $this->url($email,$options+array('image'=>true));
	}

	public function asMedia($email,$options=array()){
		$url = $this->url($email,$options);
		//check if already exists
		$media = CoreMediaTrait::findFirst(array('source'=>'url','file.source'=>$url));
		if($media)return $media;
		return MediaApp::newMedia('url',$url);
	}


}