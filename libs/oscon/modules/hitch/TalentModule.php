<?php
namespace bc\modules\hitch;

use \tip\controls\Module;
use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\elements\Table;
use tip\core\Util;
use tip\screens\helpers\base\HtmlTags;
use tip\screens\elements\Block;
use tip\screens\elements\Raw;
use bc\traits\user\TalentTrait;
use bc\modules\hitch\base\BaseHitchModule;

class TalentModule extends BaseHitchModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();

    }

    public function load( $screen, $settings ){
        $block = $screen->blockGrid();
        $searchForm = Form::create('searchFormUsers',$block,array('formLayout'=>'inline','disableActionBorder'=>true,'submitButtonText'=>'Search'));
        FormField::field('select','type',$searchForm,array('options'=>'person,skills,location,market','value'=>'person','label'=>false,'size'=>3));
        FormField::field('text','search',$searchForm,array('label'=>false,'placeholder'=>'Search Users','size'=>3));
        $table = Table::create('results',$block)->addHeaders('name,email,locations,markets,skills,actions');
        return $settings['render'] ? $screen->render() : $block;
    }

    public function form_searchFormUsers($form,$data){
        $type = $data['type'];
        $search = $data['search'];

        $db = $this->loadService('bcDatabase');
        $q = array('type'=>'talent');
        if($type == 'skills' && $search){
            $q['skills.skill'] = array('$regex'=>"$search",'$options'=>'i');
        }
        if($type == 'location' && $search){
            $q['locations'] = array('$regex'=>"$search",'$options'=>'i');
        }
        if($type == 'markets' && $search){
            $q['industries'] = array('$regex'=>"$search",'$options'=>'i');
        }
        if($type == 'person' && $search){
            $q['$or'] = array(
                array('name.first'=>array('$regex'=>"$search",'$options'=>'i')),
                array('name.last'=>array('$regex'=>"$search",'$options'=>'i')),
                array('traits.0.email'=>array('$regex'=>"$search",'$options'=>'i')),
            );
        }
        $users = $db->collection('users')->get($q);
        $users = \tip\models\Users::create($users);
        $table = $form->parent()->element('results');
        $table->clear();
        $table->clearHeaders();
        $table->addHeaders('name,email,roles,locations,markets,skills,actions');
        $table->addRows($users,array(
            'name'=>function($val,$key,$user){


              return $user->name();
            },
            'email'=>function($val,$key,$user){
              return $user->tData('/email');
            },
            'roles'=>function($val,$key,$user){
                $roles= TalentTrait::tData($user,'preferredRoles');
                $result = Table::filterList();
                $result = $result($roles,$key,$user);
                return $result;
//                Table::filterList()
            },
            'locations'=>function($val,$key,$user){
                $locs= TalentTrait::tData($user,'preferredLocations');
                $result = Table::filterList();
                $result = $result($locs,$key,$user);
                return $result;
//                Table::filterList()
            },
            'markets'=>function($val,$key,$user){
                $markets = TalentTrait::tData($user,'preferredMarkets');
                $result = Table::filterList();
                $result = $result($markets,$key,$user);
                return $result;
//                Table::filterList()
            },
            'skills'=>function($val,$key,$user){
                $skills = TalentTrait::tData($user,'skills');
                $new = array();
                foreach(Util::toArray($skills) as $skill){
                    $new[] = Util::nvlA($skill,'skill').'('.Util::nvlA($skill,'level.0').')';
                }
                return implode(', ',$new);
            },
            'actions'=>function($val,$key,$user){
                return HtmlTags::buttonGroup(array(
                   HtmlTags::modalButton('Perform Search','hitch:user:search/'.$user->id())
                ));
            }
        ));
        $this->screenAction('hitch:user:search','performSearch');
        $form->parent()->render(array('who'=>'parent'));

    }

    public function performSearch($screen,$id){
        $db = $this->loadService('bcDatabase');
        $user = $db->collection('users')->getById($id);
        $user = \tip\models\Users::create($user);
        $locs = TalentTrait::tData($user,'preferredLocations');
        $maps = $this->setting('mappings');
        $locMaps = Util::nvlA2A($maps,'locations');
        $allJobs = $this->jobsIn($locs,$maps);
        $block = Block::create('jobs',$screen);
        $self = $this;//i feel like im in javascript land
        foreach(array('angel','indeed') as $source){
            $sourceJobs = $allJobs[$source];

            if($sourceJobs){
                Raw::create("{$source}_title",$block,HtmlTags::tag('h3',Util::inflect($source,'h'). " Jobs"));
                $table = Table::create($source,$block);
                $table->addHeaders('score,company,title,actions');
                $table->addInitialSort('score','desc');
//                    $jobs = Util::nvlA($sourceJobs,"jobs");
//                    if($jobs){
                        $table->addRows($sourceJobs,array(
                            'score'=>function($val,$key,$row) use($source,$user,$self,$maps){
                                $score = $self->scoreJob($user,$row,$maps,$scores);
                                $row->tData('/_scores_', $scores);
                                return $score;
                            },
                            'company'=>function($val,$key,$row){
                                return $row->company()->name();
                            },
                            'title'=>function($val,$key,$row) use($source){
                                return $row->name();
                            },
                            'actions'=>function($val,$key,$row) use($source,$user,$self,$maps){

                                return HtmlTags::modalLink('Score Details','',array('text'=>print_r($row->tData('/_scores_'),true)));
                            },
                        ));
//                    }

            }
        }
        return $block->render();
//        echo "jobs:<pre>"; print_r( $jobs ); echo "</pre>";exit;
    }


}


?>