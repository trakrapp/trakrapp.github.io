<?php
	require_once('aweber/aweber_api.php');
	$consumerKey = 'Akz0KemiYM3N7kny8T4IM5vS'; # put your credentials here
	$consumerSecret = 'otjk1Zcu2IlqGZO7hsG9TotRfZeVOgHB1ICBbX3f'; # put your credentials here
	if (!$consumerKey || !$consumerSecret){
	    print "You need to assign \$consumerKey and \$consumerSecret at the top of this script and reload.<br><br>" .
	        "These are listed on <a href=\"https://labs.aweber.com/apps\" target=\"_blank\">https://labs.aweber.com/apps<a><br>\n";
	    exit;
	}
	$aweber = new AWeberAPI($consumerKey, $consumerSecret);
	if (!$accessKey || !$accessSecret)
	{
	    display_access_tokens($aweber);
	}
	 
	function get_self()
	{
    	return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
 	function display_access_tokens($aweber)
	{
	    if (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier']))
	    {
	 		$aweber->user->requestToken = $_GET['oauth_token'];
	        $aweber->user->verifier = $_GET['oauth_verifier'];
	        $aweber->user->tokenSecret = $_COOKIE['secret'];
	 		list($accessTokenKey, $accessTokenSecret) = $aweber->getAccessToken();
	        $access = array($accessTokenKey,$accessTokenSecret);
			$name="getaccess.txt";
		    if(file_exists($name))
		    {
		        unlink($name);
		        file_put_contents($name, json_encode($access, true), FILE_APPEND);
		        chmod($name,0777);
		    }else{
		        file_put_contents($name, json_encode($access, true), FILE_APPEND);
		        chmod($name,0777);
		    }
	        print_r('Access assigned successfully, You can close this window and continue at export window and your Aweber list name.');die;
	    }
	    $callbackURL = get_self();
	    list($key, $secret) = $aweber->getRequestToken($callbackURL);
	    $authorizationURL = $aweber->getAuthorizeUrl();
		setcookie('secret', $secret);
	 	header("Location: $authorizationURL");
	}
?>