<?php
/**
* @package ScreenIdeaz
*/
/*
Plugin Name:  Divi ScreenIdeaz
Plugin URI:   https://amicritas.com/plugins
Description:  Simple plugin
Version:      2.0.0
Author:       Md Sabbir Ahmed.
Author URI:   https://amicritas.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  ScreenIdeaz
*/

defined( 'ABSPATH' ) or die( 'Hey! You can not access to this' );

/**
* ScreenIdeaz Plugin Class
*/

class ScreenIdeaz
{
    //constructing all functionalities directly
    function __construct(){
       
        
    }

    //Registering all functionalities
    function registerAllAction(){
        add_action( 'admin_menu', array($this,'AddoptionToadmin') );
        
        add_action('admin_enqueue_scripts',array($this,'AdminStyleandScript'));

        add_action( 'admin_init', array($this,'redirect_after_installation') );

        //enable this token if you want to use login feature
        //add_option( 'dvam_token', '' );

        add_action('wp_ajax_install_child_theme', 'install_child_theme');
        add_action('wp_ajax_nopriv_install_child_theme', 'install_child_theme');

        add_action('wp_ajax_activate_child_theme', 'activate_child_theme');
        add_action('wp_ajax_nopriv_activate_child_theme', 'activate_child_theme');

        add_action( 'wp_ajax_import_divi_layout', 'import_divi_layout' );
        add_action('wp_ajax_nopriv_import_divi_layout', 'import_divi_layout');
        
    }

   
    /**
    * Plugin activation
    */
    function activation(){

        //adding options menu
        $this->AddoptionToadmin();

        //adding ridirect option
        add_option( 'redirect_after_installation', true );

        //flush rewrite rules
		flush_rewrite_rules();
    }

    /**
    * Plugin Deactivation
    */
    function deactivation(){

        //delete_option( 'dvam_token' );
        //flush rewrite rules
		flush_rewrite_rules();        
    }

    /**
    * Plugin Uninstallation
    */
    function uninstall(){


	}

    //add option menu fuction
    function AddoptionToadmin(){
        add_menu_page( 'Divi Screenideaz', 'Divi Screenideaz', 'manage_options', 'screenideaz', 'AmicritasOptionPage', 'dashicons-move', 90 );
    }

    //All style and scripts
    function AdminStyleandScript($HOOK){
        if('toplevel_page_screenideaz' !== $HOOK ){
            return;
        }
        wp_register_style( 'admin-styles',  plugin_dir_url( __FILE__ ) . 'css/style.css' );
	    wp_enqueue_style( 'admin-styles' );

        wp_register_style('dvam_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');
        wp_enqueue_style('dvam_bootstrap');

        wp_enqueue_script('dvamjs_loading',plugin_dir_url( __FILE__ ) . 'js/modalLoading.min.js');

        wp_enqueue_script('dvamjs_jquery','//cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js', array(), '3.6.3', true);
        wp_enqueue_script('dvamjs_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js', array( 'dvamjs_jquery' ), false, true);


        wp_register_script( 'custom-js', plugin_dir_url( __FILE__ ) . '/js/scripts.js', array(), '2.2.0', true );
        $arr = array(
            'ajaxurl' => admin_url('admin-ajax.php')
        );
        wp_localize_script('custom-js','obj',$arr );
        wp_enqueue_script('custom-js');


    }

    //Redirect to plugin page after installation
    function redirect_after_installation() {
	
        if ( get_option( 'redirect_after_installation', false ) ) {
            
            delete_option( 'redirect_after_installation' );
    
            wp_safe_redirect( admin_url( 'admin.php?page=screenideaz' ) );
    
            exit;
        }
    }

}
//install call of child themes

require_once(plugin_dir_path( __FILE__ ) . '/includes/modules/install.php');

require_once(plugin_dir_path( __FILE__ ) . '/includes/modules/activate.php');

//Plugin page view
function AmicritasOptionPage(){
    require_once(plugin_dir_path( __FILE__ ) . '/includes/amicritas.php');
}


//calling the base class
if (class_exists('ScreenIdeaz')) {

	$amicritas = new ScreenIdeaz();
    $amicritas->registerAllAction();

}

// Activation

register_activation_hook(__FILE__, array($amicritas, 'activation' ));

// Deactivation

register_deactivation_hook(__FILE__, array($amicritas, 'deactivation'));

// Uninstall


// AJAX function to import a Divi layout file
function import_divi_layout(){
    $theme = wp_get_theme();
    if ( 'Divi' == $theme->name || 'Divi' == $theme->parent_theme ){
        $layout_url = $_POST['download_url'];

        // get the layout file content
    	$response = wp_remote_get( $layout_url );
        $layout_content = wp_remote_retrieve_body( $response );

        $layout_data = json_decode($layout_content, true);

        if ( ! empty( $layout_data ) ) {
            require_once 'includes/import-post.php';
            $import = new Screenideaz_import();
            echo $import->post_import( $layout_data );
            exit;
        }

    }
    else{
		wp_send_json_error( 'Not using Divi theme' );
	}
}

