<?php
namespace tip_databases\services;

use tip\services\base\BaseService;
class MongoDbService extends BaseService{
    protected $_collection;
    public function __construct(array $config = array())
    {
        $defaults = array('endpointMap'=>'host,port,username,password,database');
        parent::__construct($config+$defaults);
    }

    protected function _init()
    {
        parent::_init();
    }

    protected function _loadService()
    {
        $host = $this->_buildHost();
        $connection = new \Mongo($host);
        $this->_service = $connection->selectDB($this->database());
        return $this->_service;
    }


    protected function _buildHost(){
        $username = $this->username();
        $password = $this->password();
        $host = $this->host();
        $port = $this->port();
        $db = $this->database();
        return "mongodb://$username:$password@$host:$port/$db";
    }

    public function port($val = null)
    {
        return $this->config(__FUNCTION__, $val);
    }

    public function database($val = null)
    {
        return $this->config(__FUNCTION__, $val);
    }

	public function collection($c=null,$returnThis=true)
	{
		if($c)$this->_collection = $this->service()->selectCollection($c);
        return $returnThis ? $this : $this->_collection;
	}

	public function insert($f, $c = '')
	{
        return $this->collection($c,false)->insert($f);
	}

    public function getById($_id,$fields=array(),$c=''){
        $first = $this->get1( array('_id'=>new \MongoId($_id)), $fields, $c);

        return $first;
    }
	public function get1($q=array(),$fields=array(),$c='')
	{
		return $this->collection($c,false)->findOne($q,$fields);
	}
	public function get($q,$fields=array(),$options=array(),$c='')
	{
		$options += array('limit'=>false,'sort'=>false);
        $cursor = $this->collection($c,false)->find($q,$fields);
		$limit = $options['limit'];
		$sort = $options['sort'];
		if($limit){
			$cursor = $cursor->limit($limit);
		}
		if($sort){
			$cursor = $cursor->sort($sort);
		}
		$k = array();
		$i = 0;

		while( $cursor->hasNext() )
		{
		    $k[$i] = $cursor->getNext();
			$i++;
		}
		if($limit == 1 && count($k) === 1)return $k[0];
		return $k;
	}

	public function update($criteria, $data,$options=array(),$c='')
	{
        return $this->collection($c,false)->update($criteria, array('$set'=>$data),$options);
	}

	public function deleteById($id,$c=''){
		$criteria = array('_id'=>new \MongoId($id));
		return $this->delete($criteria,true,$c);
	}

	public function delete($criteria, $one = FALSE,$c='')
	{
		if(is_bool($one))$one = array('justOne'=>$one);
		$d = $this->collection($c,false)->remove($criteria, $one);
		return $d;
	}

	public function ensureIndex($args,$c='')
	{
        return $this->collection($c,false)->ensureIndex($args);
	}



}
