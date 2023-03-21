<?php

//download and install theme functionalities. only admin can perform the task

function install_child_theme(){

    if($_POST['action'] == 'install_child_theme') {
        $theme_download_url = $_POST['download'];
        //$theme_download_name = $_POST['name'];
    
        set_time_limit(0);

        $url = $theme_download_url;
        $upload = wp_upload_dir();
        $tmppath = $upload['basedir']."/theme.zip";
        $themdir = WP_CONTENT_DIR."/themes";
        $fp = fopen ($tmppath, 'w+');//This is the file where we save the zip file

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch); // get curl response
        curl_close($ch);
        fclose($fp);


        if (file_exists($tmppath)){
            $zip = new ZipArchive;
            $res = $zip->open($tmppath);
            if ($res === TRUE)
            {
                $zip->extractTo($themdir);
                $zip->close();
                echo 'Theme file has been extracted.';
                unlink($tmppath);
            }
            else
            {
                echo 'There was a problem opening the theme zip file: '.$res;
            }
        }
        else{
            echo("There was an error downloading, writing or accessing the theme file.");
        }
      }
      wp_die();

}


  


