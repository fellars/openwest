<?php
namespace bc\apps;

use tip\controls\App;
use tip\screens\BlockPartyScreen;
use tip\screens\elements\Block;
use tip\screens\elements\Raw;
use tip\screens\elements\Table;
use tip\screens\elements\Menu;
use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\elements\Alert;

use tip\core\Util;
use tip\screens\helpers\base\HtmlTags;

class LaunchContestApp extends App{


    public function __construct(array $config = array()) {
        parent::__construct($config);
    }


    protected function _init() {
        parent::_init();
        $this->action('','contest');
        $this->action('screen:contest:add','contest_add');
        $this->action('screen:contest:tweet','contest_tweet');
        $this->action('screen:contest:edit','contest_edit');
        $this->action('screen:contest:delete','contest_delete');
        $this->action('screen:contest:add:manual','contest_manual_add');
        $this->config('name','Betacave Launch Contest');
        $this->config('type','contests');
        $this->config('category','launch');
        $this->config('description','Managing the Betacave Launch Contest');
        $this->configMenu(array('contest::View Contest','tweets::View Tweets','emails::View Emails','configure'));
    }



    public function contest(){
        $block = $this->_screenStart('block','contest',true);

        $mongolab = $this->loadService('mongolab');
        if(!$mongolab){
            $this->redirect($this->appUrl('settings'));
        }
        $results = $mongolab->listDocuments('contestsubmissions');
//        echo "<pre>"; print_r( $results ); echo "</pre>";exit;
        $actions = function($val,$key,$row){
            $data = array(
                                'id'=>Util::nvlA($row,'_id.$oid'),
                                'img'=>Util::nvlA($row,'img'),
                                'cropImg'=>Util::nvlA($row,'cropImg'),
                                'handle'=>Util::nvlA($row,'handle'),
                                'oriText'=>Util::nvlA($row,'text'),
                                'name'=>Util::nvlA($row,'name'),
                                'sourceId'=>Util::nvlA($row,'sourceId'),
                                'officialTweet'=>$officialTweet = Util::nvlA($row,'officialTweet'),

                            );
            return ($officialTweet ? "" : HtmlTags::modalLink('Tweet','contest:tweet',$data)
                  ." | ")
                  . HtmlTags::modalLink('Edit','contest:edit',$data)
                  ." | "
                  . HtmlTags::modalLink('Delete','contest:delete',$data);
        };
        Table::create('contests',$block)->addHeaders('source,name,img::image,actions')->addRows($results,array('actions'=>$actions,'img'=>function($val){return HtmlTags::image($val,array('style'=>'width:100px'));}));
        $block->screen()->render();
    }
    private function _tweetMsg($handle,$reminder=false){
        return !$reminder ? "Retweet this to help @$handle win an Ipad Mini. Show us your betacave and you could win too!  http://betacave.com/#contest"
                : "@$handle - check out your official betacave contest submission. Get as many people as you can to retweet this. <link>";
    }

    public function contest_tweet($screen){
        $handle = $this->requestData('handle');
        $url = $this->requestData('img');
        $tweet = $this->_tweetMsg($handle);
        $form = Form::create('contest_tweet',$screen,array('submitButton'=>false));
        FormField::field('textArea',$name = 'tweet',$form,array(
            'label'=>'Tweet this from @Betacave account:',
            'value'=>$tweet));
        FormField::field('text',$name = 'reminder',$form,array('label'=>"<b>Upload the picture from here:</b>",'value'=>$url));
        FormField::field('raw',$name = 'spacer',$form,array('label'=>' ','html'=>"<hr/>"));
        FormField::field('textArea',$name = 'tweetAt',$form,array(
            'label'=>'Then copy the url of tweet and do new tweet:',
            'value'=>$this->_tweetMsg($handle,true)));
        return $form->render();
    }
    public function contest_edit($screen){
        $form = Form::create('contest_edit',$screen);
        FormField::field('hidden',$name = 'id',$form,array('value'=>$this->requestData($name)));
        FormField::field('text',$name = 'img',$form,array('label'=>'Image Url','value'=>$this->requestData($name)));
        FormField::field('text',$name = 'cropImg',$form,array('label'=>'Cropped Image Url','value'=>$this->requestData($name)));
        FormField::field('text',$name = 'handle',$form,array('value'=>$this->requestData($name)));
        FormField::field('text',$name = 'name',$form,array('value'=>$this->requestData($name)));
        FormField::field('text',$name = 'sourceId',$form,array('value'=>$this->requestData($name)));
        FormField::field('text',$name = 'officialTweet',$form,array('value'=>$this->requestData($name)));
        FormField::field('text',$name = 'text',$form,array('value'=>$this->requestData('oriText')));

        return $form->render();
    }

    public function form_contest_edit($form,$data){
//        echo "<pre>"; print_r( $data ); echo "</pre>";exit;
        $mongolab = $this->loadService('mongolab');
        $id = $data['id'];
        unset($data['id']);
        $result = $mongolab->updateDocById('contestsubmissions',$id,$data);
        Alert::create('success',$form,array('text'=>'Successfully edited'));
        $form->render(array('status'=>'self'));
    }

    public function tweets(){
        $block = $this->_screenStart('block','tweets',array('title'=>'Betacave Tweets','subtitle'=>"View Incoming Tweets"));
        Raw::create('buttons',$block,array('html'=>HtmlTags::modalLink('Manual Entry','contest:add:manual',array(),array('class'=>'btn btn-primary','style'=>'align:right'))));

        $endpointId = $this->appConfig('twitter');
        $twitter = \_services\services\TwitterService::byId($endpointId);
        $created = function($val){return date('m/d/Y',strtotime($val));};
        $pic = function($val,$key,$entity){
            $imgs = Util::readSet($entity,'entities.media');
            if(!$imgs)$imgs=Util::readSet($entity,'entities.urls');
            $response = '';
            foreach($imgs as $imgInfo){
                $img = Util::readSet($imgInfo,'expanded_url');
                $imgData = array('from_user_name'=>Util::readSet('from_user_name',$entity),'source_id'=>"t_".Util::readSet('id_str',$entity),'text'=>Util::readSet('text',$entity),'from_user'=>Util::readSet('from_user',$entity),'img'=>$img);
                if(preg_match('/(.jpg|.jpeg|.png)/i',$img) !== 0){
                    $response .=  HtmlTags::modalLink('View Image',null,array('text'=>HtmlTags::image($img,array('style'=>'width:500px')).HtmlTags::modalLink('Add To Contest','contest:manual:add',$imgData,array('class'=>'btn'))),array('escape'=>true)) .'<br/>';
                }
                else if($img){
                    $response .= HtmlTags::modalLink('Check For Image','contest:add:manual',$imgData) .'<br/>';
                }
            }
            return $response;
        };
        $fromUser = function($val,$key,$entity){
            $user = Util::readSet($entity,'from_user');
            return HtmlTags::link($val,"https://twitter.com/$user",array('target'=>'_blank'));
        };
        $text = function($val,$key,$entity){
            $user = Util::readSet($entity,'from_user');
            $id = Util::readSet($entity,'id_str');
            return HtmlTags::link($val,"https://twitter.com/$user/status/$id",array('target'=>'_blank'));
        };
        $rowProcess = array('created_at'=>$created,'pic'=>$pic,'from_user_name'=>$fromUser,'text'=>$text);
        $table = Table::create('tweets',$block)->addHeaders('created_at::Time,from_user_name::From,pic,text');

        $twitter->switchResource('search');
        $tweets = $twitter->get('search',array('q'=>'#mybetacave','result_type'=>'mixed','include_entities'=>true));
//        echo "tweets:<pre>"; print_r( $tweets ); echo "</pre>";exit;
        if(isset($tweets->results))$table->addRows($tweets->results,$rowProcess);
        $tweets = $twitter->get('search',array('q'=>'betacave','result_type'=>'mixed','include_entities'=>true));
        if(isset($tweets->results)) $table->addRows($tweets->results,$rowProcess);

        $table->data('initialSorting',array(array(0,'desc')));
        $block->screen()->render();

    }
    public function emails(){
        $block = $this->_screenStart('block','emails',array('title'=>'Emails','subtitle'=>"View Incoming Emails"));


        $mode = 'read';
        $endpointId = $this->appConfig('email');
        $searchQuery = 'ALL';
        $email = \_services\services\EmailService::byId($endpointId,compact('mode'));

        $emails = $email->searchEmails($searchQuery,true);

        Table::create('emails',$block)->addHeaders('date,subject,message')->addRows($emails);
        $block->screen()->render();
    }
    public function configure(){
        $block = $this->_screenStart('block',__FUNCTION__,array(
            'title'=>'Endpoints',
            'subtitle'=>"If you haven't configured endpoints yet, <a href='/_core/settings/services'>then do so now</a>"
        ));

        $form = Form::create('config',$block);
        $form->submitButtonText('Done');
        $form->data('disableActionBorder',true);

        $settings = $this->appConfig();
        FormField::selectModel($name = 'mongolab',$form,'',Util::nvlA($settings,"$name"),'endpoints','tip_mongolab::mongolab');
        FormField::selectModel($name = 'twitterOauth',$form,'',Util::nvlA($settings,"$name"),'endpoints',\_services\traits\endpoint\OauthTrait::conditions(array('clientService'=>'_services::twitter')));
        FormField::selectModel($name = 'twitter',$form,'',Util::nvlA($settings,"$name"),'endpoints','_services::twitter');
        FormField::raw('newTwitter',$form,' ',\_services\apps\OauthApp::buildLink('twitter',$this->setting('twitterOauth')));
        FormField::selectModel($name = 'email',$form,'',Util::nvlA($settings,"$name"),'endpoints','_services::email');
        $block->screen()->render();
    }


    public function contest_manual_add($screen){
        $form = Form::create('manual_add',$screen);
        $handle = $this->requestData('fromUser');
        $tweet = $this->_tweetMsg($handle);
        $reminder = $this->_tweetMsg($handle,true);


        FormField::field('raw','instructions',$form,array('html'=>'1) Crop Image to 156x156<br/>2) Upload that image to someplace like flickr and copy the url to the image (not the landing page)<br/>'
        .'3)Save that img location here.<br/>4) Create official tweet with following text:<pre>'.$tweet.'</pre> Be sure to upload the full from here.5) Send a tweet to the owner notifying him of contest:<pre>'.$reminder.'</pre>'
        .'5) Finally, record the new link of the official tweet and save in this form under officialTweet<br/>6) Save form'));

        FormField::field('text','img',$form,array('label'=>'Image Url'));
        $img = $this->requestData('img');
        if($img){
            FormField::field('raw','url',$form,array('html'=>HtmlTags::link('Check here',$img,array('target'=>'_blank'))));
        }
        FormField::field('text',$name = 'cropImg',$form,array('label'=>'Cropped Image URL','value'=>$this->requestData($name)));
        FormField::field('text',$name = 'fromUser',$form,array('value'=>$this->requestData($name)));
        FormField::field('text',$name = 'fromUserName',$form,array('value'=>$this->requestData($name)));
        FormField::field('text',$name = 'sourceId',$form,array('value'=>$this->requestData($name)));
        FormField::field('text',$name = 'officialTweet',$form,array('value'=>$this->requestData($name)));
        FormField::field('text',$name = 'text',$form,array('value'=>$this->requestData($name)));
        return $form->render();
    }

    public function form_manual_add($form,$data){
        return $this->contest_add($data);
    }

    public function contest_add($data=array()){
        $this->requestData($data);
//        $this->dumpPostData();
        $from = $this->requestData('fromUser');
        $fromName = $this->requestData('fromUserName');
        $img = $this->requestData('img');
        $sourceId = $this->requestData('sourceId');
        $text = $this->requestData('text');
        $officialTweet = $this->requestData('officialTweet');
        $cropImg = $this->requestData('cropImg');


        $mongolab = $this->loadService('mongolab');
        $document = array(
          'source'=>'twitter',
          'name' => $fromName,
          'handle' => $from,
          'img' => $img,
          'sourceId'=>$sourceId,
          'officialTweet'=>$officialTweet,
          'cropImg'=>$cropImg,
          'text'=>$text,
          'time'=>time(),
        );
//        $this->dumpPostData();
        $result = $mongolab->updateDocuments('contestsubmissions',$document,compact('sourceId'),false,true);
//        echo "results:<pre>"; print_r( $result ); echo "</pre>";exit;
        $this->renderJson(array('status'=>'silent','closeAll'=>true,'_alert_'=>Alert::create('success',null,array('text'=>'Successfully added'))->renderQuick()));
    }

}
?>