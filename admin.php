<?php
  
// This provides access to helper functions
require_once('sdk/src/facebook.php');
require_once('db.php');
require_once('config.php');

$facebook = new Facebook(array(
  'appId'  => "APP_ID",
  'secret' => "APP_SECRET",
));

$fbdb = new db( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );  

$user_id = $facebook->getUser();

if ($user_id) {
  try {
     // Fetch the viewer's basic information
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // If the call fails we check if we still have a user. The user will be
    // cleared if the error is because of an invalid accesstoken
    error_log($e);
    if (!$facebook->getUser()) {
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit();
    }
  }

  $facebook->setExtendedAccessToken();

  $app_token_url = "https://graph.facebook.com/oauth/access_token?"
                . "client_id=" . $facebook->getAppId()
                . "&client_secret=" . $facebook->getAppSecret() 
                . "&grant_type=client_credentials";

  $response = file_get_contents($app_token_url);
  $app_access_token = null;
  parse_str($response, $app_access_token);  

  $input_token = $facebook->getAccessToken();


  $access_token_url = "https://graph.facebook.com/debug_token?"
                . "input_token=" . $input_token
                . "&access_token=" . $app_access_token['access_token'];
  
  $response = file_get_contents($access_token_url);
  $access_token = null;
  $access_token = json_decode($response, true);
  $expiry_date = $access_token['data']['expires_at'];


  $page_info = $facebook->api('/113702895386410?fields=access_token');
  $page_token = $page_info['access_token'];

  $fbdb_result = $fbdb->query("UPDATE facebook_data SET access_token='" . $page_token . "' WHERE ID = 1");

}

?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>
    <meta charset="utf-8" />
  <link rel="stylesheet" href="facebook.css" type="text/css" />
  <title>My Posts</title>
  </head>
  <body>
    <div class="container">
      <div id="fb-root"></div>
      <script type="text/javascript">
        window.fbAsyncInit = function() {
          FB.init({
            appId      : 'APP_ID', // App ID
            channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
            status     : true, // check login status
            cookie     : true, // enable cookies to allow the server to access the session
            xfbml      : true // parse XFBML
          });
        
          FB.getLoginStatus(function(response) {
            if (response.status === 'connected') {
            // connected
            } else if (response.status === 'not_authorized') {
            // not_authorized
              login();
            } else {
            // not_logged_in
              login();
            }
          });

          // Listen to the auth.login which will be called when the user logs in
          // using the Login button
          FB.Event.subscribe('auth.login', function(response) {
            // We want to reload the page now so PHP can read the cookie that the
            // Javascript SDK sat. But we don't want to use
            // window.location.reload() because if this is in a canvas there was a
            // post made to this page and a reload will trigger a message to the
            // user asking if they want to send data again.
            window.location = window.location;
          });

          FB.Canvas.setAutoGrow();
        };

        function login() {
          FB.login(function(response) {
            if (response.authResponse) {
                 // connected
            } else {
                 // cancelled
            }
          }, {scope: 'manage_pages'});
        }  

        // Load the SDK Asynchronously
        (function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/en_US/all.js";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
      </script>
      <fb:name uid="loggedinuser" useyou="false" linked="true"></fb:name>
      <fb:profile-pic uid="loggedinuser" size="square" facebook-logo="true"></fb:profile-pic>
      <?php if ($user_id) { ?>
      <p>The access token info</p>
      <pre>
      <?php print_r($access_token); ?>
      </pre>  
      <p>Expires: <?php echo date("D, d M Y H:i:s", $expiry_date); } ?></p>
    </div>
  </body>
</html>

