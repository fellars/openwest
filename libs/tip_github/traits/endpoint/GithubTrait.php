<?php
namespace tip_github\traits\endpoint;

use _services\traits\endpoint\base\OauthClientTrait;

class GithubTrait extends OauthClientTrait{
	public function __construct(array $config = array()) {
     parent::__construct($config);
 }

}
