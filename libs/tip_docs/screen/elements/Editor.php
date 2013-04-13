<?php
namespace tip_docs\screen\elements;


use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\elements\Alert;
use tip\core\Util;

class Editor extends FormField{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        $this->_configData('type','AceEditor','fieldType');
        $this->_configData('view','AceEditor');
        parent::_init();
        $this->_configData('content');
        $this->_configData('renderView');
        $this->_configData('mediaOptions');
        $this->screenAsset('js','ace','/tip_docs/js/ace/ace.js');
        $this->screenAsset('js','tipEditor','/tip_docs/js/tip-ace');
        $this->screenAsset('hb','tipEditor','/tip_docs/hb/tip-ace');
        $this->action('editor:link',true);
        $this->action('editor:media',true);

    }

    public function content($val = null)
    {
        return $this->data(__FUNCTION__, $val);
    }

    public function renderView($val = null)
    {
        return $this->data(__FUNCTION__, $val);
    }

    public function mediaOptions($val = null)
    {
        return $this->data(__FUNCTION__, $val);
    }


    public function editor_link(){
        $form = Form::create('link',$this,array('submitButtonText'=>'Insert','cancelButton'=>true));
        FormField::reqField('text','url',$form,array('defaultValue'=>'http://'));
        FormField::field('text','title',$form,array());
        return $form->render(array('buttons'=>false,'title'=>'Insert Link'));
    }

    public function form_submit_link($form,$data){
        return $this->renderJson($data+array('close'=>true));
    }
    public function editor_media(){
        $media = FormField::field('media','media',$this);
        return $media->media_manager();
    }



}