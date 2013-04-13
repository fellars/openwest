<?php
namespace tip_docs\screen\elements;


use tip\controls\Element;


class MarkdownDisplay extends Element{
    public function __construct(array $config = array())
    {
        parent::__construct($config);
    }

    protected function _init()
    {
        $this->_configData('template','tip-raw');
        parent::_init();
        $this->_configData('content');
        $this->screenAsset('js','markdown','/tip_docs/js/marked.js');
        $this->screenAsset('js','tipMarkdown','/tip_docs/js/tip-markdown');

    }

    public function content($val = null)
    {
        return $this->data(__FUNCTION__, $val);
    }




}