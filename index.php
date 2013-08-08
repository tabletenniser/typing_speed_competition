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


  $access_token = $facebook->getAccessToken();
  echo "app access token: ".$app_access_token;

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

      <?php if (isset($basic)) { ?>
     
      <div>
       

<table class="background">
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

var time = 0;
var TimerID=0;
var err = 0;
//var pos = 0;
var position=0;
var numOfWords=0;
var innerText="";
var previousText="";
var isCorrectChar=false;
var timePassedInSec=0;
var started=false;


function clock()
  {
	  timePassedInSec++;
	  document.getElementById("time").innerHTML=Math.round(timePassedInSec*1000)/1000;	// 3 decimal places
	  document.getElementById("speed").innerHTML=Math.round(numOfWords/timePassedInSec*1000)/1000;
	  
	if (position==0){
		document.getElementById("accuracy").innerHTML=0;	  
	  	document.getElementById("score").innerHTML=0;
	}else if (speed==0 || err/position==1){
			document.getElementById("score").innerHTML=0;
	}else{		  	
	  	document.getElementById("accuracy").innerHTML=Math.round((1-err/position)*100000)/1000;	  
	  	document.getElementById("score").innerHTML=Math.round(numOfWords/timePassedInSec*10000*(1-err/position)*(1-err/position));	
	}
  }


// this function is invoked when the user presses a key
function updateText(event){
var chCode = ('charCode' in event) ? event.charCode : event.keyCode;
//alert ("You've pressed"+chCode);

	//var input_text=document.getElementById("input_text").value;
//alert("new Text: input"+input_text.charAt(input_text.length-1)+"text"+text.charAt(pos));

	//if the user inputs the correct character
position++;

	if (String.fromCharCode(chCode)==text_array[randomNumberGenerator].charAt(position-1)){		
		isCorrectChar=true;
	}else{
		err++;
		isCorrectChar=false;
	}
	
	if (text_array[randomNumberGenerator].charAt(position-1)==" "){
			numOfWords++;
			document.getElementById("word_entered").innerHTML=numOfWords;
	}
	
	//pos++;
	
	if (position==text_array[randomNumberGenerator].length){
		numOfWords+=1;
		document.getElementById("word_entered").innerHTML=numOfWords;
		end();
	}
	
	//innerText=innerText.substr(0, pos-1);	
	if (isCorrectChar){
		previousText+="<span style='color: #2222EE'>"+text_array[randomNumberGenerator].charAt(position-1)+"</span>";
	}
	else{
		previousText+="<span style='color: #EE2222'>"+text_array[randomNumberGenerator].charAt(position-1)+"</span>";
		document.getElementById("char_entered_wrong").innerHTML=err;
	}
	innerText=previousText;
	innerText+="<span style='text-decoration: underline; font-weight: bold;'>"+text_array[randomNumberGenerator].charAt(position)+"</span>";
	//innerText+=text_array[randomNumberGenerator].substr(pos+1, text_array[randomNumberGenerator].length - pos);	// 2nd para is the length selected
	innerText+=text_array[randomNumberGenerator].substr(position+1);
	
	
	document.getElementById("char_entered").innerHTML=position;
	document.getElementById("text_para").innerHTML=innerText;
	//document.getElementById("text_para").innerHTML=text.substr(0, pos)+"<span style='text-decoration: underline; font-weight: bold;'>"+text.charAt(pos)+"</span>"+text.substr(pos+1, text.length - pos);
}


function end(){
	window.clearInterval(TimerID);
	//window.clearTimeout(timerID);
	
	// calculation of values required
	var end_time=new Date().getTime()/1000;
	var time_diff=end_time-start_time;
	var accuracy=(1-err/position)*100;
	var speed=Math.round(numOfWords/time_diff*1000)/1000;
	var score=Math.round(speed*accuracy*accuracy);
	
	// set session variables to pass values to result.php
	sessionStorage.setItem("time", Math.round(time_diff*1000)/1000);
	sessionStorage.setItem("words", numOfWords);
	sessionStorage.setItem("charactersEntered", position);
	sessionStorage.setItem("wrongCharacters", err);
	sessionStorage.setItem("speed", speed);
	sessionStorage.setItem("accuracy", accuracy);
	sessionStorage.setItem("score", score);
	
	//window.location.href = "http://localhost/main.php?width=" + width + "&height=" + height;

	// clear all cokies
	/*var cookies = document.cookie.split(";");
		    for (var i = 0; i < cookies.length; i++) {
    			var cookie = cookies[i];
    			var eqPos = cookie.indexOf("=");
    			var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
    			document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
    		}*/
	window.location = "result.php?score="+score;
	
	/*
	input_text=document.getElementById("input_text").value;
	
	var end_time=new Date().getTime()/1000;
	var time_diff=end_time-start_time;
	
//alert ("you have typed for "+(time_diff)+" seconds");
	
	
	if (window.XMLHttpRequest){//for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}else{//  for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}

	xmlhttp.onreadystatechange=function(){
		if (xmlhttp.readyState==4 && xmlhttp.status==200){
			//document.getElementById("debug").innerHTML=xmlhttp.responseText;
			window.location = "http://typingtest.p.ht/questions.php";
		}
	}
	
	var errPercentage=err/pos*100;
	var speed=numOfWords/time_diff;

	xmlhttp.open("GET","real_ajax.php?time_diff="+time_diff+
	"&input_text="+encodeURIComponent(input_text)+"&numOfWords="+encodeURIComponent(numOfWords)+"&errPercentage="+errPercentage,true);
	
	alert ("you have typed "+numOfWords+" words in "+time_diff+" seconds. (speed="+speed+" words per sec) Among the "+pos+" characters you have typed, "+err+" are wrong ("+errPercentage+"%)");
	xmlhttp.send();*/
	
}
	
	</script>	  
      <?php } else { ?>		  
	  <div>
        <h1>Welcome to typing test competition (Beta)!</h1>
		<br/>
        <div class="fb-login-button" data-scope="user_games_activity,friends_games_activity,publish_actions"></div>
      </div>
      <?php } 
	  
	  ?>
		  
  </body>
</html>
