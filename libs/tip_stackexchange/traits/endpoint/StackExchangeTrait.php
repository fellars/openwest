<?php
namespace tip_stackexchange\traits\endpoint;

use _services\traits\endpoint\base\OauthClientTrait;

class StackExchangeTrait extends OauthClientTrait{
	public function __construct(array $config = array()) {
     parent::__construct($config);
 }

}
