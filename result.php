<?php
//for IE iFrame 3rd party cookie blocking 
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
require_once('AppInfo.php');	// contains appID, SECRET and URL

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
 
  header('Location: https://google.com');
    trigger_error("Cannot establish a secure connection using HTTPS", E_USER_NOTICE);
 
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

require_once('utils.php');		// global array and html helper functions
require_once('sdk/src/facebook.php');	// fb API

$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
  'sharedSession' => true,
  'trustForwarded' => true,
));

// viewer's info
$user_id = $facebook->getUser();
if ($user_id) {
  try {
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    if (!$facebook->getUser()) {
	
	header('Location: https://renren.com');
	
		trigger_error("Cannot get user ID", E_USER_NOTICE);
  
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }
	
	//graph API to retrieve likes, friends and photos
  $likes = idx($facebook->api('/me/likes?limit=4'), 'data', array());
  $friends = idx($facebook->api('/me/friends?limit=4'), 'data', array());
  $photos = idx($facebook->api('/me/photos?limit=16'), 'data', array());
  //FQL
  $app_using_friends = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
  ));
}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());
$app_name = idx($app_info, 'name', '');





/*
define("DB_HOST","mysql13.000webhost.com");
define("DB_USER","a2110984_sta286");
define("DB_PASS","3q3PDmGx");
define("DB_NAME","a2110984_sta286");

// establish connection to individual database
if(!defined('DB_HOST')) {
	die("ERROR: config.php not configured.  Please run <a href='install.php'>install</a>.");
}
$con=mysqli_connect(DB_HOST, DB_USER, DB_PASS) or die("FATAL ERROR: Unable to connect to MySQL database.");
mysqli_select_db($con, DB_NAME) or die("FATAL ERROR: Unable to select database " . DB_NAME);
mysqli_set_charset($con, "utf8");


	mysqli_query($con, "INSERT INTO typingtest (textEntered)	
	VALUES (N'a bc')") or die (mysqli_error($con));
			
mysqli_close($con);
	*/


?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />

    <title><?php echo he($app_name); ?></title>
    <link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" />
    <link rel="stylesheet" href="stylesheets/mobile.css" media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" type="text/css" />

    <!--[if IEMobile]>
    <link rel="stylesheet" href="mobile.css" media="screen" type="text/css"  />
    <![endif]-->

    <!-- These are Open Graph tags.  They add meta data to your  -->
    <!-- site that facebook uses when your content is shared     -->
    <!-- over facebook.  You should fill these tags in with      -->
    <!-- your data.  To learn more about Open Graph, visit       -->
    <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
    <meta property="og:title" content="<?php echo he($app_name); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
    <meta property="og:image" content="<?php echo AppInfo::getUrl('/logo.png'); ?>" />
    <meta property="og:site_name" content="<?php echo he($app_name); ?>" />
    <meta property="og:description" content="My first app" />
    <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />

    <script type="text/javascript" src="/javascript/jquery-1.7.1.min.js"></script>

    <script type="text/javascript">
      function logResponse(response) {
        if (console && console.log) {
          console.log('The response was', response);
        }
      }

      $(function(){
        // Set up so we handle click on the buttons
        $('#sendRequest').click(function() {
          FB.ui(
            {
              method  : 'apprequests',
              message : $(this).attr('data-message')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });
      });
    </script>

    <!--[if IE]>
      <script type="text/javascript">
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]-->
  </head>
  <body>
    <div id="fb-root"></div>


      <?php if (isset($basic)) { ?>
     
      <div>
       

<table class="background" width="400px">
	<tr>
		<div id="welcome_msg">
		<h1>Welcome to the typing-speed competition v2.0</h1>  
		<br/>
		</div>
	</tr>
	<tr>
		<div id="author">
		<h6>---- By Aaron Wang</h6>
		</div>
	</tr>
		
	<tr>
	<div class="input_text" id= "input_text"><br/>
		<table>
			<tr>
				<td>
					TIME:
				</td>
				<td id="time_value">
					0
				</td>
			</tr>
			<tr>
				<td>
					WORDS:
				</td>
				<td id="words">
					0
				</td>
			</tr>
		</table>
		</p></div>
	</tr>
	
	<tr>
	<div class="horizontal_list">
        <h3>Top players of your friends</h3>
        <ul class="friends">
          <?php
            foreach ($app_using_friends as $auf) {
              // Extract the pieces of info we need from the requests above
              $id = idx($auf, 'uid');
              $name = idx($auf, 'name');
          ?>
          <li>
		  	<div>
            <a href="https://www.facebook.com/<?php echo he($id); ?>" target="_top">
              <img src="https://graph.facebook.com/<?php echo he($id) ?>/picture?type=square" alt="<?php echo he($name); ?>"><br/>
              <?php echo he($name); ?>
            </a>
			</div>
          </li>
          <?php
            }
          ?>
        </ul>
      </div>
	  <input type="button" id="sendRequest" value="submit" data-message="I want to compete typing speed with you"></input>
	<tr/>	
	<tr>
	<div id="share-app">
          
              <a href="#" class="facebook-button apprequests" id="sendRequest" data-message="I want to compete typing speed with you">
                <span class="apprequests">Invite your friends to compete~</span>
              </a>
    </div>
	<tr/>
</table>




<br/>

	   <p class="tagline">
          This is my app
          <a href="<?php echo he(idx($app_info, 'link'));?>" target="_top"><?php echo he($app_name); ?></a>
        </p>

        <div id="share-app">
          
              <a href="#" class="facebook-button apprequests" id="sendRequest" data-message="Test this awesome app">
                <span class="apprequests">Share this app with your friends!</span>
              </a>
        </div>
      </div>
	  
	  
      <?php } else { ?>
      <div>
        <h1>Welcome to typing test competition v2.0!</h1>
		<br/>
        <div class="fb-login-button" data-scope="user_likes,user_photos"></div>
      </div>
      <?php } ?>

    <?php
      if ($user_id) {
    ?>

    <section id="samples" class="clearfix">
      <h1>Examples of the Facebook Graph API</h1>

      <div class="list">
        <h3>A few of your friends</h3>
        <ul class="friends">
          <?php
            foreach ($friends as $friend) {
              // Extract the pieces of info we need from the requests above
              $id = idx($friend, 'id');
              $name = idx($friend, 'name');
          ?>
          <li>
            <a href="https://www.facebook.com/<?php echo he($id); ?>" target="_top">
              <img src="https://graph.facebook.com/<?php echo he($id) ?>/picture?type=square" alt="<?php echo he($name); ?>">
              <?php echo he($name); ?>
            </a>
          </li>
          <?php
            }
          ?>
        </ul>
      </div>

      <div class="list inline">
        <h3>Recent photos</h3>
        <ul class="photos">
          <?php
            $i = 0;
            foreach ($photos as $photo) {
              // Extract the pieces of info we need from the requests above
              $id = idx($photo, 'id');
              $picture = idx($photo, 'picture');
              $link = idx($photo, 'link');

              $class = ($i++ % 4 === 0) ? 'first-column' : '';
          ?>
          <li style="background-image: url(<?php echo he($picture); ?>);" class="<?php echo $class; ?>">
            <a href="<?php echo he($link); ?>" target="_top"></a>
          </li>
          <?php
            }
          ?>
        </ul>
      </div>

      <div class="list">
        <h3>Things you like</h3>
        <ul class="things">
          <?php
            foreach ($likes as $like) {
              // Extract the pieces of info we need from the requests above
              $id = idx($like, 'id');
              $item = idx($like, 'name');

              // This display's the object that the user liked as a link to
              // that object's page.
          ?>
          <li>
            <a href="https://www.facebook.com/<?php echo he($id); ?>" target="_top">
              <img src="https://graph.facebook.com/<?php echo he($id) ?>/picture?type=square" alt="<?php echo he($item); ?>">
              <?php echo he($item); ?>
            </a>
          </li>
          <?php
            }
          ?>
        </ul>
      </div>

      <div class="list">
        <h3>Friends using this app</h3>
        <ul class="friends">
          <?php
            foreach ($app_using_friends as $auf) {
              // Extract the pieces of info we need from the requests above
              $id = idx($auf, 'uid');
              $name = idx($auf, 'name');
          ?>
          <li>
            <a href="https://www.facebook.com/<?php echo he($id); ?>" target="_top">
              <img src="https://graph.facebook.com/<?php echo he($id) ?>/picture?type=square" alt="<?php echo he($name); ?>">
              <?php echo he($name); ?>
            </a>
          </li>
          <?php
            }
          ?>
        </ul>
      </div>
    </section>

    <?php
      }
    ?>
		
	    <script type="text/javascript">	
alert( "time = " + sessionStorage.getItem("time"));

document.getElementById("time_value").innerHTML=sessionStorage.getItem("time");
document.getElementById("words_value").innerHTML=sessionStorage.getItem("words");
document.getElementById("charactersEntered_value").innerHTML=sessionStorage.getItem("charactersEntered");
document.getElementById("wrongChracters_value").innerHTML=sessionStorage.getItem("wrongCharacters");
document.getElementById("speed_value").innerHTML=sessionStorage.getItem("speed");
document.getElementById("errPercentage_value").innerHTML=sessionStorage.getItem("errPercentage");
	
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?php echo AppInfo::appID(); ?>', // App ID
          channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true // parse XFBML
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

      // Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>	

  </body>
</html>
