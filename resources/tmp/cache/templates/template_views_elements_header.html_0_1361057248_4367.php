<?php
use lithium\core\Environment;

if(isset($visible) && !$visible)return;

$showLogout = ($loggedIn = \tip\models\Users::current()) && !$loggedIn->isPublic();
$showProperties = $loggedIn && !$loggedIn->isPublic() && \tip\controls\Screen::current()->showProperties();

$logo = "";
$defaultLogo = '&nbsp;';
if(isset($hideLogo) && $hideLogo){
    $logo = '&nbsp;';
}
if(!$logo && $account = \tip\models\Accounts::current()){
    if($account->tData('/logo')){
        $logo = $account->logoImg();
    }else{
        $defaultLogo = $account->name;
    }
}
if(!$logo){
    if($global = Environment::get('server_logo')){
        $logo = "<img src='{$global}'/>";
    }else if($defaultLogo === '&nbsp;'){
        $defaultLogo = Environment::get('server_owner');
    }
}
if(!$logo){
    $logo = $defaultLogo;
}
?>
<div id="header">

        <div class="navbar">
            <div class="navbar-inner">
              <div class="container-fluid">
                  <a href="/" class="brand"><?php echo $logo; ?></a>

<?php

           if( $showLogout ){
?>
          <ul class="nav pull-right" id="right-nav">
            <?php if($showProperties){
                $currProperty = \tip\models\Users::current()->currentProperty();
                $properties = \tip\controls\Screen::current()->properties();

                if(count($properties) > 0){//only show if we have any
               $selected = '--Select Property --';
              $propsInner = '';
                foreach($properties as $prop){
                   $id = $prop->id();
                   $name = $prop->name;
                   $selected = ($id == $currProperty) ? $name : $selected;
                    $propsInner .= "<li><a href=\"#\" data-property=\"$id\" data-property-select=\"top\" >$name</a></li>";
                }
              ?>
                <script>
                  $(document).ready(function(){

                       $('div.top-property a[data-property-select="top"]').click(function(e){
                          var el = $(e.currentTarget);
                           $('li.dropdown').removeClass('open');
                           if($('div.top-property .current-property').text() != el.html()){
                               $('div.top-property .current-property').text(el.html())
                               var newProp = $(el).data('property');
                               $.ajax('/screen/_screen_/_property_/'+newProp,{success:function(){location.reload(true)}});
                           }
                      });

                  });
               </script>
              <?php
                $props = '<div style="margin-top:5px" id="property-dropdown" class="top-property btn-group"><button data-toggle="dropdown" class="btn btn-greybar dropdown-toggle" style="padding-bottom:3px"><i class="icon-filter"></i>&nbsp;<span class="current-property">' . $selected . '</span>&nbsp;<b class="caret"></b></button>';
                $props .= '<ul class="dropdown-menu">';
                $props .= $propsInner;
                $props .= "</ul></div>";
                echo $props;
                }
                $username = $loggedIn->name(false);
                $userMenu = '<div style="margin-top:5px" class="btn-group top-user" id="user-dropdown"><button data-toggle="dropdown" class="btn btn-greybar dropdown-toggle" style="padding-bottom:3px"><i class="icon-user"></i>&nbsp;<span class="username">' . $username . '</span>&nbsp;<b class="caret"></b></button>';
                $userMenu .= '<ul class="dropdown-menu">';
                $userMenu .= "<li>" . $this->html->link(' Profile','/_core/door/profile',array('escape'=>false)) . "</li>";
                $userMenu .= "<li>" . $this->html->link(' Logout','/_core/door/logout',array('escape'=>false)) . "</li>";
                $userMenu .= "</ul></div>";
                echo $userMenu;


          }
?>

          </ul>
<?php
           }else if(isset($showLogin) && $showLogin ){
?>

               <ul class="nav pull-right">
                   <li class="link-logout"><?php echo $this->html->link('Login','/_core/door/login'); ?></li>
                 </ul>
<?php
           }
?>
      </div>
    </div><!-- /navbar-inner -->

</div> <!-- /#navbar -->




      </div><!-- End #header -->