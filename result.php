<?php
//for IE iFrame 3rd party cookie blocking 
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
require_once('AppInfo.php');	// contains appID, SECRET and URL

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
 trigger_error("Cannot establish a secure connection using HTTPS", E_USER_NOTICE);
 
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

require_once('utils.php');		// global array and html helper functions
require_once('sdk/src/facebook.php');	// fb API

// construct a new Facebook object of this application
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
		trigger_error("Cannot get user ID", E_USER_NOTICE);  
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }
	
  //FQL to get friends who are using the app
  $app_using_friends = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
  ));
}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());
$app_name = idx($app_info, 'name', '');

// METHOD1: try for user access token ==> works for user access token, but not app access token
//$access_token_user = $facebook->getAccessToken();
//echo "user access token: ".$access_token_user;	// checked to be good
/*
//METHOD2: get access token for the application (token returned doesn't have permission)
$token_url = 'https://graph.facebook.com/oauth/access_token?'
    . 'client_id=' . AppInfo::appID()
    . '&client_secret=' . AppInfo::appSecret()
    . '&grant_type=client_credentials';
  $token_response = file_get_contents($token_url);	// converts into token response string
  $params = null;
  parse_str($token_response, $params);
  $app_access_token = $params['access_token'];
  
  echo $token_response.'\n';
  echo $app_access_token.'\n';
//https://graph.facebook.com/USER_ID/scores?score=USER_SCORE&access_token=APP_ACCESS_TOKEN
  */
  /*
  //post scores-METHOD1: doesnt work
  $scorePostURL = 'https://graph.facebook.com/'.$user_id.'/scores?'
    . 'score=' . '50'
    . '&access_token=' . $app_access_token;
  $scorePostResponse = file_get_contents($scorePostURL);	// converts into token response string
	  echo $scorePostURL.'\n';
	  echo $scorePostResponse;*/

	  
	  
	  // if the score obtained is higher than the score in the Graph API, post the score and ask for a request to send to the user next
$my_scores = idx($facebook->api('/me/scores?limit=16', 'post', array('access_token' => $app_access_token)), 'data', array());
foreach ($my_scores as $my_individual_app_score){
    $application_id_for_the_score = idx(idx($my_individual_app_score, 'application'), 'id');
	if (AppInfo::appID()==$application_id_for_the_score){
		$my_previous_score=idx($my_individual_app_score, 'score');
		if (>$my_previous_score){
			$success=$facebook->api(
    		'/me/scores/',
    		'post',
    		array('score' => '20', 'access_token' => $app_access_token)
			);
			echo 'is successful? '.$success.'\n';			
		}
	}
}

var_dump($_SESSION);
echo 'app access token: '.$app_access_token;
// post scores on the api-METHOD2
$success=$facebook->api(
    '/me/scores/',
    'post',
    array('score' => '60', 'access_token' => $app_access_token)
);
echo 'is successful? '.$success.'\n';




//display the scores: REQUIRE AN ACCESS TOKEN
$scores = idx($facebook->api('/'.AppInfo::appID().'/scores?limit=16', 'get', array('access_token' => $app_access_token)), 'data', array());



echo "user_id".$scores[0]["user"]["id"]."\n";
echo "Scores: ".$scores[0]["score"]."\n";

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
	  
	  $(function(){
        // Set up so we handle click on the buttons
        $('#retry').click(function() {
          window.location = "index.php";
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
       

<table class="background">
	<tr><td>
		<div id="welcome_msg">
		<h1>Welcome to the typing-speed competition v2.0</h1>  
		<br/>
		</div>
	</td></tr>
	<tr><td>
		<div id="author">
		<h6>---- By Aaron Wang</h6>
		</div>
	</td></tr>
		
	<tr><td>
	<div class="input_text" id= "input_text"><br/>
		<table cellpadding="10">
			<tr>
				<td class="large_padding">
					TIME:
				</td>
				<td id="time_value" class="large_padding">
					0
				</td>
			</tr>
			<tr>
				<td class="large_padding">
					WORDS:
				</td>
				<td id="words_value" class="large_padding">
					0
				</td>
			</tr>
			<tr>
				<td class="large_padding">
					CHARACTERS ENTERED:
				</td>
				<td id="charactersEntered_value" class="large_padding">
					0
				</td>
			</tr>
			<tr>
				<td class="large_padding">
					WRONG CHARACTERS:
				</td>
				<td id="wrongCharacters_value" class="large_padding">
					0
				</td>
			</tr>
			<tr class="highlight_row">
				<td class="large_padding">
					SPEED:
				</td>
				<td id="speed_value" class="large_padding">
					0
				</td>
			</tr>
			<tr class="highlight_row">
				<td class="large_padding">
					ACCURACY:
				</td>
				<td id="accuracy_value" class="large_padding">
					0
				</td>
			</tr class="super_highlight_row">
			<tr>
				<td class="large_padding">
					TOTAL SCORE:
				</td>
				<td id="score_value" class="large_padding">
					0
				</td>
			</tr><br/>
		</table>
	<br/>
	</div>
	</td></tr>
	<tr><td>
		<div class="center_alignment">
		<input type="button" id="retry" value="Retry" ></input><br/>
		<input type="button" id="sendRequest" value="Compete with Friends" data-message="I want to compete typing speed with you"></input>
		</div>
	</td></tr>
	<tr><td>
	<br/>
	<div class="horizontal_list">
        <h3>Top players of your friends</h3>
        <ul class="friends">
          <?php
		  foreach ($app_using_friends as $auf){
			  $user_id=idx($auf, 'uid');
			  $user_name=idx($auf, 'name');
		  
/* SCORE API:
		  // GET the scores from fb api
		  $scores = idx($facebook->api('/'+$app_info+'/scores?limit=16'), 'data', array());
		  
            foreach ($scores as $scoreForIndividualUser) {
              // Extract the pieces of info we need from the requests above
              $user_id = idx(idx($scoreForIndividualUser, 'user'), 'id');
              $name = idx(idx($scoreForIndividualUser, 'user'), 'name');
         */
		 ?>
          <li>
		  	<div>
            <a href="https://www.facebook.com/<?php echo he($user_id); ?>" target="_top">
              <img src="https://graph.facebook.com/<?php echo he($user_id) ?>/picture?type=square" alt="<?php echo he($user_name); ?>"><br/>
              <?php 
			  	echo he($user_name); 
			  ?>
            </a><br/>
			<?php 
			//echo he((idx($scoreForIndividualUser, 'score')); 
			  ?>pts
			</div>
          </li>
          <?php
            }
          ?>
        </ul>
      </div>
	</td></tr>
</table>
<br/>
</div>
	  
	  
      <?php } else { ?>
      <div>
        <h1>Welcome to typing test competition v2.0!</h1>
		<br/>
        <div class="fb-login-button" data-scope="user_games_activity,friends_games_activity,publish_actions"></div>
      </div>
      <?php } ?>

		
	    <script type="text/javascript">	
document.getElementById("time_value").innerHTML=sessionStorage.getItem("time")+" s";
document.getElementById("words_value").innerHTML=sessionStorage.getItem("words");
document.getElementById("charactersEntered_value").innerHTML=sessionStorage.getItem("charactersEntered");
document.getElementById("wrongCharacters_value").innerHTML=sessionStorage.getItem("wrongCharacters");
document.getElementById("speed_value").innerHTML=sessionStorage.getItem("speed")+" words/s";
document.getElementById("accuracy_value").innerHTML=sessionStorage.getItem("accuracy")+"%";
document.getElementById("score_value").innerHTML=sessionStorage.getItem("score")+" pts";
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
