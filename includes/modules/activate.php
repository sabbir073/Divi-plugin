<?php
//activating themes depends on button click veriable

function activate_child_theme(){
    if($_POST['action'] == 'activate_child_theme'){
        $theme_directory_name = $_POST['name'];
        
        update_option('stylesheet', $theme_directory_name);
    
    }
    wp_die();
}



?>