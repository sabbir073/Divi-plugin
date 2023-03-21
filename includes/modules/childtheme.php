<?php

//All child theme functionalities
$title = "";
$image = "";
$url = "";

//api call for getting the themes
$apiUrl = 'https://screenideaz.com/wp-json/wp/v2/divilayoutlibrary?divilayoutcategory=43&_embed';
$response = wp_remote_get($apiUrl);
$responseBody = wp_remote_retrieve_body( $response );
$result = json_decode( $responseBody );
if ( is_array( $result ) && ! is_wp_error( $result ) ) {

   $thumb_url = '';
   $child_theme = '';
   $themes = array();
   $all_themes = wp_get_themes();
   $current_theme = wp_get_theme();
   foreach ($all_themes as $theme) {
      $themes[] = $theme->get('Name');
   }
	foreach( $result as $item ) {
      if ( ! empty( $item->featured_media ) && isset( $item->_embedded ) ) {
         $thumb_url = $item->_embedded->{'wp:featuredmedia'}[0]->media_details->sizes->medium->source_url;
         
     }
      $download_url = $item->acf->layoutfile;
      $preview_url = $item->acf->preview_url;
      $theme_directory_name = $item->acf->theme_directory_name;
      $name_theme = $item-> title->rendered;
      if(in_array($name_theme,$themes)){
         if ( $name_theme == $current_theme->name) {
            $link_html = '<a class="button activate disabled" href="#">Activated</a>';
         }
         else{
            $link_html = '<a class="button activate activate_theme" data-name="'.$theme_directory_name.'" href="#">Activate</a>';
         }
         
      }
      else{
         $link_html = '<a class="button activate install_button" href="" data-download="'.$download_url.'" data-name="'.$name_theme.'">Install</a>';
      }

      $child_theme .=  '<div class="theme" tabindex="0" aria-describedby="twentynineteen-action twentynineteen-name" data-slug="twentynineteen">
      <div class="theme-screenshot">
         <img src="'.$thumb_url.'" alt="">
      </div>
      <div class="theme-id-container">
         <h2 class="theme-name" id="twentynineteen-name">'.$name_theme.'</h2>
         <div class="theme-actions">
            '.$link_html.'
            <a class="button button-primary load-customize hide-if-no-customize" target="_blank" href="'.$preview_url.'">Live Preview</a>
         </div>
      </div>
   </div>';
		
	}
	

} else {
    echo 'not getting result';
}
?>
