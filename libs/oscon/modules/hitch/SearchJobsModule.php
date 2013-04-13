<?php
namespace bc\modules\hitch;

use \tip\controls\Module;
use bc\modules\hitch\base\BaseHitchModule;

class SearchJobsModule extends BaseHitchModule
{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();

    }

    public function searchIndeed(){
        $block = $this->_screenStart('block',__FUNCTION__,array(
            'title'=>'Search Jobs',
            'subtitle'=>"Jobs Performed against Indeed API"
        ));
        $this->screenAction('crunchbase:view','crunchbase');
        $searchForm = Form::create('searchFormIndeed',$block,array('formLayout'=>'inline','disableActionBorder'=>true,'submitButtonText'=>'Search'));
        FormField::field('text','search',$searchForm,array('label'=>false,'placeholder'=>'Search Jobs'));

        $table = Table::create('results',$block)->addHeaders('title::::jobtitle,company,jobkey,location::::formattedLocation,source,timeAgo::::formattedRelativeTime,date,snippet,actions');
        return $block->screen()->render();
//        $indeed = $this->loadService('indeed',array('channel'=>'betacave'));
//        echo "<pre>"; print_r( $indeed->jobs('4c26154395901de3') ); echo "</pre>";exit;
    }
    public function searchAngellist(){
        $block = $this->_screenStart('block',__FUNCTION__,array(
            'title'=>'Search Jobs',
            'subtitle'=>"Jobs Performed against Angelist API"
        ));
        $this->screenAction('crunchbase:view','crunchbase');
        $this->screenAction('summarize','summarize');
        $searchForm = Form::create('searchFormAngel',$block,array('formLayout'=>'inline','disableActionBorder'=>true,'submitButtonText'=>'Search'));
        $searchForm->data('searchType','meta','render');
        FormField::field('text','search',$searchForm,array('label'=>false,'placeholder'=>'Search Jobs By Tag','size'=>3));
        FormField::field('select','type',$searchForm,array('options'=>'Startup,MarketTag,LocationTag','value'=>'MarketTag','label'=>false,'size'=>3));
        $table = Table::create('results',$block)->addHeaders('title,company::::startup.name,location,date::::updated_at,snippet::::startup.product_desc,actions');
        return $block->render();
    }

    private static function __searchActionFilter($idPath='id',$namePath='company'){
        return function($val,$key,$row,$index)use ($idPath,$namePath){
            return HtmlTags::buttonGroup(array(
                HtmlTags::modalLink('View Crunchbase','crunchbase:view/'.Util::nvlA($row,$namePath),array(),array('class'=>'btn')),
                HtmlTags::modalLink('Summarize Job','summarize/'.Util::nvlA($row,$idPath),array(),array('class'=>'btn'))
            ));
        };
    }

    public function form_searchFormIndeed($form,$data){
        $search = $data['search'];
        $table = $form->parent()->element('results');
        $indeed = $this->loadService('indeed',array('channel'=>'betacave'));
        $results = $indeed->search($search);
        $results = Util::nvlA2A($results,'results');
        $table->clear();
        $table->addRows($results,array(


            'date'=>Table::filterDate(),
            'actions'=>static::__searchActionFilter()
        ));
        $form->parent()->render(array('who'=>'parent'));
    }
    public function form_searchFormAngel($form,$data){
        $search = Util::nvlA($data,'search');
        $type = Util::nvlA($data,'type');
        $table = $form->parent()->element('results');
        $al = $this->loadService('angellist',array());
        $searchType = $form->data('searchType');
        $table->clear();
        $doSearch = true;
        if($search && $searchType == 'meta'){
            $results = $al->search($search,$type);
            if(count($results)==0){
                $doSearch = false;
            }else if (count($results) == 1){
                $searchType = $type;
                $type = Util::nvlA($results,"0.id");
            }else{
                $doSearch = false;

                //update the type item
                $multiple = false;
                FormField::field('select','type',$form,array('options'=>$results,'value'=>Util::nvlA($results,"0.id"),'multiple'=>$multiple,'label'=>false,'size'=>3,'optionsConfig'=>array('label'=>'name','value'=>'id')));
                $form->removeElement('search');
                $form->data('searchType',$type);
            }
        }
        if($doSearch){
            $results = $al->jobs($searchType,$type);
//            echo "$searchType,$type<pre>"; print_r( $results ); echo "</pre>";exit;
            $table->addRows(Util::nvlA2A($results,"jobs"),array(
                'date'=>Table::filterDate(),
                'location'=>function($val,$key,$row){
                  $tags = Util::nvlA2A($row,'tags');
                    $loc = array();
                    foreach($tags as $tag){
                        if($tag['tag_type'] == 'LocationTag'){
                            $loc[] = $tag['name'];
                        }
                    }
                    return implode(', ',$loc);
                },
                'actions'=>static::__searchActionFilter('startup.id','startup.name')
            ));
        }
        $form->parent()->render(array('who'=>'parent'));
    }
    public function summarize($screen,$id){
        $al = $this->loadService('angellist');
        $company = $al->company($id);
        $jobs = $al->jobs('company',$id);
        $maps = $this->setting("mappings");
        $companyRanker = new \bc\hitch\angel\AngelCompanyRanker(array('app'=>$this,'mappings'=>$maps));
        $oppRanker = new \bc\hitch\angel\AngelOpportunityRanker(array('app'=>$this,'mappings'=>$maps));
        $sumComp = $companyRanker->summarize($company);
        $sumOpps = array();
        foreach($jobs as $job){
            $sumOpps[] = $oppRanker->summarize($job);
        }
        return Raw::create('summarize',null,"$id<pre>". print_r( $sumComp,true ). print_r($sumOpps,true).  "</pre>")->render();
    }


    public function crunchbase($screen,$company,$page=1){
            $cb = $this->loadService('crunchbase');
            $results = $cb->search($company,compact('page'));
            $total = Util::nvlA($results,'total');
            $page = Util::nvlA($results,'page');
            $totalPages = $total / 10 + ($total % 10 ? 1:0);
            $pageOptions = array();
            for($i=1;$i<=$totalPages;$i++)$pageOptions[] = "$i";
    //        $block = Block::create('crunchbaseSearchResults',$screen);
            $form = Form::create('crunchbaseSearch',$screen);
            FormField::field('text','company',$form,array('value'=>$company));
            FormField::field('select','page',$form,array('options'=>$pageOptions,'value'=>$page,'submitOnChange'=>true));
            FormField::field('raw','reload',$form,array(
                'label'=>false,
                'html'=> HtmlTags::tag('div',
                 HtmlTags::tag('button','Reload',array('class'=>'btn submit','type'=>'submit'))
                . ($page > 1 ? HtmlTags::tag('button','Prev Page',array('class'=>'btn submit')) : '')
                . ($page < $totalPages ? HtmlTags::tag('button','Next Page',array('class'=>'btn submit')) : '')
                . HtmlTags::tag('button','Done',array('class'=>'btn submit')),
                    array('class'=>'btn-group'))
            ));
            $table = Table::create('crunchbaseResults')->addHeaders('id::::permalink,name,overview');
            $table->addRows(Util::nvlA($results,'results'),array(
                'overview'=>function($val){return substr($val,0,250); }
            ),function($row){return Util::nvlA($row,'namespace')==='company';});//only return company entities
            FormField::field('table','matchedCompany',$form,array(
               'label' => 'Select Matching Company',
               'table'=>$table,
                'limit'=>1
            ));
    //        $result = Raw::create('crunchbase',null,HtmlTags::tag('pre',print_r($results,true)));
            return $form->render(array('status'=>'me'));
        }

    public function form_crunchbaseSearch($form,$data,$submittedOnSelect,$screen,$submitButton){
        $page = $data['page'];
        if($match = $data['matchedCompany']){
            //found a match
            $cb = $this->loadService('crunchbase');
            echo "$match<pre>"; print_r( $cb->company($match) ); echo "</pre>";exit;
            return $form->render(array('who'=>'silent'));
        }
        if($submitButton == 'Done'){
            return $form->render(array('who'=>'silent'));
        }else if($submitButton == 'Next Page'){
            $page += 1;
        }
        else if($submitButton == 'Prev Page'){
            $page -= 1;
        }else if ($submittedOnSelect){
            $page = 1;
        }


//        echo "$submitButton - $submittedOnSelect<pre>"; print_r( $page ); echo "</pre>";exit;
        return $this->crunchbase($screen,$data['company'],$page);
    }
}


?>