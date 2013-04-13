<?php
use tip\core\Util;
use tip\traits\CoreAppTrait;
if(isset($visible) && !$visible)return;
if(($user = \tip\models\Users::current()) === null || $user->isPublic())return;//no sidebar since not logged in or public gateway
$favorites = (array)$user->core()->data('favoriteApps');

$recents = (array)$user->core()->data('recentApps');
$mediaApp = \tip\models\Media::mediaAppClass();
?>
<script>
$(document).ready(function() {
    //$('#global-sidebar a').tooltip({placement:'right',title:'App'});
});
</script>
<!--Sidebar collapse button-->
<div class="resBtn">
    <a href="#"><span class="icon16 minia-icon-list-3"></span></a>
</div>
        <div class="collapseBtn leftbar">
             <a href="#" class="tipR" title="Hide sidebar"><span class="icon12 minia-icon-layout"></span></a>
        </div>

        <!--Sidebar background-->
        <div id="sidebarbg"></div>
        <!--Sidebar content-->
        <div id="sidebar">

            <div class="shortcuts">
                <ul>
                    <li><?php echo $this->html->link('<span class="sidebar-icon"><i class="icon-home icon-white icon24"></i></span>','/_core/home/go/',array('rel'=>"tooltip",'title'=>'Home','escape'=>false)); ?></li>
            		<li><?php echo $this->html->link('<span class="sidebar-icon"><i class="icon-user icon-white icon24"></i></span>','/_core/home/go/_core::door/profile/',array('rel'=>"tooltip",'title'=>'Profile','escape'=>false)); ?></li>
                    <li><?php echo $this->html->link('<span class="sidebar-icon"><i class="icon-picture icon-white icon24"></i></span>','/_core/home/go/_core::media/',array('rel'=>"tooltip",'title'=>'Media','escape'=>false)); ?></li>
                    <li><?php echo $this->html->link('<span class="sidebar-icon"><i class="icon-cog icon-white icon24"></i></span>','/_core/home/go/_core::settings/',array('rel'=>"tooltip",'title'=>'Settings','escape'=>false)); ?></li>
                </ul>
            </div><!-- End shortcuts -->

            <div class="sidenav" style="width: 100%;">

                <div class="sidebar-widget" style="margin: -1px 0 0 0;">
                    <h5 class="title" style="margin-bottom:0; margin-top:0;">Navigation</h5>
                </div><!-- End .sidenav-widget -->
                <div class="sidebar-widget" style="margin: -1px 0 0 0;">
                    <h5 class="title" style="margin-bottom:0; margin-top:0;">Favorites</h5>
                </div><!-- End .sidenav-widget -->

                <div class="mainnav">
                    <ul>
                        <?php
                                 if(isset($favorites)){

                                     foreach($favorites as $item){
                                         $cPath = CoreAppTrait::locate("apps",$item);
                                         if(!$cPath)continue;
                        //                 $item += array('active'=>'','link'=>'','text'=>'');
                                         //echo String::insert('<li class="{:active} sidebar-icon"><a href="{:link}" id="properties-settings" title="{:text}">{:text}</a></li>',$item);
                                         list($lib,$name) = Util::parse($item);
//                                         $name = Util::inflect($name,'h');
                                         $app = CoreAppTrait::find(array('app'=>$item),'first');
                                         if($app){
                                             $appIcon = $app->traitData('/icon');
                                             $appImg = $app->traitData('/img');
                                             $name = substr($app->name,0,25);
                                             $icon = $appImg ? '<img src="' . $mediaApp . '/app:icon/'.$item.'/small"/> ' :
                                                '<i class="icon icon-' .($appIcon ?: 'cogs') .'"></i>';
                                             echo "<li>" . $this->html->link('<span class="sidebar-icon"> ' . $icon . $name . '</span>','/_core/home/go/'.$item,array('rel'=>"tooltip",'title'=>$name,'escape'=>false)) . "</li>";
                                         }
                                     }
                                 }
                        ?>

                    </ul>
                </div>
                <div class="sidebar-widget" style="margin: -1px 0 0 0;">
                    <h5 class="title" style="margin-bottom:0; margin-top:0;">Recents</h5>
                </div><!-- End .sidenav-widget -->
                <div class="mainnav">
                    <ul>
                <?php
                 if(isset($recents)){
                     foreach($recents as $item){
                         $cPath = CoreAppTrait::locate("apps",$item);
                         if(!$cPath)continue;

        //                 $item += array('active'=>'','link'=>'','text'=>'');
        //                 echo String::insert('<li class="{:active} app-icon-24"><a href="{:link}" id="properties-settings"  title="{:text}">{:text}</a></li>',$item);
                         list($lib,$name) = Util::parse($item);
//                         $name = Util::inflect($name,'h');
                         $app = CoreAppTrait::find(array('app'=>$item),'first');
                         if($app){
                             $appIcon = $app->traitData('/icon');
                             $appImg = $app->traitData('/img');
                             $name = substr($app->name,0,25);
                             $icon = $appImg ? '<img src="' . $mediaApp . '/app:icon/'.$item.'/small"/> ' :
                                '<i class="icon icon-' .($appIcon ?: 'cogs') .'"></i>';
                             echo "<li>" . $this->html->link('<span class="sidebar-icon"> ' . $icon . $name . '</span>','/_core/home/go/'.$item,array('rel'=>"tooltip",'title'=>$name,'escape'=>false)) . "</li>";
                         }
                     }
                 }
                ?>
                    </ul>
                </div>
            </div><!-- End sidenav -->



        </div><!-- End #sidebar -->
<?php return; ?>
<div id="global-sidebar">
	<ul id="user-menu">
        <!-- <li id="open-control" class="shut"><span class="sidebar-icon"><i class="icon-arrow-right"></i></span></li> -->
        <li><?php echo $this->html->link('<span class="sidebar-icon"><i class="icon-home icon-white"></i></span>','/_core/home/go/',array('rel'=>"tooltip",'title'=>'Home','escape'=>false)); ?></li>
		<li><?php echo $this->html->link('<span class="sidebar-icon"><i class="icon-user icon-white"></i></span>','/_core/home/go/_core::door/profile/',array('rel'=>"tooltip",'title'=>'Profile','escape'=>false)); ?></li>
        <li><?php echo $this->html->link('<span class="sidebar-icon"><i class="icon-picture icon-white"></i></span>','/_core/home/go/_core::media/',array('rel'=>"tooltip",'title'=>'Media','escape'=>false)); ?></li>
        <li><?php echo $this->html->link('<span class="sidebar-icon"><i class="icon-cog icon-white"></i></span>','/_core/home/go/_core::settings/',array('rel'=>"tooltip",'title'=>'Settings','escape'=>false)); ?></li>
        <li><?php echo $this->html->link('<span class="sidebar-icon"><i class="icon-flag icon-white"></i></span>','/_core/home/go/_core::test/',array('rel'=>"tooltip",'title'=>'Test','escape'=>false)); ?></li>
	</ul>
	<ul class="nav list" id="recent-and-favorites">
	    <li class="nav-header"><span class="small-icon-size"><i class="icon-star icon-white"></i></span> Favorites</li>
        <?php
         if(isset($favorites)){
             foreach($favorites as $item){
//                 $item += array('active'=>'','link'=>'','text'=>'');
                 //echo String::insert('<li class="{:active} sidebar-icon"><a href="{:link}" id="properties-settings" title="{:text}">{:text}</a></li>',$item);
                 list($lib,$name) = Util::parse($item);
                 $name = Util::inflect($name,'h');
                 echo "<li>" . $this->html->link('<span class="sidebar-icon"><img src="' . $mediaApp . '/app:icon/'.$item.'/small"/></span>','/_core/home/go/'.$item,array('rel'=>"tooltip",'title'=>$name,'escape'=>false)) . "</li>";
             }
         }
        ?>
	    <li class="nav-header"><span class="small-icon-size"><i class="icon-time icon-white"></i></span> Recent</li>
        <?php
         if(isset($recents)){
             foreach($recents as $item){
//                 $item += array('active'=>'','link'=>'','text'=>'');
//                 echo String::insert('<li class="{:active} app-icon-24"><a href="{:link}" id="properties-settings"  title="{:text}">{:text}</a></li>',$item);
                 list($lib,$name) = Util::parse($item);
                 $name = Util::inflect($name,'h');
                 echo "<li>" . $this->html->link('<span class="sidebar-icon"><img src="' . $mediaApp . '/app:icon/'.$item.'/small"/></span>','/_core/home/go/'.$item,array('rel'=>"tooltip",'title'=>$name,'escape'=>false)) . "</li>";
             }
         }
        ?>
	</ul>
</div>
