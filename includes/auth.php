<?php
    //setting up some variable
    $ERROR_MESSAGE = '';
    $verifyer = '';
    $code = 0;

if(isset($_POST['submit'])){

    //calling api for login verification | host website should use Jwt auth plugin
    $username = $_POST['username'];
    $password = $_POST['password'];
    $url = 'https://screenideaz.com/wp-json/jwt-auth/v1/token?';

    // ************* Call API:
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1); // set post data to true
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
    curl_setopt($ch, CURLOPT_POSTFIELDS,'username='.$username.'&password='.$password);   // post data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $json = curl_exec($ch);
    curl_close ($ch);
    $obj = json_decode($json);

    //checking for error code
    if (!empty($obj -> {'data'} -> {'status'})) {
        $code = $obj -> {'data'} -> {'status'};
    }
    if( $code == 403){
        $ERROR_MESSAGE = 'Your Credentials are wrong! Try again';
    }
    //storing token to database
    elseif(!empty($obj -> {'token'})){
        $verifyer = $obj -> {'token'};
        update_option( 'dvam_token', $verifyer );
        echo '<script>
		location.reload();
		</script>';
    }
    else{
        $ERROR_MESSAGE = 'Something went wrong. Contact plugin author!';
    }
}

