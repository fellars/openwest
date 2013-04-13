<div class="row-fluid">
    <?php
        $gridSize = $this->html->controlData('gridSize');
        $spacer = $gridSize < 12 ? '<div class="span' . ((12-$gridSize)/2) . '">&nbsp;</div>'  :'';
    ?>
    <?php echo $spacer;?>
    <div id="theGrid" class="span<?php echo $h($gridSize); ?>">
    	    <!-- blockhead will go here -->
    </div>
    <?php echo $spacer;?>


</div><!-- end .row-fluid -->
<script>


Tip.App = new Tip.Application({

});


Tip.App.addInitializer(function(){


});

<!-- left nav -->
<?php echo $this->js->view('blockGrid','theGrid' ); ?>



Tip.App.start();




</script>

