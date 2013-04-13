<?php
use tip\core\Util;
$this->html->addAssets('css','scratch','scratch',true,100,"",array('media'=>'screen, projection'));
$this->html->addAssets('css','ie7','ie7',true,100,"gte IE 7",array('media'=>'screen, projection'));
$this->html->addAssets('css','ie6','ie6',true,100,"IE 6",array('media'=>'screen, projection'));
$this->html->addAssets('css','print','print',true,100,"",array('media'=>'print'));

$this->html->addAssets('js','belated','belated',true,100,"IE 6");
$this->html->addAssets('js','ieCss','DOMAssistant-2.0.min.js,ie-css3.js',true,100,"lt IE 9");

$this->html->addAssets('js','jquery','jquery-1.7.2.js',true,100);
$this->html->addAssets('js','modernizer','modernizr-1.5.min.js',true,100);
$this->html->addAssets('js','underscore','underscore-1.3.3',true,100);
$this->html->addAssets('js','inflection','inflection',true,100);
$this->html->addAssets('js','backbone','backbone-0.9.2.js',true,100);
$this->html->addAssets('js','marionette','backbone.marionette-0.2.6.js',true,100);
$this->html->addAssets('js','jquery-ui','jquery-ui-1.10.0.custom.min.js',true,100);
$this->html->addAssets('js','totop','jquery.ui.totop.min.js',true,100);
$this->html->addAssets('js','uniform','uniform/jquery.uniform.min.js',true,100);


$this->html->addAssets('js','less','less-1.1.5.min.js',true,100);

$this->html->addAssets('js','bootstrap','bootstrap/bootstrap-dropdown.js',true,100);

//http://www.smashingmagazine.com/2012/07/11/avoiding-faux-weights-styles-google-web-fonts -->
$this->html->addAssets('css','fonts',array(
    "http://fonts.googleapis.com/css?family=Open+Sans:400,700",
    "http://fonts.googleapis.com/css?family=Droid+Sans:400,700",

),true,100,"");
$this->html->addAssets('css','ieFonts',array(
    "http://fonts.googleapis.com/css?family=Open+Sans:400",
    "http://fonts.googleapis.com/css?family=Open+Sans:700",
    "http://fonts.googleapis.com/css?family=Droid+Sans:400",
    "http://fonts.googleapis.com/css?family=Droid+Sans:700"
),true,100,"lt IE 9");

$this->html->addAssets('css','jquery-ui','jquery-ui-1.10.0.custom.min',true,100);
$this->html->addAssets('css','bootstrap', "bootstrap/bootstrap.min", true, 100);
$this->html->addAssets('css','icons', "icons", true, 100);
$this->html->addAssets('css','uniform', "uniform/uniform.default", true, 100);
$this->html->addAssets('css','font-awesome', "bootstrap/font-awesome", true, 100);
$this->html->addAssets('css','main', "main", true, 100);




$this->html->addAssets('js','handlebars','handlebars.js',true,100);





$this->html->addAssets('js','stacktrace', "stacktrace-min-0.4", true, 0);



$this->html->addAssets('js','main', "main", true, 100);
$this->html->addAssets('js','tip', "tip", true, 100);


//Tip Items
//for now all are loaded by default; in future plan to impelment async loading and can turn these to false
$this->html->addAssets('hb','tipElements','hb/tip-elements.hb',true,50);
$this->html->addAssets('js','tipModal','tip-modal',true,50);
$this->html->addAssets('js','tipAlert','tip-alert',true,50);
$this->html->addAssets('js','tipBlockGrid','tip-blockgrid',true,50);
$this->html->addAssets('js','tipBlock','tip-block',true,50);
$this->html->addAssets('js','tipMenu','tip-menu',true,50);


$this->html->addAssets('js','tipTable','jquery.dataTables.min,tip-table',true,50);
$this->html->addAssets('css','tipTable','jquery.dataTables',true,50);
$this->html->addAssets('js','tipDatalist','tip-datalist',false,50);


$this->html->addAssets('js','tipForm','tip-form,select2.js',true,50);
$this->html->addAssets('css','tipForm','select2',true,50);
$this->html->addAssets('hb','tipForm','hb/tip-form.hb',true,50);

$this->html->addAssets('css','fileupload', "jquery.fileupload-ui", true, 50);
$this->html->addAssets('js','fileupload', "fileupload/jquery.iframe-transport.js,fileupload/canvas-to-blob.min.js,fileupload/load-image.min.js,fileupload/jquery.fileupload.js,fileupload/jquery.fileupload-ip.js,fileupload/jquery.fileupload-ui.js", true, 50);

$this->html->addAssets('js','select2','select2.js',true,50);
$this->html->addAssets('css','select2','select2',true,50);

$this->html->addAssets('js','video','video.min.js',false,50);
$this->html->addAssets('css','video','video-js.min',false,50);

$this->html->addAssets('js','datetime','bootstrap-collapse,bootstrap-datetimepicker.min',true,50);
$this->html->addAssets('css','datetime','bootstrap-datetimepicker.min',true,50);

$this->html->addAssets('js','wysiwyg','wysihtml5/parser_rules/advanced.js,wysihtml5/wysihtml5-0.3.0.js,wysihtml5/bootstrap-wysihtml5.js',false,50);
$this->html->addAssets('css','wysiwyg','bootstrap-wysihtml5',false,50);

$this->html->addAssets('js','tipSwitch','tip-switch',false,50);


$this->html->addAssets('js','tipGrid','tip-grid',false,50);
$this->html->addAssets('hb','tipGrid','hb/tip-grid.hb',false,50);



//graphs not turned on by default
$this->html->addAssets('js','tipGraph','d3/d3.v2.js,d3/nv.d3.js,d3/models/line.js,d3/models/lineChart.js,d3/models/multiBarChart.js,tip-graph',false,50);
$this->html->addAssets('css','tipGraph', "nv.d3", false, 50);
$this->html->addAssets('hb','tipGraph','hb/tip-graph.hb',false,50);
//json editor not turned on by default
$this->html->addAssets('js','jsoneditor','jsoneditor/jsoneditor,jsoneditor/contextmenu,jsoneditor/highlighter,jsoneditor/history,jsoneditor/jsonformatter,jsoneditor/node,jsoneditor/appendnode,jsoneditor/searchbox,jsoneditor/util',false,50);
$this->html->addAssets('css','jsoneditor', "jsoneditor/jsoneditor,jsoneditor/contextmenu,jsoneditor/menu,jsoneditor/searchbox", false, 50);





?>
<!DOCTYPE html>
<html lang="en" dir="ltr" class="no-js" <?php echo $h(Util::nvlA($this->_data,"$_control_.htmlTag")); ?> >
<head>
<meta charset=utf-8>
<meta name="viewport" content="width=940">
<?php echo $this->html->charset();?>
    <?php
    if(!$this->title()){
            $title = '';
            if($navTitle = trim(Util::nvlA($this->_data,"$_control_.elements.navbar.title"))){
                $title = $navTitle;
            }else if($pageTitle = Util::nvlA($this->_data,"$_control_.title")){
                  $title = $pageTitle;
            }
            if(isset($this->_data[$_control_]['elements']['navbar']['items'])){
                $items = $this->_data[$_control_]['elements']['navbar']['items'];
                foreach($items as $item){
                    if(isset($item['active']) && $item['active'] && $label = $item['label']){
                        $this->title( "$title > $label");
                        break;
                    }
                }
            }
        }
    ?>
    <script type="text/javascript">
        //adding load class to body and hide page
        document.documentElement.className += ' loadstate';
    </script>
<title><?php echo $this->title(); ?></title>

    <?php echo $this->html->assets('css'); ?>
    <?php echo $this->html->assets('js'); ?>

    <script>
        Tip.SERVER_ROOT = '<?php echo $h(\lithium\net\http\Router::match('', $this->_request)); ?>';
        console.log('Tip.SERVER_ROOT',Tip.SERVER_ROOT);
    </script>





</head>

<body id="page" class="<?php echo $h(Util::nvlA($this->_data,"$_control_.bodyClasses")); ?>">
<!-- loading animation -->
    <div id="qLoverlay"></div>
    <div id="qLbar"></div>


    <?php echo $this->layout->element('header'); ?>
    <div id="wrapper">
<!--Responsive navigation button-->
<?php echo $this->layout->element('sidebar'); ?>

    <div class="clearfix" id="<?php echo $h(Util::nvlA($this->_data,"$_control_.fullScreenMode") ? "" : "content"); ?>" <?php
            echo "style=\"";
            if (Util::nvlA($this->_data,"$_control_.elements.sidebar.visible") === false)echo " margin-left:0;";
            if (Util::nvlA($this->_data,"$_control_.elements.navbar.visible") === false)echo " margin-top:30px;";
            echo "\"";
            ?>
    >
        <div class="contentwrapper"><!--Content wrapper-->
        <?php echo $this->layout->element('navbar'); ?>
        <!-- Content -->
        <div id="innercontent">
        <div class="row-fluid" > <div id="alerts" class="span12" style="min-height:0"></div> </div>
        <?php echo $this->html->assets('hb'); ?>
        <?php echo $this->content(); ?>
        </div>
        </div> <!-- #contentwrapper -->
    </div>
    </div> <!-- #wrapper -->
    <?php echo $this->layout->element('footer'); ?>


</body>
</html>
 
