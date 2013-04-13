<?php
namespace tip_docs\apps;

use tip\controls\App;
use tip\screens\BlockPartyScreen;
use tip\screens\elements\Block;
use tip\screens\elements\Raw;
use tip\screens\elements\Table;
use tip\screens\elements\Menu;
use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\elements\Alert;
use tip_docs\screen\elements\Editor;
use tip_docs\screen\elements\MarkdownDisplay;
use tip\core\Util;
use tip\screens\helpers\base\HtmlTags;

/**
 * Description here
 */
class DocsApp extends App{

    /**
     * Just describing a test
     * @var string
     */
    protected $_test = 'test value';

    public function __construct(array $config = array()) {
        parent::__construct($config);
    }


    protected function _init() {
        parent::_init();
        $this->action('','home');

        $this->config('name','Documentation');
        $this->config('type','core');
        $this->config('description','Documentation for the Platform');
        $this->configMenu('home');

    }

    public function home(){
        $me = \tip_docs\models\Classes::get($this,true);
        $screen = $this->_screenStart('durc',__FUNCTION__,array(
                        'title'=>'Documentation',

                    ));
        $screen->initDurcModel(\tip_docs\models\Classes::modelClass());
        $screen->render();

    }
    public function test(){
        $grid = $this->_screenStart('block',__FUNCTION__,array(
                        'title'=>'Documentation',

                    ));
        $screen = $grid->screen();
        $grid->layout2Col();
        $form = Form::create('editor',$grid);
        $editor = Editor::create('editor',$form, array('content'=>"My content is here\n#But here is my title\n\t__and my bold__",'renderView'=>'markdown'));
        $editor->data('submitRendered',true);

        MarkdownDisplay::create('markdown',$grid,array('template'=>'tip-raw'));
        return $screen->render();
    }

    public function form_editor($form,$data){
        $content = $data['editor'];
        echo "<pre>"; print_r( $content ); echo "</pre>";exit;
    }


}
?>