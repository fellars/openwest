<?php
namespace bc\modules\hitch;

use \tip\controls\Module;
use tip\core\Util;
use bc\modules\hitch\base\BaseHitchModule;
use bc\models\Companies;
use bc\models\CompanyRepresentatives;
use bc\models\Opportunities;
use bc\models\UserMatches;
use tip\models\Users;
use bc\traits\user\TalentTrait;

class CronUpdateModule extends BaseHitchModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
       
    }
	public function needsUpdating($traitClass, $limit=50){
		$now = time();

		$update = $traitClass::masterFind($this,array(
			'$and'=>array(
				array('$or'=> array(
					array('nextUpdate'=>array('$exists'=>true)),
					array('nextUpdate'=>array('$lte'=>$now))
				)),
				array(
					array('found'=>true),
				)

			)
		),array(),'all',array(),array('limit'=>$limit)
		);
		return $update;
	}
    public function schedule($screen,$options=array()){
        $scheduler = \_core\apps\SchedulerApp::create();
        $conditions = \_core\traits\CoreSchedulerTaskTrait::conditions(array('app'=>$this->app()->name(true)));

        return $scheduler->tasks($screen,$conditions, array('allowCreate'=>false,'allowClone'=>false,'allowEdit'=>true,'allowDelete'=>false));
    }


    public function load($screen,$options=array()){
		$name = $this->requestData('_taskName');
		$maps = $this->setting("mappings");
		set_time_limit(500);
		if($this->is($name,'Daily Refresh')){
//			$this->_sendCompanyMatchEmails($maps);
//			$this->_sendCompanyUpdateEmails($maps);
			$this->_matchUsers($maps);
//			$this->_updateBlogFeed($maps);
//			$this->_updateMediaFeed($maps);
//			$this->_updateCrunchbase($maps);
//			$this->_updateTwitterFeed($maps);
//			$this->_updateGithubFeed($maps);
//			$this->_updateLocationJobs($maps,'angel');
			echo "<pre>"; print_r( "done" ); echo "</pre>";exit;
		}
	}

	protected function _updateLocationJobs($maps,$source=true){
		$locations = Util::nvlA($maps,'locations');
		$sourceJobs = array();
		foreach($locations as $key => $loc){
			$sourceJobs[$key] =$this->jobsIn($key,$maps,$source);
		}
		return $sourceJobs;
	}

	protected function _sendCompanyMatchEmails($maps){
		$db = $this->_db();
		$limit = 25;
		$now = time();


		$find = TalentTrait::conditions(array(
				'$or'=> array(
					array('nextEmail'=>array('$exists'=>false)),
					array('nextEmail'=>array('$lte'=>$now))
				),
				'nextEmail'=>array('$ne'=>-1)
			)
		);
		$users = $db->collection('users')->get($find,array(),compact('limit'));
		$users = \tip\models\Users::create($users);
		foreach($users as $user){
			$next = TalentTrait::nextEmailTime($user);
			$talentIndex = TalentTrait::tIndex($user);
			if($next !== -1){
				$match = \bc\traits\user_match\CoreTrait::masterFindFirst($this,array('user'=>$user->id(),'companyInterest'=>array('$exists'=>false), 'maxOppScore'=>array('$gte'=>50)),array(),array(),array('sort'=>array('traits.0.maxOppScore'=>-1)));
				if(!$match){
					//find ones where interest is -1 (meaning skipped)
					$match = \bc\traits\user_match\CoreTrait::masterFindFirst($this,array('user'=>$user->id(),'companyInterest'=>-1, 'maxOppScore'=>array('$gte'=>50)),array(),array(),array('sort'=>array('traits.0.maxOppScore'=>-1)));
				}
				if($match){
					$company = $match->company();
					$opp = $match->highestMatchingOpportunity();
					//count this as skipped so doesn't get picked up again
					//send email match
					$this->_sendMatchEmail($user,$company,$opp,$match);
					$match->tData('/companyInterest',-1);
					$match->save();

				}else{
					//no matches at this time
				}
			}
			$db->collection('users')->update(array('_id'=>$user->id(true)),array("traits.$talentIndex.nextEmail"=>$next));
		}
	}

	protected function _sendMatchEmail($user,$company,$opp,$match){
		$to = $user->tData('/email');
		$name = $user->name();
		$subject = "New BetaCave Match";
		$body = "Hey $name,<pre>". print_r( $company->data(), true ) . "</pre>";
		$body .= "<pre>". print_r( $opp->data(), true ) . "</pre>";
		$body .= "<pre>". print_r( $match->data(), true ) . "</pre>";
		$headers = 'From: matches@betacave.com' . "\r\n";
		$sent = mail($to,$subject,$body,$headers);
		//todo: use email service

	}
	protected function _sendCompanyUpdateEmails($maps){
		$db = $this->_db();
		$limit = 25;
		$companyLimit = 15;
		$now = time();


		$find = TalentTrait::conditions(array(
				'$or'=> array(
					array('nextUpdateEmail'=>array('$exists'=>false)),
					array('nextUpdateEmail'=>array('$lte'=>$now))
				),
				'nextUpdateEmail'=>array('$ne'=>-1)
			)
		);
		$users = $db->collection('users')->get($find,array(),compact('limit'));
		$users = \tip\models\Users::create($users);
		foreach($users as $user){
			$next = TalentTrait::nextUpdateEmailTime($user);
			$talentIndex = TalentTrait::tIndex($user);
			if($next !== -1){
				$matches = \bc\traits\user_match\CoreTrait::masterFind($this,array('user'=>$user->id(),'companyInterest'=>array('$gte'=>40)),array(),'all',array(),array('limit'=>$companyLimit,'order'=>array('traits.0.companyInterest'=>-1)));
				$companies = array();
				foreach($matches as $match){
					$company = $match->company();
					$companies[$company->id()] = $company;
				}
				$this->_sendUpdateEmail($user,$companies);

			}
			$db->collection('users')->update(array('_id'=>$user->id(true)),array("traits.$talentIndex.nextUpdateEmail"=>$next));
		}
	}
	protected function _sendUpdateEmail($user,$companies){
		$to = $user->tData('/email');
		$name = $user->name();
		$headers = 'From: updates@betacave.com' . "\r\n";
		if($companies){
			$subject = "Your BetaCave Update";
			$body = "Hey $name, Check Out your Companies\r\n";
			foreach($companies as $i => $company){
				$match = $company->entityVar('match');
				$body .= "Company #$i: " .$company->name() . "\r\n";
			}
			$sent = mail($to,$subject,$body,$headers);
			//todo: use email service

		}
	}


	protected function _matchUsers($maps){
		$db = $this->_db();
		$limit = 25;//userLimit
		$companyLimit = 100;
		$oppLimit = 200;
		$now = time();
		$next = $now + ( 60 * 60 * 24 * 1);//daily
		$find = TalentTrait::conditions(array(
				'$or'=> array(
					array('nextUpdate'=>array('$exists'=>false)),
					array('nextUpdate'=>array('$lte'=>$now))
				),
				'nextUpdate'=>array('$ne'=>-1)
			)

		);
		$users = $db->collection('users')->get($find,array(),compact('limit'));
		$users = \tip\models\Users::create($users);
		$finalDetails = array();

		foreach($users as $user){
			$talentIndex = TalentTrait::tIndex($user);
			$locs = TalentTrait::tData2A($user,'preferredLocations');
			$roles = TalentTrait::tData2A($user,'preferredRoles');
			$matched = UserMatches::matchedUserCompaniesAndOpportunities($user->id());
			$oppCount = 0;
			foreach(array('new','existing') as $companyMode){
				foreach($locs as $loc){
					if($oppCount < $oppLimit){
						$selector = ($companyMode == 'new') ? '$nin':'$in';
						$compConditions = array('_id'=>array($selector=>$matched[0]));
						$companies = \bc\traits\company\CoreTrait::masterFind($this,
							array('ready'=>true,'locations'=>$loc),
							array(),
							'all',
							$compConditions,
							array('fields'=>array("_id"),'limit'=>$companyLimit)
						);
						$companyIds = $companies->keys();
						$opps = \bc\traits\opportunity\CoreTrait::masterFind($this,
							array('company'=>array('$in'=>$companyIds),'roles'=>array('$in'=>$roles)),
							array(),'all',array('_id'=>array('$nin'=>$matched[1])));
						foreach($opps as $opp){
							if(\bc\traits\opportunity\CorporateTrait::ifHasData($opp,'active') !== false){//if doesn't have trait then will match as well
								$this->scoreJob($user,$opp,$maps,$details);
								$finalDetails[] = $details;
								$oppCount++;
							}
						}
					}
				}
			}

			$db->collection('users')->update(array('_id'=>$user->id(true)),array("traits.$talentIndex.nextUpdate"=>$next));
		}

	}

	protected function _updateAngelList($maps){
		$now = time();
		$next = $now + (60 * 60 * 24 * 30 * 3);//every 3 months
		$companies = $this->needsUpdating(\bc\traits\company\AngelListTrait::class_path());
		$al = $this->loadService('angellist');
		$companyRanker = new \bc\hitch\angel\AngelCompanyRanker(array('app' => $this, 'mappings' => $maps));
		$allUnmatchedTags = Util::toArray($this->setting('mappings.unmatched.angel'));
		foreach($companies as $company){
			$id = \bc\traits\company\AngelListTrait::tData($company,'id');
			if($id){
				$compData = $al->company($id);
				$companySum = $companyRanker->summarize($company,$compData,$allUnmatchedTags);

				\bc\traits\company\CoreTrait::tData($company, $companySum, null, false);
				\bc\traits\company\AngelListTrait::tData($company, $compData, null, false);
			}
			\bc\traits\company\AngelListTrait::tData($company,'nextUpdate',$next,true);
		}
		$this->setting('mappings.unmatched.angel',$allUnmatchedTags,true);
	}
	protected function _updateCrunchbase($maps){
		$now = time();

		$next = $now + (60 * 60 * 24 * 30 * 3);//every 3 months
		$companies = $this->needsUpdating(\bc\traits\company\CrunchbaseTrait::class_path());
		$cb = $this->loadService('crunchbase');
		$companyRanker = new \bc\hitch\crunchbase\CrunchbaseCompanyRanker(array('app' => $this, 'mappings' => $maps));
		$allUnmatchedTags = Util::toArray($this->setting('mappings.unmatched.crunchbase'));
		foreach($companies as $company){
			$id = \bc\traits\company\CrunchbaseTrait::tData($company,'permalink');
			if($id){
				$compData = $cb->company($id);
				$companySum = $companyRanker->summarize($company,$compData,$allUnmatchedTags);
				\bc\traits\company\CoreTrait::tData($company, $companySum, null, false);
				\bc\traits\company\CrunchbaseTrait::tData($company, $compData, null, false);
			}
			\bc\traits\company\CrunchbaseTrait::tData($company,'nextUpdate',$next,true);
		}
		$this->setting('mappings.unmatched.crunchbase',$allUnmatchedTags,true);
	}

	protected function _updateTwitterFeed(){

		$now = time();
		$next = $now + (60 * 60 * 24 * 1);//every day
		$allTwitter = $this->needsUpdating(\bc\traits\company\TwitterFeedTrait::class_path());

		$oauth = $this->loadService('twitter');
		$service = \_services\services\TwitterService::create();
		$service->initConsumerByEndpointId($oauth->endpoint());
		foreach($allTwitter as $company){

			$handles = Util::toArray(\bc\traits\company\TwitterFeedTrait::tData($company,'handles'));
			foreach($handles as $handle){
				$twitterId = \bc\traits\company\TwitterFeedTrait::tData($company,"users.$handle.twitterId");
				if(!$twitterId){
					$info = Util::toArray($service->get('/users/show',array('screen_name'=>$handle,'include_entities'=>true)));
					$twitterId = Util::nvlA($info,'id');
					unset($info['id']);
					$info['twitterId'] = $twitterId;
					if(!isset($info['errors'])){
						\bc\traits\company\TwitterFeedTrait::tData($company,"users.$handle",$info,false);
					}

				}

				//get latest
				if($twitterId){
					$latest = Util::toArray($service->get('statuses/user_timeline',array(
						'user_id'=>$twitterId,
						'include_rts'=>true,
						'trim_user'=>false,
						'include_entities'=>true,
						'count'=>50,//how many to receive?
					)));
					$clean = array();
					foreach($latest as $tweet){
						$clean[] = array(
							'id' => $tweet->id,
							'created_at' => $tweet->created_at,
							'text'=>$tweet->text,
							'source'=>$tweet->source,
							'user'=>array(
								'id' => $tweet->user->id,
								'name'=> $tweet->user->name,
								'screen_name' => $tweet->user->screen_name,
							),
							'retweet_count' => $tweet->retweet_count,
							'entities' => $tweet->entities,
						);
					}
					\bc\traits\company\TwitterFeedTrait::tData($company,"feeds.$handle",$clean,false);

				}
				\bc\traits\company\TwitterFeedTrait::tData($company,'nextUpdate',$next,true);
			}
//			echo "<pre>"; print_r( $company->data() ); echo "</pre>";exit;
		}
	}




	protected function _updateMediaFeed(){
		$noFeed = Companies::masterFind($this,'all',array('conditions'=>\bc\traits\company\MediaFeedTrait::withoutCondition()));
		foreach($noFeed as $company){
			$mediaFeed = \bc\traits\company\MediaFeedTrait::create($company);
			$company->save();
		}
		$next = time() + (60 * 60 * 24 * 7);//every week
		$companies = $this->needsUpdating(\bc\traits\company\MediaFeedTrait::class_path());
		foreach($companies as $company){
			$existing = \bc\traits\company\MediaFeedTrait::tData2A($company,'media');

			if(\bc\traits\company\CrunchbaseTrait::hasTrait($company)){
				$pickBiggest = function($image){
					$sizes = Util::nvlA2A($image,'available_sizes');
					$attribution = Util::nvlA($image,'attribution','');
					$maxWidth = 0;
					$useIndex= 0;
					foreach($sizes as $index => $size){
						if(($thisWidth = Util::nvlA($size,'0.0')) > $maxWidth){
							$maxWidth = $thisWidth;
							$useIndex= $index;
						}
					}
					$width = Util::nvlA($sizes,"$useIndex.0.0");
					$height = Util::nvlA($sizes,"$useIndex.0.1");
					$url = "http://www.crunchbase.com/".Util::nvlA($sizes,"$useIndex.1");
					return array($url,compact('width','height','attribution'));
				};
				$image = \bc\traits\company\CrunchbaseTrait::tData2A($company,'image');
				if($image){
					list($url,$data) = $pickBiggest($image);
					$this->_updateMediaAsset($existing,'crunchbase','logo',$url,$data);
				}

				$videos = \bc\traits\company\CrunchbaseTrait::tData2A($company,'video_embeds');
				foreach($videos as $video){
					$data = array('description'=>Util::nvlA($video,'description'));
					$embed = Util::nvlA($video,'embed_code');
					$this->_updateMediaAsset($existing,'crunchbase','video',$embed,$data);

				}
				$screenshots = \bc\traits\company\CrunchbaseTrait::tData2A($company,'screenshots');
				foreach($screenshots as $screenshot){
					list($url,$data) = $pickBiggest($screenshot);
					$this->_updateMediaAsset($existing,'crunchbase','screenshot',$url,$data);
				}

			}
			if(\bc\traits\company\AngelListTrait::hasTrait($company)){
				$logo = \bc\traits\company\AngelListTrait::tData($company,'logo_url');
				$thumb = \bc\traits\company\AngelListTrait::tData($company,'thumb_url');
				if($logo){
					$this->_updateMediaAsset($existing,'angel','logo',$logo,array('thumb'=>$thumb));
				}
				$video = \bc\traits\company\AngelListTrait::tData($company,'video_url');
				if($video){
					$this->_updateMediaAsset($existing,'angel','video',$video,array());
				}
				$screenshots = \bc\traits\company\AngelListTrait::tData($company,'screenshots');
				foreach($screenshots as $screenshot){
					$this->_updateMediaAsset($existing,'angel','screenshot',Util::nvlA($screenshot,'original'),array('thumb'=>Util::nvlA($screenshot,'thumb')));
				}
			}
			if(\bc\traits\company\TwitterFeedTrait::hasTrait($company)){
				$users = \bc\traits\company\TwitterFeedTrait::tData2A($company,'users');
				foreach($users as $users){
					$profileImage = Util::nvlA($users,'profile_image_url');
					if($profileImage){
						$this->_updateMediaAsset($existing,'twitter','logo',$profileImage);
					}
				}
				$feeds = \bc\traits\company\TwitterFeedTrait::tData2A($company,'feeds');
				foreach($feeds as $user => $feed){
					foreach($feed as $tweet){
						$medias = Util::nvlA2A($tweet,'entities.media');
						foreach($medias as $media){
							$type = Util::nvlA($media,'type','photo');
							if($type == 'photo')$type = 'image';
							$url = Util::nvlA($media,'url');
							$this->_updateMediaAsset($existing,'twitter',$type,$url,$media);
						}
					}
				}
			}
			\bc\traits\company\MediaFeedTrait::tData2A($company,'media',$existing);

			\bc\traits\company\MediaFeedTrait::tData($company,'nextUpdate',$next,true);
		}
	}

	protected function _updateMediaAsset(&$existing,$source,$type,$url,$data=array()){
		$found = false;
		$added = $updated = time();
		foreach($existing as $index => $media){
			if( Util::nvlA($media,'source') === $source &&
					Util::nvlA($media,'type') === $type &&
					Util::nvlA($media,'url') === $url ){
				$found = true;
				$existing[$index] = compact('source','type','url','updated')+$data;
				break;
			}
		}
		if(!$found){
			$existing[] = compact('source','type','url','added','updated')+$data;
		}
		return $existing;
	}

	protected function _updateBlogFeed($maps){

		$next = time() + (60 * 60 * 24 * 7);//every week
		$companies = $this->needsUpdating(\bc\traits\company\BlogFeedTrait::class_path());
		$rss  = \_services\services\RssService::create();
		foreach($companies as $company){
			$blogs = \bc\traits\company\BlogFeedTrait::tData2A($company,'blogs');
			$feeds = array();
			$valid = false;
			foreach($blogs as $blog){

				//pull in latest
				$rss->feedUrl($blog);
				$validFeed = $rss->init();
				$link = $rss->subscribe_url();
				if(!$validFeed){
					//try adding /feed to end of it
					$try = $blog . (strrpos($blog,'/') !== (strlen($blog)-1) ? '/' : '') . 'feed/';
					$rss->feedUrl($try);
					$validFeed = $rss->init();
					$link = $rss->subscribe_url();
				}

				$title = $rss->get_title();
				$count = $rss->get_item_quantity();
				if($validFeed){
					$valid = true;
					$blogFeeds = array();
					$blogFeeds['title'] = $title;
					$blogFeeds['permalink'] = $blog;
					$blogFeeds['feed'] = $link;
					$blogFeeds['description'] = $rss->get_description();
					$items = array();
					foreach ($rss->get_items() as $item){
						$items[] = array(
							'permalink'=>$item->get_permalink(),
							'title'=>$item->get_title(),
							'description'=>$item->get_description(),
							'date'=>$item->get_date('j F Y | g:i a'),
							'author'=>($author = $item->get_author()) ? $author->get_name() : '',
						);
					}
					$blogFeeds['items'] = $items;
					$feeds[] = $blogFeeds;
				}
//				echo $company->name()." - $blog - $valid<pre>"; print_r( compact('title','link','count','feeds')); echo "</pre>";exit;
			}
			if(!$valid){
				\bc\traits\company\BlogFeedTrait::tData2A($company,'blogs',array());
				\bc\traits\company\BlogFeedTrait::tData2A($company,'found',false);
				\bc\traits\company\BlogFeedTrait::tData2A($company,'nextCheck',0,true);
			}
			\bc\traits\company\BlogFeedTrait::tData($company,'feeds',$feeds);
			\bc\traits\company\BlogFeedTrait::tData($company,'nextUpdate',$next,true);
		}
	}
	protected function _updateNewsFeed($maps){
		$noFeed = Companies::masterFind($this,'all',array('conditions'=>\bc\traits\company\NewsFeedTrait::withoutCondition()));
		foreach($noFeed as $company){
			$news = "";
			if(\bc\traits\company\CrunchbaseTrait::hasTrait($company)){
				$news = \bc\traits\company\CrunchbaseTrait::tData($company,'twitter_username');
			}
			if(!$news && \bc\traits\company\AngelListTrait::hasTrait($company)){
				$news = \bc\traits\company\AngelListTrait::tData($company,'twitter_url');

			}
			if($news){
				$feed = \bc\traits\company\NewsFeedTrait::create();
				$feed->data('lastUpdate',0);
				$feed->data('url',$news);
				$company->addTrait($feed,true);
			}
		}
		$now = time();
		$companies = $this->needsUpdating(\bc\traits\company\NewsFeedTrait::class_path());
		$rss  = $this->loadService('rss');
		foreach($companies as $company){
			$url = \bc\traits\company\NewsFeedTrait::tData($companies,'url');
			if($url){
				//get latest
				$latest = $rss->get($url);
				\bc\traits\company\NewsFeedTrait::tData($companies,'feed',$latest,false);
			}
			\bc\traits\company\NewsFeedTrait::tData($companies,'lastUpdate',$now,true);
		}
	}
	protected function _updateGlassdoor($maps){
		$now = time();
		$companies = $this->needsUpdating(\bc\traits\company\GlassdoorFeedTrait::class_path());
		$rss  = $this->loadService('rss');
		foreach($companies as $company){
			$url = \bc\traits\company\GlassdoorFeedTrait::tData($companies,'url');
			if($url){//get latest
				$latest = $rss->get($url);
				\bc\traits\company\GlassdoorFeedTrait::tData($companies,'feed',$latest,false);
			}
			\bc\traits\company\GlassdoorFeedTrait::tData($companies,'lastUpdate',$now,true);
		}
	}
	protected function _updateGithubFeed($maps){
		$now = time();
		$next = $now + (60 * 60 * 24 * 7);//every week
		$companies = $this->needsUpdating(\bc\traits\company\GithubFeedTrait::class_path());
		$github  = $this->appConfig('github');
		static::addLib('tip_github');
		$github = \tip_github\services\GithubService::byConsumerEndpoint($github);

		foreach($companies as $company){
			$username = \bc\traits\company\GithubFeedTrait::tData($company,'login');
			$type = \bc\traits\company\GithubFeedTrait::tData($company,'type');
			$id  = \bc\traits\company\GithubFeedTrait::tData($company,'id');
			$isOrg = $type === 'Organization';
			if($username){//get repositories
				$repos = $github->api($isOrg?'organization':'user')->repositories($username);
				$repos = $this->_githubCleanRepos($repos);
				\bc\traits\company\GithubFeedTrait::tData($company,'repositories',$repos,false);
				$activity = $github->getHttpClient()->get(($isOrg ? "orgs" : "users"). "/$username/events")->getContent();
				\bc\traits\company\GithubFeedTrait::tData($company,'activity',$activity,false);
			}
			\bc\traits\company\GithubFeedTrait::tData($company,'nextUpdate',$next,true);
		}
	}

	protected function _githubCleanRepos($repos){
		$new = array();
		foreach($repos as $repo){
			unset(
				$repo['owner']['followers_url'],$repo['owner']['following_url'],
				$repo['owner']['gists_url'],$repo['owner']['starred_url'],
				$repo['owner']['subscriptions_url'],$repo['owner']['organizations_url'],
				$repo['owner']['repos_url'],$repo['owner']['events_url'],
				$repo['owner']['received_events_url']
			);
			$list = array('forks','keys','collaborators','teams','hooks','issue_events',
				'events','assignees','branches','tags','blobs','git_tags','git_refs',
				'trees','statuses','languages','stargazers','contributors','subscribers',
				'subscription','commits','git_commits','comments','issue_comment','contents',
				'compare','merges','archive','downloads','issues','pulls','milestones',
				'notifications','labels','clone','git','ssh','svn',);
			foreach($list as $url){
				unset($repo["$url"."_url"]);
			}

			$new[] = $repo;
		}
		return $new;
	}

}


?>