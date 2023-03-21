<?php
//setting up error message
$ERROR_MESSAGE = '';

$dir = plugin_dir_path( __FILE__ );

$theme = wp_get_theme(); // gets the current theme

//checking for theme if divi is using or not then performing other task
if ( 'Divi' == $theme->name || 'Divi' == $theme->parent_theme ){
   //uncomment token for login feature
   //$validity = get_option('dvam_token');
   //if($validity == ''){
   //   wp_safe_redirect( admin_url( 'admin.php?page=screenideaz' ) );
   //   exit;
   //}
   //else{
      include_once($dir.'/modules/childtheme.php');
      include_once($dir.'/modules/layouts.php');
      include_once($dir.'/modules/modules.php');
      include_once($dir.'/modules/plugins.php');
      include_once($dir.'/modules/sections.php');
   //}
}


?>
<!-- tab headers -->

<ul class="nav nav-tabs" role="tablist">

   <li class="nav-item">
      <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">Child Themes</a>
   </li>
   <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab">Layouts</a>
   </li>
   <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab">Plugins</a>
   </li>
   <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#tabs-4" role="tab">Sections</a>
   </li>
   <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#tabs-5" role="tab">Modules</a>
   </li>
   
</ul>


<!-- Tab panes -->
<div class="tab-content">
   <div class="tab-pane active" id="tabs-1" role="tabpanel">
	   <?php echo $child_theme;?>
   </div>
   <div class="tab-pane" id="tabs-2" role="tabpanel"> 
      <?php echo $layouts;?>
   </div>
   <div class="tab-pane" id="tabs-3" role="tabpanel">
      <?php echo $plugins;?>
   </div>
   <div class="tab-pane" id="tabs-4" role="tabpanel">
      <?php echo $sections;?>
   </div>
   <div class="tab-pane" id="tabs-5" role="tabpanel">
      <?php echo $modules;?>
   </div>
</div>
