<?php

//declaring error msg
$ERROR_MESSAGE = '';

//Getting plugin dir 
$dir = plugin_dir_path( __FILE__ );

?>
<div class="amicritas-title">
    <center><h1 class="amititle">Welcome to Divi ScreenIdeaz v2.0</h1></center>
</div>

<?php
$theme = wp_get_theme(); // gets the current theme
//checking if the divi theme installed or not
if ( 'Divi' == $theme->name || 'Divi' == $theme->parent_theme ) {

    //uncomment token if you want to use login features
    //getting token field from database
    //$validity = get_option('dvam_token');
    //if($validity !== ''){
        include_once($dir.'dashboard.php');
    //}
    //else{
     //   include_once($dir.'auth.php');

        ?>
<!-- For login system work, host website has to enable anyone can register option from setting-->
         <!--   <div class="logincheck"><p>You are not logged in, Please login</p></div>

            <form method="post" action="">
                    <div class="login">
                    <input type="text" placeholder="Username" id="username" name="username">  
                    <input type="password" placeholder="password" id="password" name="password">  -->
                    <!-- setting up host website forget password link -->
                   <!-- <a target="_blank" href="https://screenideaz.com/wp-login.php?action=lostpassword" class="forgot">forgot password?</a>
                    <input type="submit" name="submit" value="Sign In">
                    <div class="signup">
                    <h4 style="color:#C70039;"><?php echo $ERROR_MESSAGE;?></h4>
                    <p>Don't have an account?</p>-->
                    <!-- Setting up host registration link from here -->
                <!--    <a target="_blank" href="https://screenideaz.com/wp-login.php?action=register">Sign Up</a>
                    </div>
                    </div>
                    </form>
                <div class="shadow"></div>-->
    
        <?php
    //}
}
else{
    $image = plugin_dir_url( __FILE__ ) . 'img/sorry.jpg';
?>
 <div class="logincheck">
     <p>You are not Using Divi theme. This plugin only works with Divi Theme.</p>
     <img src="<?php echo $image;?>" />
</div>
<?php
}
?>


