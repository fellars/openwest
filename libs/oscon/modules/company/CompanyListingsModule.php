<?php
namespace bc\modules\company;

use bc\apps\MasterApp;
use bc\models\Opportunities;
use bc\modules\company\base\BaseCompanyModule;
use tip\core\Util;
use tip\models\Endpoints;
use tip\screens\elements\Alert;
use tip\screens\helpers\base\HtmlTags;
use tip\screens\elements\Form;
use tip\models\Accounts;
use tip\models\Users;
use tip\screens\elements\TableBlock;
use tip\screens\elements\Table;

class CompanyListingsModule extends BaseCompanyModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
    }

    public function load($screen, $settings=array(),$render=true){
		$grid = $screen->blockGrid();
		$screen->screenJs('wysiwyg',true);
		$screen->screenCss('wysiwyg',true);
		$account = Accounts::current();
		$render = Util::nvlA($settings,'render',$render);
		$grid->title($account->name(). " Listings");
		$grid->removeAllElements();
		$db = $this->_db();
		$table = TableBlock::create('opportunities',$grid);
		$table->addHeaders('_id,name::Title,roles::::/roles,actions');
		$table->box(false);
		$this->screenAction('listing:new',true);
		$this->screenAction('skills:table:addRow','skills_table_row_add');
		$this->screenAction('listing:edit',true);
		$this->screenAction('listing:delete',true);
		$this->screenAction('listing:activate',true);
		if(($id=Util::nvlA($settings,'id')) == 'new'){
			$this->listing_new($screen,false);
		}else if($id){
			$this->listing_edit($screen,$id,false);
		}else{
				$opps = Opportunities::byCorporateId($account->id(),$db);
				$numListings = count($opps);
				$table->upperButtons(array(
					HtmlTags::refreshButton('New Listing','listing:new')
				));
				$table->addRows($opps,array(
					'actions'=>Table::filterActions(array(
						'editMode'=>false,
						'callback'=>'listing',
						'preActions'=>function($row){
							$active = \bc\traits\opportunity\CorporateTrait::tData($row,'active');
							$activate = HtmlTags::refreshButton($active?'Deactivate':'Activate','listing:activate/'.$row->id().($active?'/0':'/1'));
							$pipeline = HtmlTags::link("View Pipeline",'/bc/home/company_pipeline/'.$row->id(),array('class'=>'btn'));
							$actions =  array();
							if($active){
								$actions[] = $pipeline;
							}
							$actions[] = $activate;
							return $actions;
						}
					))
				));


		}

		return $render ? $grid->render(array('_url_'=>'/bc/home/company_listings')) : $grid;
    }

	public function listing_edit($screen,$id,$render=true){
		$opp = Util::isDocument($id) ? $id : Opportunities::byIdRemote($this->_db(),$id);
		$grid = $screen->blockGrid();
		$grid->removeAllElements();
		$form = Form::create('listing',$grid,array('cancelButton'=>true));
		$form->data('cancelRefresh','listing:cancel');
		$form->data('stepNext',true);
		$this->screenAction('listing:cancel','load');
		$form->steps('overview,listing');
		$account = Accounts::current();
		$type = $opp->isNew() ? "New" : "Edit";
		$grid->title($account->name(). ": $type Listing");

		$opp->form($form);
		return $render ? $grid->render(array('_url_'=>'/bc/home/company_listings/'.($opp->isNew() ? "new" : $opp->id()))) : $grid;

	}
	public function listing_delete($screen,$id){
		$db = $this->_db();
		$db->collection('opportunities')->deleteById($id);
		$grid = $screen->blockGrid();
		Alert::create('success',$grid,"Listing Deleted");
		return $this->load($screen);
	}

	public function listing_activate($screen,$id,$active=0){
		$db = $this->_db();
		$active = Util::isTruthy($active);
		$opp = Opportunities::byIdRemote($db,$id);
		\bc\traits\opportunity\CorporateTrait::tData($opp,'active',$active);
		$opp->saveRemote($db);
		$grid = $screen->blockGrid();
		Alert::create('success',$grid,"Listing ".($active? "activated":'deactivated'));
		return $this->load($screen);

	}

	public function listing_new($screen,$render=true){
		$opp = Opportunities::newEntity();
		$opp->addTrait(new \bc\traits\opportunity\CorporateTrait());
		return $this->listing_edit($screen,$opp,$render);
	}

	public function form_listing($form,$data){
		$id = $form->id();
		$db = $this->_db();
		if($id){
			$opp = Opportunities::byIdRemote($db,$id);
		}else{
			$opp = Opportunities::newEntity();
			$opp->addTrait(new \bc\traits\opportunity\CorporateTrait());
		}
		$account = Accounts::current();
		$companyId = \bc\traits\account\CorporateTrait::tData($account,'company');
		if($companyId){
			$opp->tData('/company',$companyId);
		}
		$opp->process($form,false);
		\bc\traits\opportunity\CorporateTrait::tData($opp,'corporate',Accounts::currentId());
		$opp->saveRemote($db);
		Alert::create('success',$form->parent(),"Listing " . ($id ? "updated" : "created"));
		$form->parent()->removeAllElements();

		return $this->load($form->screen(),array(),true);
	}

	public function skills_table_row_add($screen){
		$opportunityId = $this->requestData('opportunity');
		if($opportunityId){
			$opp = Opportunities::byIdRemote($this->_db(),$opportunityId);

		}else{
			$opp = Opportunities::newEntity();
			$opp->addTrait(new \bc\traits\opportunity\CorporateTrait());
		}
		$table = $screen->blockGrid()->element('listing')->field('skills')->element('skillsTable');
		$forms = \bc\traits\opportunity\CoreTrait::get($opp)->skillsTableRowAdd($table,array(false));
		$lastRow = $table->getRow(-1);
		$screen->session('to');
		return $this->renderJson(array('forms'=>$forms,'row'=>$lastRow));

	}
}
