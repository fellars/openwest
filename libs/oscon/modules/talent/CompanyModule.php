<?php
namespace bc\modules\talent;

use bc\models\Conversations;
use lithium\net\http\Media;
use tip\controls\Module;
use tip\core\Util;
use tip\models\Endpoints;
use tip\screens\elements\Raw;
use tip\screens\helpers\base\HtmlTags;
use bc\traits\user\TalentTrait;
use tip\models\Users;
use tip\models\Accounts;
use bc\models\Companies;
use bc\models\UserMatches;
use bc\models\Opportunities;
use tip\screens\elements\Alert;
use tip\screens\elements\FormField;
use tip\screens\elements\Form;
use tip\screens\elements\Block;
use tip\screens\elements\BlockGrid;
use tip\screens\elements\TableBlock;
use bc\modules\talent\base\BaseTalentModule;
use tip\traits\account\MasterTrait;

class CompanyModule extends BaseTalentModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

    public function load($screen,$config=array()){
        $grid = $screen->blockGrid();
		$screen->screenHb('bc', '/bc/hb/bc.hb');
		$id = Util::nvlA($config,'id');
		$company = $this->_loadCompany($screen,$id);

		if($company){
			$this->screenAction('like:opportunity',true);
			$this->screenAction('like:company',true);
		}
	}

	public function menu_view($screen,$menu){
		$top = $screen->blockGrid()->element('top');
		$user = Users::current();
		$me = $user->id();
		$company = $screen->data('company');
		$db = $this->_db();
		$scoreCutoff = 45;
		if($menu === 'opportunities'){
			$oppBlock = Block::create('opportunities',$top);
			$table = TableBlock::create('opps',$oppBlock);
			$find = \bc\traits\user_match\CoreTrait::conditions(array('user'=>$me,'company'=>$company));
			$match = $db->collection('user_matches')->get($find,array(),array('limit'=>1));
			$match = UserMatches::create($match,array('set'=>true));
			$oppIds = array();
			$formList = array();
			$matches = $match->tData2A('/opportunities');

			foreach($matches as $matchOppId => $m){
				$score = Util::nvlA($m,'score');
				if($score >= $scoreCutoff){
					$oppIds[$matchOppId] = $score;
					$formList[] = "rank_$matchOppId";
				}
			}

			$opps = Opportunities::byIdRemote($db,array_keys($oppIds),false,true);
			$companyInterest = $match->tData('/companyInterest');
			if($formList)$table->upperButtons( HtmlTags::submitFormButton('Save Rankings',implode(',',$formList),array('company'=>$company, 'companyInterest'=>$companyInterest)));
			$table->addHeaders(array('job'=>array('width'=>'50%'),'score'=>array('width'=>'5%'),'rank'=>array('render'=>true)));
			$table->addRows($opps,array(
				'job'=>function($val,$key,$row) use($oppIds){
					$info = HtmlTags::tag('b',$row->name());
					$json = print_r( $row->data(), true );
					$info .= HtmlTags::tag('pre',$json );
					return $info;
				},
				'score'=>function($val,$key,$row) use($oppIds){
					$info = HtmlTags::tag('b',Util::nvlA($oppIds,$row->id()));
					return $info;
				},
				'rank'=>function($val,$key,$row) use($table,$matches){
					$oppId = $row->id();
					$rank = Form::create("rank_$oppId",$table->table(),array('formCallbackName'=>'oppRank','submitButton'=>false,'formLayout'=>'inline'));
					$rank->data('fieldSetSize',12);

					$slider = FormField::slider("interest_$oppId",$rank,false);
					$slider->data('requireSelection',true);
					$slider->data('selectText','<h3>Rank Opportunity</h3>');
					$slider->data('allowCancel',true);
					$slider->data('range',false);
					$slider->data('size',12);
					$slider->data('labels',array('Hate It','Not Interested','Maybe','Kinda','Totally'));
					$val = $slider->value(Util::nvlA2A($matches,"$oppId.interest"));
					FormField::hidden("startingInterest_$oppId",$rank,$val);
					return $rank->renderQuick();
				}
			));
			return $oppBlock->render();
		}
		else if ($menu === 'engage'){
			$company = Companies::byIdRemote($db,$company);

			return $this->_engage($top,$company,true);
		}
	}

	public function form_oppRank($form,$data){
		$forms = Util::nvlA2A($data,'form');
		$company = $data['company'];
		$companyInterest = $data['companyInterest'];
		$block = $form->screen()->blockGrid()->element('top.opportunities');

		$tableBody = $block->element('opps')->body();
		foreach($forms as $index=>$formName){
			list($ignore,$id) = explode('_',$formName);
			$interest = Util::nvlA($data,"interest_$id.0");
			$startingInterest = Util::nvlA($data,"startingInterest_$id");
			if($interest !== null){


				$this->_saveOpportunityInterest($company,$id,$companyInterest,$interest);
				$rankings['opportunities'][$id]['interest'] = $interest;
				$tableBody[$index]['rank'] = $form->renderQuick();//need to recreate to get right value
			}
		}

		$block->element('opps')->body($tableBody);
		Alert::create('success',$block,'Rankings Saved');
		return $block->render();
	}

	protected function _loadCompany($screen,$id,$build=true){
		$main = $screen->blockGrid();
		if($build){
			$top = \tip\screens\elements\Block::create('top',$main,array('box'=>false,'loadUrl'=>'menu:view'));
			$this->screenAction('menu:view',true);
			$menu = \tip\screens\elements\Menu::create('menu',$top);
			$menu->createNav('company');
			$menu->createNav('opportunities');
			$menu->createNav('engage');
			$companyBlock = \tip\screens\elements\Block::create('company',$top);
		}else{
			$top = $main->element('top');
			$companyBlock = $main->element('top.company');
			$companyBlock->removeAllElements();
		}

		$rank = Form::create('companyRank',$companyBlock,array('submitButton'=>false));

		$grid = BlockGrid::create('grid',$companyBlock);
		$grid->layout3Col();


		$company = $match = false;
		$db = $this->_db();
		$user = \tip\models\Users::current();
		$me = $user->id();

		if(!$id || $id == 'next'){
		   $find = \bc\traits\user_match\CoreTrait::conditions(array('user'=>$me,'maxOppScore'=>array('$gte'=>50),'companyInterest'=>array('$exists'=>false)));
		   $match = $db->collection('user_matches')->get($find,array(),array('limit'=>1,'sort'=>array('traits.0.maxOppScore'=>-1)));
			if(!$match){
				//find ones where interest is -1 (meaning skipped)
				$find = \bc\traits\user_match\CoreTrait::conditions(array('user'=>$me,'maxOppScore'=>array('$gte'=>50),'companyInterest'=>-1));
 			    $match = $db->collection('user_matches')->get($find,array(),array('limit'=>1,'sort'=>array('traits.0.maxOppScore'=>-1)));
			}
			if($match){
			   $match = UserMatches::create($match);
			   $company = Companies::byIdRemote($db,$match->tData('/company'),false);
	 	    }else{

			}
		}else{
		   $company = Companies::byIdRemote($db,$id,false);
			$find = \bc\traits\user_match\CoreTrait::conditions(array('user'=>$me,'company'=>$id));
		    $match = $db->collection('user_matches')->get1($find);
			$match = UserMatches::create($match);
		}
		$grid->removeAllElements();
		$grid->buttons("");
		if($company){
			$id = $company->id();
			$screen->data('company',$id,'render');
			$main->title($company->name());
			$interest = $match->tData('/companyInterest');
			$share = Util::isTruthy($match->tData('/share'));
			FormField::hidden('id',$rank,$id);
			FormField::hidden('startingInterest',$rank,$interest);
			FormField::hidden('company',$rank,$company->name());
			$slider = FormField::slider('rank',$rank,false);
			$slider->data('requireSelection',true);
			$slider->data('selectText','<h3>Rank Company</h3>');
			$slider->data('allowCancel',true);
			$slider->data('range',false);
			$slider->data('labels',array('Hate It','Not Interested','Maybe','Kinda','Totally'));
			$slider->value(Util::toArray($interest));
			FormField::radio('share',$rank,'Share Your Info?', $share ? 'yes':'no','yes,no','If you choose to share, the company will be able to contact you');
			FormField::raw('score',$rank,false,HtmlTags::tag('b','Your Interest: ' . $interest."&nbsp;&nbsp;Company Score: " . $match->tData('/companyScore')."&nbsp;&nbsp;"));

			$buttonHtml =
				HtmlTags::buttonGroup( array(
					HtmlTags::submitFormButton('Skip','companyRank',array('skip'=>true)),
					HtmlTags::submitFormButton('Save','companyRank'),
					HtmlTags::submitFormButton('Save & Next','companyRank',array('next'=>true)),
				));
			FormField::raw('buttons',$rank,false,$buttonHtml);

			$func = function($class,$title,$comp=null) use($company,$grid){
				$comp = $comp ?: $company;
				if($class::hasTrait($comp) && $class::ifHasData($comp,'found') !== false){
					$u = Util::inflect($title,'u');
					$item = \tip\screens\elements\Block::create($u,$grid);
					$item->title($title);
					$data = $class::tData($comp);
					\tip\screens\elements\Raw::create('data',$item,HtmlTags::tag('pre',print_r($data,true)));
				}

			};
			$func(\bc\traits\company\CoreTrait::class_path(),'Core Data');
			$corporate = \bc\traits\company\CoreTrait::tData($company,'corporateAccount');
			if($corporate){
				$corporate = Accounts::masterById($this,$corporate);
				$func(\bc\traits\account\CorporateTrait::class_path(),'Corporate Data',$corporate);
			}

			$func(\bc\traits\company\CrunchbaseTrait::class_path(),'Crunchbase');
			$func(\bc\traits\company\AngelListTrait::class_path(),'Angel List');
			$func(\bc\traits\company\MediaFeedTrait::class_path(),'Media');
			$func(\bc\traits\company\TwitterFeedTrait::class_path(),'Twitter');
			$func(\bc\traits\company\BlogFeedTrait::class_path(),'Blog');
			$func(\bc\traits\company\GithubFeedTrait::class_path(),'Github');
			$func(\bc\traits\company\GlassdoorFeedTrait::class_path(),'Glassdoor');
			$grid->data('_url_',$this->_companyUrl($company));

		}else{

			\tip\screens\elements\Raw::create('empty',$top,'No more companies for review.  Try again later.');
			$top->data('_url_',$this->_companyUrl($company));
		}
		return $company;
	}



	protected function _saveCompanyInterest($company,$interest,$share){
		$user = Users::current();
		$db = $this->_db();
		if(Util::isDocument($company))$company = $company->id();
		$find = \bc\traits\user_match\CoreTrait::conditions(array('user'=>$user->id(),'company'=>$company));
		$update = array('_lastUpdate'=>time(),'traits.0.companyInterest'=>$interest,'traits.0.share'=>$share);
		return $db->collection('user_matches')->update($find,$update);
	}
	protected function _saveOpportunityInterest($company,$opportunity,$companyInterest,$oppInterest){
		$user = Users::current();
		$db = $this->_db();
		if(Util::isDocument($company))$company = $company->id();
		if(Util::isDocument($opportunity))$opportunity = $opportunity->id();
		$find = \bc\traits\user_match\CoreTrait::conditions(array('user'=>$user->id(),'company'=>$company));
		if($oppInterest >= 50 && !$companyInterest || $companyInterest == -1){
			$companyInterest = 50;
		}
		$update = array('_lastUpdate'=>time(),"traits.0.companyInterest"=>$companyInterest,"traits.0.opportunities.$opportunity.interest"=>$oppInterest);
		return $db->collection('user_matches')->update($find,$update);
	}
	protected function _match($id){
		$me = Users::currentId();
		$db = $this->_db();
		return UserMatches::create($db->collection('user_matches')->get1(array('traits'=>array('$elemMatch'=>array('company'=>$id,'user'=>$me)))));
	}
	public function form_companyRank($form,$data){
		$screen = $form->screen();
		$id = $data['id'];
		$startingInterest = Util::nvlA($data,'startingInterest');
		$interest = Util::nvlA($data,'rank.0');
		$skip = Util::nvlA($data,'skip');
		$name = Util::nvlA($data,'company');
		$share = Util::isTruthy(Util::nvlA($data,'share'));

		$next = $skip || Util::nvlA($data,'next');
		$grid = $screen->blockGrid()->element('top.company');
		if($skip){
			$interest = $startingInterest !== null ? $startingInterest : -1;
		}
		$this->_saveCompanyInterest($id,$interest,$share);
		$likedText = $skip ? 'Skipped' : 'Updated';
		Alert::create('success', $grid, "$name $likedText!");
		$this->_loadCompany($screen,$next ? 'next' : $id,false);
		return $next ? $screen->blockGrid()->render() : $grid->render();

	}

	protected function _engage($top,$company,$render=true){

		$table = TableBlock::create('engage',$top);
		//see if they are a corporate account
		$corporate = $company->tData('/corporateAccount');
		$screen = $top->screen();
		if($corporate){
			$companyId = $company->id();
			$companyName = $company->name();

			$screen->data('company',$companyId,'render');
			$screen->data('corporate',$corporate,'render');
			$screen->data('companyName',$companyName,'render');
			$table->upperButtons(HtmlTags::modalButton('New Conversation',"conversation:read/-1"));
			$table->addHeaders('last,listing,topic,actions');
			$self = $this;
			$db = $this->_db();
			$this->screenAction('conversation:read',true);
			$me = Users::currentId();
			$conversations = \bc\traits\conversation\CoreTrait::masterFind($this,array('user'=>$me,'corporate'=>$corporate),array(),'all',array(),array('order'=>array('_last_modified'=>-1)));
			$table->addRows($conversations,array(
				'last'=>function($val,$key,$row){
					return date('m/d/Y h:i',$row->_last_modified);
				},
				'listing'=>function($val,$key,$row) use($db){
					$oppId = $row->tData('/opportunity');
					if(!$oppId || $oppId == -1){
						return "General Inquiry";
					}else{
						$opp = Opportunities::byIdRemote($db,$oppId);
						return $opp->name();
					}

				},
				'topic'=>function($val,$key,$row){
					return $row->tData('/topic');
				},
				'actions'=>function($val,$key,$row) use($companyId,$companyName){

					return HtmlTags::buttonGroup(array(
						HtmlTags::modalButton('Read/Reply','conversation:read/'.$row->id()),

					));
				},

			));
		}else{
			$table = Raw::create('engage',$top,"This company has not yet signed up with BetaCave to receive your feedback.  We will be in contact with them shortly to let them know you had a question.  In the meantime, contact them directly via their website or twitter");
		}

		return $render ? $table->render() : $table;
	}

	public function conversation_read($screen,$cId){
		$companyId = $screen->data('company');
		$corporate = $screen->data('corporate');
		$companyName = $screen->data('companyName');

		$conversation = Conversations::masterById($this,$cId,false);
		$block = Block::create('conversation',$screen);
		$block->box(false);
		$title = "Conversation with $companyName";
		if($conversation){
			$thread = $conversation->tData2A('/thread');
			$title .= ": ".$conversation->tData('/topic');
			$data = array();
			$user = Users::current();
			$company = Accounts::masterById($this,$corporate);
			foreach($thread as $item){
				$who = Util::nvlA($item,'who');
				if($who == 'user'){
					$icon = $user->tData('/avatar');
					$name = $user->name();

				}else{
					$icon = $company->tData('/avatar');
					$name = $company->name();
				}
				$msg = Util::nvlA($item,'msg');
				$time = Util::nvlA($item,'time');
				$time = Util::timeAgo($time);
				$data[] = compact('who','icon','name','time','msg');
			}
			$raw = Raw::create('thread',$block,array('template'=>'bc-thread'));
			$raw->data('thread',$data);
			$form = Form::create("message",$block,array('template'=>'bc-thread-message'));
			FormField::hidden('cId',$form,$cId);
		}else{
			$db = $this->_db();
			$form = Form::create("message",$block,array('template'=>'bc-thread-message'));
			$find = \bc\traits\user_match\CoreTrait::conditions(array('user'=>Users::currentId(),'company'=>$companyId));
			$match = $db->collection('user_matches')->get($find,array(),array('limit'=>1));
			$match = UserMatches::create($match,array('set'=>true));
			$oppIds = array();
			$matches = $match->tData2A('/opportunities');

			$opps = Opportunities::byIdRemote($db,array_keys($matches),false,true);
			$val = "";
			FormField::select('opportunity',$form,'Corresponding Opportunity',$val,$opps,'',false,true,array(
				'optionsConfig'=>array(
					'emptyFirst'=>true,
					'firstValue'=>-1,
					'firstLabel'=>'General Inquiry',
					'value'=>'$key',
					'label'=>'name'
				)
			));
			FormField::reqField('text','topic',$form);

		}
		$form->fieldSet('default')->template('bc-thread-fieldSet');
		FormField::textarea('message',$form);
		return $block->render(array('title'=>$title,'fullSize'=>true,'buttons'=>false));

	}

	public function form_message($form,$data){
		$screen = $form->screen();
		$msg = $data['message'];
		$id = Util::nvlA($data,'cId');
		$isNew = !$id;
		$company = $screen->data('company');
		$corporate = $screen->data('corporate');
		$companyName = $screen->data('companyName');

		$db = $this->_db();
		if($id){
			$c = Conversations::masterById($this,$id);
		}else{
			//new
			$opportunity = $data['opportunity'];
			$user = Users::currentId();
			$topic = $name = $data['topic'];
			$c = \bc\traits\conversation\CoreTrait::newEntity(compact('name'),compact('corporate','opportunity','user','topic'),false);
		}
		$who = 'user';
		$time = time();
		$thread = $c->tData2A('/thread');
		$new = compact('who','msg','time');
		array_unshift($thread,$new);
		$c->tData2A('/thread',$thread);
		$c->save();
		//notify company of new msg
		$master = MasterTrait::masterFindFirst($this);
		$email = MasterTrait::tData($master,'emailService');
		$emailService = $this->_runAsMasterAccount(function() use($email){
			return Endpoints::serviceById($email);
		});

		$subject = "[BetaCave] *new feedback* " . $c->tData('/topic');
		$oppId = $c->tData('/opportunity');
		$cId = $c->id();
		$url = \lithium\net\http\Router::match("bc/home/company_pipeline/$oppId/conversations/$cId",$this->_request(),array('absolute'=>true));
		if($oppId && $oppId != -1){
			$opp = Opportunities::byIdRemote($db,$oppId);
			$oppName = $opp->name();
			$email = \bc\traits\opportunity\CorporateTrait::tData($opp,'contactEmail');
		}else{
			$oppName = "General Inquiry";
			$corp = Accounts::masterById($this,$corporate);
			$email = \bc\traits\account\CorporateTrait::ifHasData($corp,'defaultContactEmail');
		}

		$msg = "You have received a new comment re: $oppName\r\n\r\n<a href='$url'>Click here to respond.</a>";


		$emailService->sendMail($email, $subject, $msg);
		$top = $screen->blockGrid()->element('top');

		$company = Companies::byIdRemote($db,$company);
		$table = $this->_engage($top,$company,false);
		Alert::create('success',$table,"Your Message has been sent to the company.  We will notify you via email when they respond.");
		return $table->render();

	}




}
