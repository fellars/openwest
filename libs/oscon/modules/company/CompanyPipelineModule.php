<?php
namespace bc\modules\company;

use bc\apps\MasterApp;
use bc\hitch\CompanyRanker;
use bc\models\Conversations;
use bc\models\Opportunities;
use bc\models\UserMatches;
use bc\modules\company\base\BaseCompanyModule;
use bc\traits\user\TalentTrait;
use tip\core\Util;
use tip\models\Endpoints;
use tip\models\Users;
use tip\screens\elements\Alert;
use tip\screens\elements\Block;
use tip\screens\elements\Menu;
use tip\screens\elements\Raw;
use tip\screens\helpers\base\HtmlTags;
use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\elements\Table;
use tip\models\Accounts;
use tip\traits\account\MasterTrait;
use lithium\net\http\Media;
class CompanyPipelineModule extends BaseCompanyModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

    public function load($screen,$settings=array(),$render=false){
		$screen->screenHb('bc', '/bc/hb/bc.hb');
        $grid = $screen->blockGrid();
		$render = Util::nvlA($settings,'render',$render);
		$me = \tip\models\Accounts::current();
		$id = Util::nvlA($settings,'id');
		$this->screenAction('pipeline:view',true);
		$grid->title($me->name() . ' Pipeline');
		$db = $this->_db();

		$listings = Opportunities::byCorporateId($me->id(),$db,true);
		$listings = $listings->data();
		$listings = array("general"=>array('name'=>'General Inquiries')) + $listings;
		$form = Form::create('listing',$grid,array('formLayout'=>'inline','submitButton'=>false));
		FormField::select('listing',$form,false,$id,$listings,'',false,false,array(
			'submitOnChange'=>true,
			'optionsConfig'=>array(
				'emptyFirst'=>true,
				'firstLabel'=>'All',
				'firstValue'=>'all',
				'label'=>'name','value'=>'$key'
			)
		));

		$this->pipeline_view($screen,$id,false);

		return $render ? $grid->render() : $grid;
    }

	public function pipeline_view($screen,$id,$render=true){
		$grid = $screen->blockGrid();
		$db = $this->_db();
		$screen->data('id',$id);
		$block = Block::create('pipeline',$grid);
		$block->box(false);
		$block->loadUrl('pipeline:menu');
		$block->border(false);
		$block->title("<br/>");
		if($id){
			$this->screenAction('pipeline:menu',true);
			$menu = Menu::create('menu',$block);
			$menu->navType('pills');
			$menu->createNav('followers');
			$menu->createNav('conversations');
			$this->pipeline_menu($screen,'followers',false);
		}
		return $render ? $grid->render(array('_url_'=>"/bc/home/company_pipeline/$id")) : $grid;
	}

	public function form_listing($form,$data){
		$id = $data['listing'];
		return $this->pipeline_view($form->screen(),$id);
	}

	public function pipeline_menu($screen,$menu,$render=true,$userId=false){
		$grid = $screen->blockGrid();
		$block = $grid->element('pipeline');
		$block->menu()->setActive($menu,'name');
		$id = $screen->data('id');
		$tableBlock = Block::create($menu,$block);
		if($id){
			$all = $id === 'all';
			$general = $id === 'general';
			$db = $this->_db();
			$accountId = Accounts::currentId();
			if($all){
				$opps = Opportunities::byCorporateId($accountId,$db,true);
				$block->title('All');
			}else if ($general){
				$opps = Opportunities::byCorporateId($accountId,$db,true);
				$block->title('General Inquiries');
			}else if($id){
				$opps = Opportunities::byIdRemote($db,$id);
				if($opps){
					$opps = array($opps);
				}else{
					$opps = array();
				}

			}else{
				$opps = array();
			}
			$this->screenAction('follower:profile',true);
			$this->screenAction('follower:conversations',true);
			$self = $this;
			foreach($opps as $opp){
				$oppId = $opp->id();
				$table = Table::create("listing_".$opp->id(),$tableBlock);
				$table->data('tableHeader',HtmlTags::tag('h2',$opp->name()));
				$matches = UserMatches::matchedOpportunityUsers($oppId,$db,array('$or'=>array(
					array("opportunities.$oppId.share"=>true),
					array("opportunities.$oppId.interest"=>array('$gte'=>45)),
				)));
				$users = array();
				foreach($matches as $match){
					$users[$match->tData('/user')] = Util::isTruthy($match->tData("/opportunities.$oppId.share"));
				}
				$matchedUsers = Users::masterFind($this,'all',array('conditions'=>array('_id'=>array('$in'=>Users::toMongoId(array_keys($users))))));
				switch($menu){
					case 'followers':
						$table->addHeaders('talent,profile,actions');
						$followers = $matchedUsers;

						//for testing
						$followers = array(Users::current());
						$users[Users::currentId()] = true;
						//for testing

						$table->addRows($followers,array(
							'talent'=>function($val,$key,$row) use($users){
								return $users[$row->id()] ? $row->name() : "--anonymous--";
							},
							'profile'=>function($val,$key,$row) use($users,$self){
								$profile = "";
								if($users[$row->id()]){
									$profile = $self->userProfile($row);

								}
								return $profile;
							},
							'actions'=>function($val,$key,$row) use($oppId){
								return HtmlTags::buttonGroup(array(
									HtmlTags::modalButton('View Profile','follower:profile/'.$row->id()."/$oppId"),
									HtmlTags::refreshButton('View Conversations','follower:conversations/'.$row->id()."/$oppId"),
								));
							},

						));
						break;
					case 'conversations':
						if($userId){
							$user = Users::masterById($this,$userId);
							$table->data('tableHeader',$user->name());
							$table->addHeaders('topic,actions');
						}else{
							$table->addHeaders('talent,topic,actions');
						}
						$conversations = \bc\traits\conversation\CoreTrait::masterFind($this,array('corporate'=>$accountId,'opportunity'=>$oppId));
						$this->screenAction('conversation:read',true);
						$table->addRows($conversations,array(
							'talent'=>function($val,$key,$row) use($self,$matchedUsers,$users){
								$userId = $row->tData('/user');
								$isPublic = false;
								if(isset($matchedUsers[$userId])){
									$user = $matchedUsers[$userId];
									$isPublic = isset($users[$userId]) && $users[$userId];
								}
								//see if sharing
								return $isPublic ? $user->name() : '--anonymous--';
							},
							'topic'=>function($val,$key,$row){
								return $row->tData('/topic');
							},
							'actions'=>function($val,$key,$row) use($matchedUsers){
								$userId = $row->tData('/user');
								$isPublic = false;
								if(isset($matchedUsers[$userId])){
									$user = $matchedUsers[$userId];
									$isPublic = isset($users[$userId]) && $users[$userId];
								}
								return HtmlTags::buttonGroup(array(
									HtmlTags::modalButton('Read/Reply','conversation:read/'.$row->id()."/$isPublic"),

								));
							},

						));
						break;
				}

			}
		}
		return $render ? $block->render() : $block;
	}

	public function follower_profile($screen,$userId){
		$user = Users::masterById($this,$userId);
		$profile = $this->userProfile($user,true);
		$raw = Raw::create('profile',null,$profile);

		return $raw->render(array('title'=>$user->name(),'fullSize'=>true));
	}
	public function follower_conversations($screen,$userId){
		return $this->pipeline_menu($screen,'conversations',true,$userId);
	}

	public function userProfile($user,$full=false){
		$profile = "";
		$profile .= "Roles: ". implode(", ",TalentTrait::ifHasData2A($user,'preferredRoles')) . "<br/>";
		$profile .= "Locations: ". implode(", ",TalentTrait::ifHasData2A($user,'preferredLocations')) . "<br/>";
		$profile .= "Positions: ". implode(", ",TalentTrait::ifHasData2A($user,'preferredPosition')) . "<br/>";
		$profile .= "Markets: ". implode(", ",TalentTrait::ifHasData2A($user,'preferredMarkets')) . "<br/>";
		$sizeStart = TalentTrait::ifHasData($user,"preferredCompanySize.0");
		$sizeEnd = TalentTrait::ifHasData($user,"preferredCompanySize.1");
		if($sizeEnd){
			$profile .= "Company Size (num employees): ". CompanyRanker::convertToNumEmployees($sizeStart) . " - " . CompanyRanker::convertToNumEmployees($sizeEnd) . "<br/>";
		}
		if($full){

		}
		return $profile;
	}

	public function conversation_read($screen,$cId, $isPublic=false){
		$conversation = Conversations::masterById($this,$cId);
		$block = Block::create('conversation',$screen);
		$block->box(false);
		$thread = $conversation->tData2A('/thread');
		$data = array();
		$user = Users::masterById($this,$conversation->tData('/user'));
		$company = Accounts::current();
		foreach($thread as $item){
			$who = Util::nvlA($item,'who');
			if($who == 'user'){
				if($isPublic){
					$icon = $user->tData('/avatar');
					$name = $user->name();
				}else{
					$icon = "?";
					$name = 'Anonymous';
				}
			}else{
				$icon = $company->tData('/avatar');
				$name = $company->name();
			}
			$msg = Util::nvlA($item,'msg');
			$time = Util::nvlA($item,'time');
			$time = Util::timeAgo($time);
			$data[] = compact('who','noicon','name','time','msg');
		}
		$raw = Raw::create('thread',$block,array('template'=>'bc-thread'));
		$raw->data('thread',$data);
		$form = Form::create("message",$block,array('template'=>'bc-thread-message'));
		FormField::textarea('message',$form);
		FormField::hidden('cId',$form,$cId);
		FormField::hidden('p',$form,$isPublic);
		$block->render(array('title'=>$conversation->tData('/topic'),'buttons'=>false,'fullSize'=>true));

	}

	public function form_message($form,$data){
		$msg = $data['message'];
		$id = $data['cId'];
		$p = $data['p'];
		$c = Conversations::masterById($this,$id);
		$who = 'admin';
		$time = time();
		$thread = $c->tData2A('/thread');
		$new = compact('who','msg','time');
		array_unshift($thread,$new);
		$c->tData2A('/thread',$thread);
		$this->_runAsMasterAccount(function() use($c){
			$c->save();//need to run as master since conversation is created from user account
		});

		//notify user of new msg
		$master = MasterTrait::masterFindFirst($this);
		$email = MasterTrait::tData($master,'emailService');
		$email = $this->_runAsMasterAccount(function() use($email){
			return Endpoints::serviceById($email);
		});
		$user = Users::masterById($this,$c->tData('/user'));
		$subject = "[BetaCave] *new response* " . $c->tData('/topic');
		$account =  Accounts::current();
		$accntId = $account->id();

		$accntName = $account->name();
		$accntNameUnder = Util::inflect($accntName,'u');
		$companyId = $account->tData('/company');
		$url = \lithium\net\http\Router::match("bc/home/company/$accntNameUnder/$companyId/engage/$id",$this->_request());
		$msg = $accntName . " has replied to your thread:\r\n\r\n$msg\r\n\r\n<a href='$url'>Click here to respond.</a>";
		$email->sendMail($user->tData('/email'), $subject, $msg);
		$block = $this->pipeline_menu($form->screen(),'conversations',false,false);
		Alert::create('success',$block,'We have notified the user of your response.  We will notify you once they reply.');
		return $block->render();
	}

}
