<?php
//setting start variables
	$userLoggedIn = FALSE;
	$userIsNew = FALSE;

	//handle current page
		if(isset($_GET['page']))
			$page = $_GET['page'];
		else
			$page = "home";

	//load song by URL until it's implemented to create a lobby
		if (isset($_GET['song']))
			$songID = $_GET['song'];
		else
			$songID = "0";

	//set song info to default
		$songImage = "0";
		$songName = "No song selected";

	########## Google Settings ###########
	$google_client_id       = '1079519191246-0vsspqsammtsnind13cnevb5d6hdv51v.apps.googleusercontent.com';
	$google_client_secret   = 'NLmza8rmfB7fBEJpiBz_psyY';
	$google_redirect_url    = 'http://faketuan.dyndns.org/karaoke/'; //path to your script
	$google_developer_key   = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

	########## MySql details #############
	$db_username = "root"; //Database Username
	$db_password = ""; //Database Password
	$hostname = "localhost"; //Mysql Hostname
	$db_name = 'karaoke'; //Database Name

//connect to database
	$mysqli = new mysqli($hostname, $db_username, $db_password, $db_name);

	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

//make DB connection ready for japanese
	$mysqli->set_charset("utf8");

//handle user login
	//include google api files
	require_once 'google-api-php-client/src/Google_Client.php';
	require_once 'google-api-php-client/src/contrib/Google_Oauth2Service.php';

	//start session
	session_start();

	$gClient = new Google_Client();
	$gClient->setApplicationName('AFHU Karaoke');
	$gClient->setClientId($google_client_id);
	$gClient->setClientSecret($google_client_secret);
	$gClient->setRedirectUri($google_redirect_url);
	$gClient->setDeveloperKey($google_developer_key);

	$google_oauthV2 = new Google_Oauth2Service($gClient);

	//If user wish to log out, we just unset Session variable
	if (isset($_REQUEST['reset'])) 
	{
	  unset($_SESSION['token']);
	  $gClient->revokeToken();
	  header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL)); //redirect user back to page
	}

	//If code is empty, redirect user to google authentication page for code.
	//Code is required to aquire Access Token from google
	//Once we have access token, assign token to session variable
	//and we can redirect user back to page and login.
	if (isset($_GET['code'])) 
	{ 
	    $gClient->authenticate($_GET['code']);
	    $_SESSION['token'] = $gClient->getAccessToken();
	    header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL));
	    return;
	}


	if (isset($_SESSION['token'])) 
	{ 
	    $gClient->setAccessToken($_SESSION['token']);
	}


	if ($gClient->getAccessToken()) 
	{
	      //For logged in user, get details from google using access token
	      $user                 = $google_oauthV2->userinfo->get();
	      $user_id              = $user['id'];
	      $user_name            = filter_var($user['name'], FILTER_SANITIZE_SPECIAL_CHARS);
	      $email                = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
	      $profile_url          = filter_var($user['link'], FILTER_VALIDATE_URL);
	      $profile_image_url    = filter_var($user['picture'], FILTER_VALIDATE_URL);
	      $_SESSION['token']    = $gClient->getAccessToken();
	}
	else 
	{
	    //For Guest user, get google login url
	    $authUrl = $gClient->createAuthUrl();
	}


	if(!isset($authUrl)){ // user logged in
	    //compare user id in our database
	    $user_exist = $mysqli->query("SELECT COUNT(guid) as usercount FROM users WHERE guid=$user_id")->fetch_object()->usercount; 
	    if($user_exist){
				$userLoggedIn = TRUE;
	    }else{
				$userIsNew = TRUE;
	    }
	}

//some functions for later use
	function secondsToTimeCode($seconds) {
		$t = round($seconds);
		return sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>AFHU Karaoke</title>
		<link rel="icon" href="/">
		<link rel="stylesheet" type="text/css" href="normalize.css">
		<link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="style.css">
		<script type="text/javascript" src="javascript.js"></script>
	</head>
	<body>
		<div class="header-wrapper">
			<div class="header-left"><img src="logo.png"></div>
			<div class="header-right">
				<?php
					if(isset($authUrl)){ //user is not logged in, show login button
						echo '<a class="login" href="'.$authUrl.'"><div class="user-signin-button"></div></a>';
					}else{
						echo '<div class="player-info-badge">';
						echo '<div class="player-info-badge-image"><a href="'.$profile_url.'" target="_blank"><img src="'.$profile_image_url.'?sz=50" /></a></div>';
	    				echo $user_name.'<br><a class="logout" href="?reset=1">Logout</a>';
	    				echo '</div>';
					}
				?>
			</div>
		</div>
		<div class="player_menu">
			<div class="player_menu_settings_entry" style="padding:0;"></div>
			<div class="player_menu_settings_entry <?php if ($page == "home") { echo "player-menu-settings-entry-active"; } ?>" onclick="javascript:window.location = '?page=home'">Home</div>
			<div class="player_menu_settings_entry <?php if ($page == "lyrics") { echo "player-menu-settings-entry-active"; } ?>" onclick="javascript:window.location = '?page=lyrics&song=cE69xAWdTq8'">♪ Last Smile</div>
			<div class="player_menu_settings_entry <?php if ($page == "lyrics") { echo "player-menu-settings-entry-active"; } ?>" onclick="javascript:window.location = '?page=lyrics&song=zyJJlPSeEpo'">♪ 終わりの世界から</div>
			<div class="player_menu_settings_entry <?php if ($page == "lyrics") { echo "player-menu-settings-entry-active"; } ?>" onclick="javascript:window.location = '?page=lyrics&song=_JKvsaII6YY'">♪ Sparkle!</div>
			<div class="player_menu_settings_entry <?php if ($page == "settings") { echo "player-menu-settings-entry-active"; } ?>" onclick="javascript:window.location = '?page=settings'">Einstellungen</div>
		</div>
		<div class="player_content">
			<?php
				if($userIsNew){ //user is new on the page
					//user is new
					echo 'Hi '.$user_name.', Thanks for Registering!';
					$mysqli->query("INSERT INTO users (guid, gname, gmail, glink, gpiclink) VALUES ($user_id, '$user_name','$email','$profile_url','$profile_image_url')");
				}elseif ($userLoggedIn) { // user logged in
					if($page == "lyrics"){
						$result = mysqli_query($mysqli,"SELECT * FROM `songs` WHERE `vID` = '$songID' LIMIT 0,1");

						while($row = mysqli_fetch_array($result)){
							$songName = $row['title'];
							$lyrics = $row['lyrics'];
							$songImage = $row['vID'];
							echo nl2br($lyrics);
						}
					}elseif($page == "home"){
						echo "<h1>account information</h1>";
						if ($userIsNew) {
							echo "this should not happen at all";
						}else{
							//list all user details
							echo '<pre>'; 
							print_r($user);
							echo '</pre>';
						}
						?>

							<h1>update account data</h1>
								<input placeholder="username">
							<h1>change current song</h1>
								WIP
							<h1>room info</h1>
						<?php
							$result = mysqli_query($mysqli,"SELECT * FROM `games` WHERE `id` = '1' LIMIT 0,1");

							while($row = mysqli_fetch_array($result)){
								echo "id: ".$row['id']."<br>";
								echo "host: ".$row['host']."<br>";
								echo "songID: ".$row['currentSongId']."<br>";
								switch ($row['playStatus']) {
									case '1':
										$playStatusName = "Playing";
										break;
									default:
										$playStatusName = "Paused";
										break;
								}
								echo "playStatus: ".$row['playStatus']." ($playStatusName)<br>";
								echo "playTime: ".$row['playTime']." (".secondsToTimeCode($row['playTime']).")";
							}
						?>
							<h1>song info</h1>
								WIP
						<?php
					}else{
						include "include/$page.php";
					}
				}else{
					echo "not logged in";
				}
			?>
		</div>
		<div class="player-roominfo">
			Players in room:
			<div class="player-info-badge">Yukina</div>
			<div class="player-info-badge">Yakahiro</div>
			<div class="player-info-badge">Yasuo</div>
			<div class="player-info-badge" style="background-color:lightgreen;">You_See</div>
			(green = host/song selector)
		</div>
		<div class="player-timeline">
			<img src="cover/<?php echo $songImage; ?>.jpg" class="player-songcover">
			<div class="player-songtitle"><?php echo $songName; ?></div>
			<div class="player-timer">01:53:34 / 03:01:15</div>
		</div>
	</body>
</html>