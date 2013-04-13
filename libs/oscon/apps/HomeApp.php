<?php
namespace bc\apps;

use bc\traits\user\CorporateTrait;
use bc\traits\user\TalentTrait;
use tip\controls\App;
use tip\models\Users;

class HomeApp extends App
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
        $this->config('name','BetaCave App');
        $this->config('type','3rdParty');
        $this->config('category','betacave');
        $this->config('description','BetaCave Client App');
        $this->config('menuTitle', " ");
		$user = Users::current();
		if(TalentTrait::hasTrait($user)){
			$this->action('','dashboard');
			$this->action('company',true);
			$this->configMenu(array('dashboard::Dashboard','profile::Profile Settings'));
		}else if(CorporateTrait::hasTrait($user)){
			$this->action('','company_dashboard');
			$this->configMenu(array('company_dashboard::Dashboard','company_pipeline::Pipeline','company_listings::Listings','profile::Profile','company_settings::Settings'));
		}else{
			$this->action('','dashboard');
			$this->configMenu(array('dashboard::Dashboard'));
		}
		//,'cave::My Cave'
    }

    protected function _screenStart($screen, $active = '', $config = array(), $screenConfig = array(), $moduleConfig=array())
    {
		$useActive = $active == "company_profile" ? 'profile' : $active;
        $grid = parent::_screenStart($screen, $useActive, $config, $screenConfig);
        $screen = $grid->screen();
        $screen->title('BetaCave');
		$type = strpos($active,'company_') === 0 ? 'company':'talent';
        \tip\screens\elements\Sidebar::create('sidebar',$screen ,array('visible'=>false));
        \tip\screens\elements\Sidebar::create('footer',$screen ,array('visible'=>false));
        $activeCamel = \tip\core\Util::inflect($active,'c');
        $modClass = '\\bc\\modules\\' . $type . '\\' . $activeCamel . 'Module';
        $this->loadModule( $modClass,$screen, $moduleConfig + array('render'=>false) );
//        $mod = new $modClass(array('app'=>$this));
//        $screen->control($mod);
//        $mod->load($grid);
        return $grid;
    }

	public function company_dashboard(){
		$grid = $this->_screenStart('block',__FUNCTION__,true);
	    return $grid->render();
	}
	public function company_pipeline($id=''){
		$grid = $this->_screenStart('block',__FUNCTION__,true,array(),compact('id'));
	    return $grid->render();

	}
	public function company_listings($id=''){
		$grid = $this->_screenStart('block',__FUNCTION__,true,array(),compact('id'));
	    return $grid->render();

	}
	public function company_settings(){
		$grid = $this->_screenStart('block',__FUNCTION__,true);
	    return $grid->render();

	}


    public function dashboard(){
        $grid = $this->_screenStart('block',__FUNCTION__,true);
        return $grid->render();
    }
	public function company($name='',$id=''){
		$grid = $this->_screenStart('block',__FUNCTION__,true,array(),compact('name','id'));
		return $grid->render();
	}

    public function profile(){
		$active = CorporateTrait::hasTrait(Users::current()) ? 'company_' : '';
		$active .= 'profile';
        $grid = $this->_screenStart('block',$active,true);
        return $grid->render();

    }







}
