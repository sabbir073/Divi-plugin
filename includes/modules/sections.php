<?php

//All sections tab functionalities. Getting from api
$title = "";
$image = "";
$url = "";

$apiUrl = 'https://screenideaz.com/wp-json/wp/v2/divilayoutlibrary?divilayoutcategory=46&_embed';
$response = wp_remote_get($apiUrl);
$responseBody = wp_remote_retrieve_body( $response );
$result = json_decode( $responseBody );
if ( is_array( $result ) && ! is_wp_error( $result ) ) {


   $thumb_url = '';
   $sections = '';
   
	foreach( $result as $item ) {
      if ( ! empty( $item->featured_media ) && isset( $item->_embedded ) ) {
         $thumb_url = $item->_embedded->{'wp:featuredmedia'}[0]->media_details->sizes->medium->source_url;
     }

      $download_url = $item->acf->layoutfile;
      $preview_url = $item->acf->preview_url;

      $sections .=  '<div class="theme" tabindex="0" aria-describedby="twentynineteen-action twentynineteen-name" data-slug="twentynineteen">
      <div class="theme-screenshot">
         <img src="'.$thumb_url.'" alt="">
      </div>
      <div class="theme-id-container">
         <h2 class="theme-name" id="twentynineteen-name">'.$item-> title->rendered.'</h2>
         <div class="theme-actions">
         <a class="button activate import-layouts" href="#" data-item-type="section" data-item-title="'.$item-> title->rendered.'" data-download-url="'.$download_url.'" aria-label="Activate Twenty Nineteen">Import</a>
            <a class="button button-primary load-customize hide-if-no-customize" target="_blank" href="'.$preview_url .'">Live Preview</a>
         </div>
      </div>
   </div>';
		
	}
	

} else {
    echo 'not getting result';
}
?>