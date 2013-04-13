<?php
namespace o_webtools\services;

use tip\services\base\BaseService;

class HtmlParserService extends BaseService{
    public function __construct(array $config = array()) {
        parent::__construct($config);
    }

    protected function _init() {
        parent::_init();
        $this->_config('category','general');

    }

    protected function _loadService()
    {
        static::requireFile('simplehtmldom','simple_html_dom.php',true,true);
        $this->_service = new \simple_html_dom();

    }

    public function load_file($file){
        $content = file_get_contents($file);
        return $this->load($content);
    }
    public function load_url($url){
        return $this->load_file($url);
    }

}
