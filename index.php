<?php
ob_start();
error_reporting(E_ALL);
ini_set("display_erros", 1);

require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/app/class/JMultimidia/User.php";

use \League\OAuth2\Client\Provider\Facebook as Facebook;

if (empty($_SESSION["userData"])) {
  echo "<h1>Guest</h1>";
  /**
   * AUTH FACEBOOK
   */
  $facebook = new Facebook([
    'clientId'          => FACEBOOK["app_id"],
    'clientSecret'      => FACEBOOK["app_secret"],
    'redirectUri'       => FACEBOOK["app_redirect"],
    'graphApiVersion'   => FACEBOOK["app_version"],
  ]);

  $authUrl = $facebook->getAuthorizationUrl([
    "scope" => ["email"],
  ]);

  $error = filter_input(INPUT_GET, "error", FILTER_SANITIZE_STRIPPED);
  if ($error){
    echo "<h4>Você precisa autorizar para continuar.</h4>";
  }

  $code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_STRIPPED);
  if ($code){
    $token = $facebook->getAccessToken("authorization_code", [
      "code" => $code
    ]);
    
    $_SESSION["userData"] = $facebook->getResourceOwner($token);
    header("Refresh: 0");
  }

  echo "<a title='FB LOGIN' href='{$authUrl}'>Log in with Facebook!</a></a>";

} else {
  /** @var $user new Facebook */  
	
  $user = $_SESSION["userData"];

  //var_dump($user);
	
	//Insert or update user data to the database
	$fbUserData = array(
		'oauth_provider'=> 'facebook',
		'oauth_uid' 	=> $user->getId(),
		'first_name' 	=> $user->getFirstName(),
		'last_name' 	=> $user->getLastName(),
		'email' 		  => $user->getEmail(),
		'picture' 		=> $user->getPictureUrl(),
  );
    
  //Initialize User class
	$user = new User();
	$userData = $user->checkUser($fbUserData);
	//var_dump($userData);
	//Put user data into session
  //$_SESSION['userData'] = $userData;
  
  //var_dump($userData);

  //Render facebook profile data
	if(!empty($userData)){
		$output = '<h1>Facebook Profile Details </h1>';
		$output .= '<img src="'.$userData['picture'].'" width="100px">';
        $output .= '<br/>Facebook ID : ' . $userData['oauth_uid'];
        $output .= '<br/>Name : ' . $userData['first_name'].' '.$userData['last_name'];
        $output .= '<br/>Email : ' . $userData['email'];
        $output .= '<br/>Logged in with : Facebook';
        $output .= '<br/><a title="logoff" href="?off=true">Logoff</a>';
	}else{
		$output = '<h3 style="color:red">Some problem occurred, please try again.</h3>';
	}
  echo "<div>$output</div>";
  //echo "<a title='Sair' href='?off=true'>Sair</a>";
  $off = filter_input(INPUT_GET, "off", FILTER_VALIDATE_BOOLEAN);
  if ($off){
    //unset($_SESSION["userLogin"]);
    unset($_SESSION["userData"]);
    header("Refresh: 0");
  }
}

ob_end_flush();