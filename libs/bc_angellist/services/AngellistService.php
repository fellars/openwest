<?php
namespace bc_angellist\services;

use _services\services\base\HttpService;
use tip\core\Util;

class AngellistService extends HttpService
{


    public function __construct(array $config = array()) {
        $defaults = array(
            'scheme'=>'https',
            'port'=>443,
            'host'=> 'api.angel.co/1/',
            'endpointMap' => array(),
        );
        parent::__construct($config + $defaults);
    }

    protected function _init() {
        parent::_init();
        $this->_config('category','company');
        $this->_config('description','Connects to Angellist API');
    }

    public function search($query,$type='Startup'){
        return $this->get('search',compact('query','type'));
    }

    public function company($id){
        return $this->get("startups/$id");
    }
    public function job($id){
        return $this->get("job/$id");
    }

    public function jobs($type,$ids,$getPage=1, $returnPerIdArray=true,$cutoffDate=false,$limit=false){
        if(!$type)$type = 'jobs';
        $jobs = array();
		foreach($ids = Util::toArray($ids) as $id)
        {
            $path = "";
            switch(strtolower($type)){
                case "jobs":
                case "job":
                    $path = $id ? "jobs" : "jobs/$id";
                    break;
                case "company":
                case "startup":
                case "startups":
                    $path = "startups/$id/jobs";
                    break;
                case "tag":
                case "tags":
                case "market":
                case "markettag":
                case "location":
                case "locationtag":
                    $path = "tags/$id/jobs";
                    break;

            }
//            echo "$id- $path<pre>"; print_r( $ids ); echo "</pre>";exit;
            $idJobs = array();
            $getAll = $getPage === true;
            $page = $getAll ? 1 : $getPage;
            $lastPage = $page+1;
            while($page < $lastPage ){
                $theseJobs = $this->get($path,compact('page'));
                $page++;
				if($error = Util::nvlA($theseJobs,'error')){
					$page = $lastPage;
					continue;
				}
                if($getAll){
                    $lastPage = Util::nvlA($theseJobs,'last_page');
					$actualJobs = Util::nvlA2A($theseJobs,'jobs');
                    $idJobs = array_merge($idJobs,$actualJobs);
					if($limit && count($idJobs) >= $limit){
						$page = $lastPage;
					}else if($cutoffDate){
						$lastIndex = count($actualJobs) - 1;
						$lastUpdate = Util::nvlA($actualJobs,"$lastIndex.updated_at");
						if($lastUpdate){
							$lastUpdate = strtotime($lastUpdate);
							if($lastUpdate < $cutoffDate){
								//no need to keep searching
								$page = $lastPage;
							}
						}
					}

                }else{
                    $idJobs = $theseJobs;
                }

            }
            if($getAll){
                $idJobs = array('total'=>count($idJobs),'page'=>'all','jobs'=>$idJobs);
            }
			$returnPerIdArray ? $jobs[$id] = $idJobs : $jobs = array_merge($jobs,$idJobs);

			if($error)continue;

        }
		return $jobs;
//        return count($jobs) == 1 ? array_pop($jobs) : $jobs;
    }

    public function send($type, $path = null, $data = array(), array $options = array())
    {
        $response = parent::send($type, $path, $data, $options);
		if(is_string($response))$response = json_decode($response,true);
		return $response;
    }


}
