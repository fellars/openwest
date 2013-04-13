<?php
use lithium\util\String;
if(isset($visible) && !$visible)return;
$title = isset($title) ? $title : '';
if(!$title && (!isset($items) || !$items)){
    echo "<br/>";//give 1 line of buffer
    return;//nothing to show here
}

?>
<div class="heading navbar ">
  <div class="navbar-inner">
      <?php if($title){?><span class="brand"><?php echo $h($title); ?></span><?php } ?>

        <ul class="nav">
          <?php
           if(isset($items)){
               foreach($items as $item){
                   $item += array('active'=>'','link'=>'','label'=>'');
                   $item['link'] = $this->html->link($item['label'],$item['link']);
                   if($item['active'])$item['active'] = 'active';
                   echo String::insert('<li class="{:active}">{:link}</li>',$item);
               }
            } 
          ?>
        </ul>

  </div><!-- /navbar-inner -->
</div> <!-- /navbar -->
