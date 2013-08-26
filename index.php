<?php

//for IE iFrame 3rd party cookie blocking 
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
	header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	exit();
}

// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');
require_once('sdk/src/facebook.php');

$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
  'sharedSession' => true,
  'trustForwarded' => true,
));
$access_token = $facebook->getAccessToken();
echo "app access token: ".$app_access_token;


// viewer's info
$user_id = $facebook->getUser();
if ($user_id) {
  try {
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    if (!$facebook->getUser()) {
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }
	
	//graph API to retrieve likes, friends and photos
  $likes = idx($facebook->api('/me/likes?limit=4'), 'data', array());
  $friends = idx($facebook->api('/me/friends?limit=4'), 'data', array());
  $photos = idx($facebook->api('/me/photos?limit=16'), 'data', array());
  //FQL
  /*$app_using_friends = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
  ));*/

echo "before fql call";
$app_using_friends_with_scores = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT user_id, value FROM score WHERE user_id IN(SELECT uid1, uid2 FROM friend WHERE uid1 = me()) AND app_id = '.AppInfo::appID().' ORDER BY value DESC'
  ));
echo "after fql call";
}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());
$app_name = idx($app_info, 'name', '');

echo "before info, app ID:".AppInfo::appID();
echo "before info, user ID:".$user_id;









/*
$user_id = $facebook->getUser();
if ($user_id) {
  try {
    // Fetch the viewer's basic information
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // If the call fails we check if we still have a user. The user will be
    // cleared if the error is because of an invalid accesstoken
    if (!$facebook->getUser()) {
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }

  // This fetches some things that you like . 'limit=*" only returns * values.
  // To see the format of the data you are retrieving, use the "Graph API
  // Explorer" which is at https://developers.facebook.com/tools/explorer/
  $likes = idx($facebook->api('/me/likes?limit=4'), 'data', array());

  // This fetches 4 of your friends.
  $friends = idx($facebook->api('/me/friends?limit=4'), 'data', array());

  // And this returns 16 of your photos.
  $photos = idx($facebook->api('/me/photos?limit=16'), 'data', array());

  // Here is an example of a FQL call that fetches all of your friends that are
  // using this app
  $app_using_friends = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
  ));
}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());
$app_name = idx($app_info, 'name', '');
*/

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
<script type="text/javascript">	
function start(){
	if (!started){
		start_time=new Date().getTime()/1000;
		//var int=self.
		TimerID=setInterval(function(){clock()},1000);
		started=true;
	}
	//timerID=setTimeout(function(){clock()},1000);
//alert (start_time);
}
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


    <header class="clearfix">
     <?php if (isset($basic)) { ?>     
      <div><table class="background">
	<tr><td>
		<div id="welcome_msg">
		<h1>Welcome to the typing-speed competition (Beta)</h1> 
		</div>
	</td></tr>
	<tr><td>
		<div id="author">
		<h6>---- By Aaron Wang</h6>
		</div>
	</td></tr>
		
	<tr><td>
	<div class="input_text" id= "input_text">
				<p id="text_para" style="font-size: 19px; color: #222299; margin: 15px">
		</p></div>
	
		<div class="input_box" style="color: #112211; font-size: 16px">
			Please click on the textbox below to start and try to type as fast as you can~<br/>

			
			<input type="text" id="input_text" name="input_text" onkeypress="updateText(event);" onclick="start();" style="width: 660px; overflow:hidden; border: 3px inset; border-color: #444444"></input>	
		</div>
	</td></tr>
	
	<tr><td>
		<div id="realtime_statistics" style="font-weight: bold;">
		<table>
		<tr>
			<td>
				<h2>Time: </h2>
			</td>
			<td>
				<h2><span id="time">0</span> s</h2>
			</td>
			<td>
				<h2>Words: </h2>
			</td>
			<td>
				<h2><span id="word_entered">0</span></h2>
			</td>
		</tr><tr>
			<td>
				<h2>Characters Enetred: </h2>
			</td>
			<td>
				<h2><span id="char_entered">0</span></h2>
			</td>
			<td>
				<h2>Wrong Characters: </h2>
			</td>
			<td>
				<h2><span id="char_entered_wrong">0</span></h2>
			</td>
		</tr><tr>
			<td>
				<h2>Speed: </h2>
			</td>
			<td>
				<h2><span id="speed">0</span> word/s</h2>
			</td>
			<td>
				<h2>Accuracy: </h2>
			</td>
			<td>
				<h2><span id="accuracy">0</span>%</h2>
			</td>
		</tr><tr>
			<td>
				<h2>Total Score: </h2>
			</td>
			<td>
				<h2><span id="score">0</span> pts</h2>
			</td>
		</tr></table>
		</div>
	<br/></td></tr>
	<tr><td class="horizontal_list">
	<div>
        <h3>Top players of your friends: </h3>
        <ul class="friends">
          <?php
		  /*foreach ($app_using_friends as $auf){
			$user_id=idx($auf, 'uid');
			$user_name=idx($auf, 'name');
			$friend_actual_score=0;
		  
			$friend_scores = idx($facebook->api('/'.$user_id.'/scores/', 'get', array('access_token' => $app_access_token)), 'data', array());
			foreach ($friend_scores as $friend_individual_app_score){
				$application_id_for_the_score = idx(idx($friend_individual_app_score, 'application'), 'id');
				if (AppInfo::appID()==$application_id_for_the_score){
					$friend_actual_score=idx($friend_individual_app_score, 'score');
				}
			}	*/
		$i=0;
		$my_current_placement=0;
		$my_highest_placement=0;
		$my_previous_score=-1;
		foreach ($app_using_friends_with_scores as $auf_with_score){
			$i++;
			
			$user_id=idx($auf_with_score, 'user_id');
			$friend_actual_score=idx($auf_with_score, 'value');			
			$user_name = idx($facebook->api('/'.$user_id, 'get', array()), 'name', array());
			$first_name = idx($facebook->api('/'.$user_id, 'get', array()), 'first_name', array());
			$last_name = idx($facebook->api('/'.$user_id, 'get', array()), 'last_name', array());
			
			if ($user_id==$facebook->getUser()){
				$first_name="YOU";
				$last_name="";
				$my_previous_score=$friend_actual_score;
				
				echo "<li class='my_ranking'>";
			}else{
				echo "<li class='friends_ranking'>";
			}
			
			/*if ($GET{}<$friend_actual_score)
				$my_current_placement=$i;
			if ($GET{}<$friend_actual_score)
				$my_highest_placement=$i;*/
			
			//$user_name=idx($auf_with_score, 'user_name');
			?>
          
		  	<div>
            <a href="https://www.facebook.com/<?php echo he($user_id); ?>" target="_top">
              <img src="https://graph.facebook.com/<?php echo he($user_id) ?>/picture?type=square" alt="<?php echo he($user_name); ?>"><br/>
              <?php 
			  	echo he($first_name); 
			  ?>
			<br/>
			<?php 
			  	echo he($last_name); 
			?>
            </a><br/><span style="font-size: 11px;">
			<?php 
			  echo $friend_actual_score." "; ?>pts </span>
			</div>
          </li>
          <?php
            }
          ?>
        </ul>
      </div>
	  </td></tr><tr><td style="text-align: center;">
	  <input type="button" id="sendRequest" value="Invite friends to compete" data-message="I want to compete typing speed with you"></input>
	</td></tr>	
</table>
      </div>
	   <script type="text/javascript">			
var start_time;
var input_text;
var textCharacters = new Array();	// boolean array, 1 for correct char, 0 for wrong char

var text_array = new Array();
text_array[0] = "abcde dfefe";
text_array[1] = "The term \"design of experiments\" derives from early statistical work performed by Sir Ronald Fisher. He was described as \"a genius who almost single-handedly created the foundations for modern statistical science.\" Fisher initiated the principles of design of experiments and elaborated on his studies of \"analysis of variance\". Perhaps even more important, Fisher began his systematic approach to the analysis of real data as the springboard for the development of new statistical methods. He began to pay particular attention to the labour involved in the necessary computations performed by hand, and developed methods that were as practical as they were founded in rigour.";
text_array[2] = "On their return to England, Hawking attended Radlett School for a year and from September 1952, St Albans School. The family placed a high value on education. Hawking's father wanted his son to attend the well-regarded Westminster School, but the 13-year-old Hawking was ill on the day of the scholarship examination. His family could not afford the school fees without the financial aid of a scholarship, so Hawking remained at St Albans. A positive consequence was that Hawking remained with a close group of friends with whom he enjoyed board games, the manufacture of fireworks, model aeroplanes and boats.";
text_array[3] = "As he slowly lost the ability to write, he developed compensatory visual methods, including seeing equations in terms of geometry. The physicist Werner Israel later compared the achievements to Mozart composing an entire symphony in his head. Hawking was, however, fiercely independent and unwilling to accept help or make concessions for his disabilities. Hawking preferred to be regarded as \"a scientist first, popular science writer second, and, in all the ways that matter, a normal human being with the same desires, drives, dreams, and ambitions as the next person.\”";
text_array[4] = "The Nobel Prize is a set of annual international awards bestowed in a number of categories by Scandinavian committees in recognition of cultural and/or scientific advances. The will of the Swedish philanthropist inventor Alfred Nobel established the prizes in 1895. The prizes in Physics, Chemistry, Physiology or Medicine, Literature, and Peace were first awarded in 1901. The related Nobel Memorial Prize in Economic Sciences was created in 1968. Between 1901 and 2012, the Nobel Prizes and the Prize in Economic Sciences were awarded 555 times to 863 people and organizations.";
text_array[5] = "The atom is a basic unit of matter that consists of a dense central nucleus surrounded by a cloud of negatively charged electrons. The atomic nucleus contains a mix of positively charged protons and electrically neutral neutrons. The electrons of an atom are bound to the nucleus by the electromagnetic force. Likewise, a group of atoms can remain bound to each other by chemical bonds based on the same force, forming a molecule. An atom containing an equal number of protons and electrons is electrically neutral, otherwise it is positively or negatively charged and is known as an ion."
text_array[6] = "A pencil is a writing implement or art medium usually constructed of a narrow, solid pigment core inside a protective casing. The case prevents the core from breaking, and also from marking the user’s hand during use. Pencils create marks via physical abrasion, leaving behind a trail of solid core material that adheres to a sheet of paper or other surface. They are noticeably distinct from pens, which dispense liquid or gel ink that stain the light color of the paper. Most pencil cores are made of graphite mixed with a clay binder, leaving grey or black marks that can be easily erased.";
text_array[7] = "The value of graphite was soon realised to be enormous, mainly because it could be used to line the moulds for cannonballs, and the mines were taken over by the Crown and guarded. When sufficient stores of graphite had been accumulated, the mines were flooded to prevent theft until more was required. Graphite had to be smuggled out for use in pencils. Because graphite is soft, it requires some form of encasement. Graphite sticks were initially wrapped in string or sheepskin for stability. The news of the usefulness of these early pencils spread far and wide, attracting the attention of artists all over the known world.";
text_array[8] = "The term engineering itself has a much more recent etymology, deriving from the word engineer, which itself dates back to 1325, when an engine'er (literally, one who operates an engine) originally referred to \"a constructor of military engines.\" In this context, now obsolete, an \"engine\" referred to a military machine, i.e., a mechanical contraption used in war (for example, a catapult). Notable exceptions of the obsolete usage which have survived to the present day are military engineering corps, e.g., the U.S. Army Corps of Engineers.";
text_array[9] = "A computer network (or data network) is a telecommunications network that allows computers to exchange data. The physical connection between networked computing devices is established using either cable media or wireless media. The best-known computer network is the Internet. Network devices that originate, route and terminate the data are called network nodes. Nodes can include hosts such as servers and personal computers, as well as networking hardware. Two devices are said to be networked when a process in one device is able to exchange information with a process in another device.";
text_array[10] = "Electrical engineering can trace its origins back to the experiments of Alessandro Volta in the 1800s, the experiments of Michael Faraday, Georg Ohm and others and the invention of the electric motor in 1872. The work of James Maxwell and Heinrich Hertz in the late 19th century gave rise to the field of electronics. The later inventions of the vacuum tube and the transistor further accelerated the development of electronics to such an extent that electrical and electronics engineers currently outnumber their colleagues of any other engineering specialty.";

var randomNumberGenerator=0;
//var randomNumberGenerator=Math.floor(Math.random()*10)+1;	// replace 10 with the max index value

document.getElementById("text_para").innerHTML=text_array[randomNumberGenerator];

	</script>	  
     
	  <?php }else{ ?>
	  
	  <?php 
	  } 	  
	  ?>
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
      <p id="picture" style="background-image: url(https://graph.facebook.com/<?php echo he($user_id); ?>/picture?type=normal)"></p>

      <div>
        <h1>Welcome, <strong><?php echo he(idx($basic, 'name')); ?></strong></h1>
        <p class="tagline">
          This is your app
          <a href="<?php echo he(idx($app_info, 'link'));?>" target="_top"><?php echo he($app_name); ?></a>
        </p>

        <div id="share-app">
          <p>Share your app:</p>
          <ul>
            <li>
              <a href="#" class="facebook-button" id="postToWall" data-url="<?php echo AppInfo::getUrl(); ?>">
                <span class="plus">Post to Wall</span>
              </a>
            </li>
            <li>
              <a href="#" class="facebook-button speech-bubble" id="sendToFriends" data-url="<?php echo AppInfo::getUrl(); ?>">
                <span class="speech-bubble">Send Message</span>
              </a>
            </li>
            <li>
              <a href="#" class="facebook-button apprequests" id="sendRequest" data-message="Test this awesome app">
                <span class="apprequests">Send Requests</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
      <?php } else { ?>
      <div>
        <h1>Welcome</h1>
        <div class="fb-login-button" data-scope="user_games_activity,friends_games_activity,publish_actions,user_likes,user_photos"></div>
      </div>
      <?php } ?>
    </header>

    <section id="get-started">
      <p>Welcome to the Connect of Five app!</p>
      <a href="https://devcenter.heroku.com/articles/facebook" target="_top" class="button">Learn How to Edit This App</a>
    </section>

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

    <section id="guides" class="clearfix">
      <h1>Learn More About Heroku &amp; Facebook Apps</h1>
      <ul>
        <li>
          <a href="https://www.heroku.com/?utm_source=facebook&utm_medium=app&utm_campaign=fb_integration" target="_top" class="icon heroku">Heroku</a>
          <p>Learn more about <a href="https://www.heroku.com/?utm_source=facebook&utm_medium=app&utm_campaign=fb_integration" target="_top">Heroku</a>, or read developer docs in the Heroku <a href="https://devcenter.heroku.com/" target="_top">Dev Center</a>.</p>
        </li>
        <li>
          <a href="https://developers.facebook.com/docs/guides/web/" target="_top" class="icon websites">Websites</a>
          <p>
            Drive growth and engagement on your site with
            Facebook Login and Social Plugins.
          </p>
        </li>
        <li>
          <a href="https://developers.facebook.com/docs/guides/mobile/" target="_top" class="icon mobile-apps">Mobile Apps</a>
          <p>
            Integrate with our core experience by building apps
            that operate within Facebook.
          </p>
        </li>
        <li>
          <a href="https://developers.facebook.com/docs/guides/canvas/" target="_top" class="icon apps-on-facebook">Apps on Facebook</a>
          <p>Let users find and connect to their friends in mobile apps and games.</p>
        </li>
      </ul>
    </section>
  </body>
</html>
