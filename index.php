<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require 'sdk/src/facebook.php';
require_once('db.php');
require_once('config.php');

date_default_timezone_set('America/New_York');

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => 'APP_ID',
  'secret' => 'APP_SECRET',
));

$fbdb = new db( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST ); 

$result = $fbdb->query("SELECT access_token FROM facebook_data WHERE ID = 1");
$access_token = mysql_result($result, 0);
$facebook->setAccessToken($access_token);

// Get User ID
$user = $facebook->getUser();

// We may or may not have this data based on whether the user is logged in.
//
// If we have a $user id here, it means we know the user is logged into
// Facebook, but we don't know if the access token is valid. An access
// token is invalid if the user logged out of Facebook.

if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
  $user_posts = $facebook->api('me/posts');
}

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>php-sdk</title>
  <link rel="stylesheet" href="facebook.css" type="text/css" /> 
  </head>
  <body>
    <div id="stream">
<?php foreach($user_posts['data'] as $post){  
  $post_link = $post['actions'][0]['link'];
  $page_id = $post['from']['id'];
  $page_name = $post['from']['name'];
  $message = ($post['message']) ? $post['message'] : " ";
  $name = ($post['name']) ? $post['name'] : " ";
  $story = ($post['story']) ? $post['story'] : " ";
  $post_time = $post['updated_time'];
?>
      <div class="post">    
        <div class="picture">
          <a href="http://facebook.com/<?php echo $page_id; ?>"><img src="http://graph.facebook.com/<?php echo $page_id; ?>/picture?type=square"/></a>
        </div>
        <div class="body">
          <a href="http://facebook.com/<?php echo $page_id; ?>" class="actor"><?php echo $page_name; ?></a>
          <span class="message"><?php echo $message; ?></span><br>
          <span class="message"><?php echo $name; ?></span><br>
          <span class="message"><?php echo $story; ?></span>
          <div class="meta">
            <a href="<?php echo $post_link ?>" class="permalink"><?php echo date("F j g:i a", strtotime($post_time)); ?></a>
          </div>
        </div>
      </div>
<?php } ?>
    </div><!-- end #stream -->
  </body>
</html>
