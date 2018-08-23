<?php
if($_POST) {
	
	/*Inlcude API configs*/
	include("config.php");

	//Success or Error Messages
	$msg_invalid_email_address="Please enter a valid email address.";
	$msg_invalid_api="Invalid API credentials, Please contact to site administrator for more information.";
	$msg_subscribed_success="You have successfully subscribed.";
	$msg_mc_error = "Error occurred, Please contact to site administrator for more information.";
	$msg_php_email_sent="The message successfully sent!";
	$msg_php_email_not_sent="The message could not been sent!";

	$purchase_email = $fs_email;
	$fs_from_email = $fs_from_email;
	$email_subject = $fs_subject;
	$g_secret_key = $g_secret_key;
	$input_values = $_POST['data']['values'];
	$input_attr = $_POST['data']['input_name'];
	$value = array_combine($input_attr,$input_values);
	$input_extra = array();
	if($value['email'])
	{
		if(filter_var($value['email'], FILTER_VALIDATE_EMAIL)){
			 $email_address = $value['email'];
		} else {
			$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $msg_invalid_email_address));
			echo $Wonder_result;die;
		}
	}

	$skip_email = array_search($value['email'],$value);
	unset($value[$skip_email]);
	$input_extra = $value;
	$user_name = $input_extra[key($input_extra)];
	if(isset($input_extra['last_name'])){
		$last_name = $input_extra['last_name'];
	}
	if(isset($input_extra['phone'])){
		$phone_num = $input_extra['phone'];
	}
	if(isset($input_extra['comment'])){
		$comment = $input_extra['comment'];
	}
	$subscribe_email = $email_address;
	if(!empty($g_secret_key) && (!empty($value['g-recaptcha-response'])))
	{
		$google_url="https://www.google.com/recaptcha/api/siteverify";
		$secret = $g_secret_key;
		$ip = $_SERVER['REMOTE_ADDR'];
		$captcha = $value['g-recaptcha-response'];
		$captchaurl = $google_url."?secret=".$secret."&response=".$captcha."&remoteip=".$ip;
		$curl_init = curl_init();
		curl_setopt($curl_init, CURLOPT_URL, $captchaurl);
		curl_setopt($curl_init, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_init, CURLOPT_TIMEOUT, 10);
		$results = curl_exec($curl_init);
		curl_close($curl_init);
		$results= json_decode($results, true);
		if($results['success'] == 1)
		{
			// API FUNCTION CALL //
				switch ($export_mail_type) 
				{	
					case 'fs_mailchimp':
						Wonder_mailchimp($subscribe_email,$user_name,$last_name,$phone_num,$comment,$msg_subscribed_success,$msg_invalid_api,$msg_mc_error);
						break;

					case 'fs_aweber':
						Wonder_aweber($subscribe_email,$user_name,$msg_subscribed_success,$msg_invalid_api);
						break;

					case 'fs_active':
						Wonder_activecampaign($subscribe_email,$user_name,$msg_subscribed_success,$msg_invalid_api,$last_name,$phone_num);
						break;

					case 'fs_response':
						Wonder_getresponse($subscribe_email,$user_name,$msg_subscribed_success,$msg_invalid_api);
						break;

					case 'fs_campain':
						Wonder_campaign($subscribe_email,$user_name,$msg_subscribed_success,$msg_invalid_api);
						break;

					case 'fs_mailerlite':
						Wonder_mailerlite($subscribe_email,$user_name,$last_name,$phone_num,$comment,$msg_subscribed_success,$msg_invalid_api);
						break;

					default:
						$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $msg_invalid_api));
						echo $Wonder_result;die;
						break;
				}
			// END API FUNCTION CALL //
		}else{

			$Wonder_result =json_encode(array('type'=>'fs_error', 'text' => $msg_invalid_api));
			echo $Wonder_result;die;
		}

	}else{

		// API FUNCTION CALL //
		switch ($export_mail_type) 
		{
			case 'fs_mailchimp':
				Wonder_mailchimp($subscribe_email,$user_name,$last_name,$phone_num,$comment,$msg_subscribed_success,$msg_invalid_api,$msg_mc_error);
				break;

			case 'fs_aweber':
				Wonder_aweber($subscribe_email,$user_name,$msg_subscribed_success,$msg_invalid_api);
				break;

			case 'fs_active':
				Wonder_activecampaign($subscribe_email,$user_name,$msg_subscribed_success,$msg_invalid_api,$last_name,$phone_num);
				break;

			case 'fs_response':
				Wonder_getresponse($subscribe_email,$user_name,$msg_subscribed_success,$msg_invalid_api);
				break;

			case 'fs_campain':
				Wonder_campaign($subscribe_email,$user_name,$msg_subscribed_success,$msg_invalid_api);
				break;

			case 'fs_mailerlite':
				Wonder_mailerlite($subscribe_email,$user_name,$last_name,$phone_num,$comment,$msg_subscribed_success,$msg_subscribed_success,$msg_invalid_api);
				break;

			default:
				$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $msg_invalid_api));
				echo $Wonder_result;die;
				break;
		}
		// END API FUNCTION CALL //
	}

}

	function Wonder_mailchimp($subscribe_email,$user_name,$last_name=NULL,$phone_num=NULL,$comment=NULL,$msg_subscribed_success,$msg_invalid_api,$msg_mc_error)
	{
		if(defined('mailchimp_api_key')	&& defined('mailchimp_api_listid'))
		{
			include('mailchimp/Mailchimp.php');
			$mailchimp_api_key = mailchimp_api_key;
		    $mailchimp_api_listid = mailchimp_api_listid;
		    $status = 'subscribed';
		    if(!isset($user_name)){
				$get_name = explode("@",$subscribe_email);
				$user_name = $get_name[0];
			}else{
				$user_name = $user_name;
			}
			$result = json_decode(Wonder_mailchimp_add_subscriber($subscribe_email,$status,$mailchimp_api_listid,$mailchimp_api_key,$user_name,$last_name,$phone_num,$comment));
			if( $result->status == 'subscribed' || $result->status == 'pending'){
				$Wonder_result = json_encode(array('type'=>'fs_message', 'text' => $msg_subscribed_success));
				echo $Wonder_result;die;
			}else{
				$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $msg_mc_error));
				echo $Wonder_result;die;
			}
		}else{
			$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $msg_invalid_api));
			echo $Wonder_result;die;
		}
	}

	function Wonder_aweber($subscribe_email,$user_name,$msg_subscribed_success,$msg_invalid_api)
	{
		if(defined('aweber_list_name'))
		{
			require_once('aweber/aweber_api.php');
			$consumerKey = 'Akz0KemiYM3N7kny8T4IM5vS'; 			
			$consumerSecret = 'otjk1Zcu2IlqGZO7hsG9TotRfZeVOgHB1ICBbX3f';
			$aweber_list_name = aweber_list_name; 
			$access_name = "getaccess.txt";
			$get_content = file_get_contents($access_name);
			$getaccess = json_decode($get_content);
			$accessKey = $getaccess[0];
			$accessSecret = $getaccess[1];
			$aweber = new AWeberAPI($consumerKey, $consumerSecret);
			try { 
					$get_account = $aweber->getAccount($accessKey, $accessSecret);
				    $findlists = $get_account->lists->find(array('name' =>$aweber_list_name));
					$lists = $findlists[0];
					if(!isset($user_name)){
						$get_name = explode("@",$subscribe_email);
						$user_name = $get_name[0];
					}else{
						$user_name = $user_name;
					}
				    //example: create a subscriber
				    
				    $params = array( 
				        'email' => $subscribe_email,
				        'ip_address' => $_SERVER['REMOTE_ADDR'],
				        'name' => $user_name 
				    ); 
				    $subscribers = $lists->subscribers; 
				    if(!empty($subscribers))
				    {
				    	$new_subscriber = $subscribers->create($params);
				  		$Wonder_result = json_encode(array('type'=>'fs_message', 'text' => $msg_subscribed_success));
						echo $Wonder_result;die;
				    }else{
				    	$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $msg_invalid_api));
						echo $Wonder_result;die;
				    }
				    
				} catch(AWeberAPIException $exc) { 

					$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $exc->message));
					echo $Wonder_result;die;
				}
				
	    }else{

	    	$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $msg_invalid_api));
			echo $Wonder_result;die;
		}
    }

    function Wonder_activecampaign($subscribe_email,$user_name,$msg_subscribed_success,$msg_invalid_api,$last_name=NULL,$phone_num=NULL)
	{
		if(defined('ac_api_url') && defined('ac_api_key') && defined('ac_api_listid') )
		{
			require_once('activecampaign/ActiveCampaign.class.php');
			$ac_api_url = ac_api_url;
			$ac_api_key = ac_api_key;
			$ac_api_listid = ac_api_listid;
			$ac = new ActiveCampaign($ac_api_url, $ac_api_key);
			$account = $ac->api("account/view");
			if(!isset($user_name)){
				$get_name = explode("@",$subscribe_email);
				$user_name = $get_name[0];
			}else{
				$user_name = $user_name;
			}
			$last_name = (isset($last_name) ? $last_name : '');
			$phone_num = (isset($phone_num) ? $phone_num : '');
			$contact = array(
								"email" => $subscribe_email,
								"first_name" => $user_name,
								"last_name" => $last_name,
								"phone" => $phone_num,
								"p[{$ac_api_listid}]" => $ac_api_listid,
								"status[{$ac_api_listid}]" => 1, // "Active" status
							);
			$contact_sync = $ac->api("contact/sync",$contact);
			if ($contact_sync->success == 1) {
				// successful request              
				$Wonder_result = json_encode(array('type'=>'fs_message', 'text' => $msg_subscribed_success ));
				echo $Wonder_result;die;
			}else{
				// request failed
				$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $contact_sync->error));
				echo $Wonder_result;die;
			}
			
		}else{

			$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $msg_invalid_api));
			echo $Wonder_result;die;
		}
	}

	function Wonder_getresponse($subscribe_email,$user_name,$msg_subscribed_success,$msg_invalid_api)
	{
		if(defined('getresponse_api_key') && defined('getresponse_campaign_token'))
		{
			require_once('getresponse/GetResponseAPI3.class.php');
			$getresponse_api_key = getresponse_api_key;
			$getresponse_campaign_token = getresponse_campaign_token;
			$getresponse = new GetResponse($getresponse_api_key);
			$subscribe = $getresponse->addContact(array(
													    'name'=> $user_name,
													    'email' => $subscribe_email,
													    'dayOfCycle'=> 0,
													    'campaign' => array('campaignId' => $getresponse_campaign_token),
													    'ipAddress'=> $_SERVER['REMOTE_ADDR']
													  ));
			
			if($subscribe){
				$Wonder_result = json_encode(array('type'=>'fs_message', 'text' => $msg_subscribed_success));
				echo $Wonder_result;die;
			}else if($subscribe->httpStatus == 409){
				$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $subscribe->message)); //Already Email Exits
				echo $Wonder_result;die;
			}else{
				$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $subscribe->message));
				echo $Wonder_result;die;
			}
		}else{
			$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $msg_invalid_api));
			echo $Wonder_result;die;
		}
	}

	function Wonder_campaign($subscribe_email,$user_name,$msg_subscribed_success,$msg_invalid_api)
	{
		if(defined('cm_api_key') && defined('cm_list_id'))
		{
			require_once('campaignmonitor/csrest_subscribers.php');
			$cm_api_key = cm_api_key;
			$cm_list_id = cm_list_id;
			if(!isset($user_name)){
				$get_name = explode("@",$subscribe_email);
				$user_name = $get_name[0];
			}else{
				$user_name = $user_name;
			}
			$wrap = new CS_REST_Subscribers($cm_list_id, $cm_api_key);
			$result = $wrap->add(array(
			    'EmailAddress' => $subscribe_email,
			    'Name' => $user_name,
			    'Resubscribe' => true
			));
			
			if($result->response == $subscribe_email) {
			    $Wonder_result = json_encode(array('type'=>'fs_message', 'text' => $msg_subscribed_success));
				echo $Wonder_result;die;
			} else {
			   	$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $result->response->Message));
				echo $Wonder_result;die;
			}
		}else{
			$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $msg_invalid_api));
			echo $Wonder_result;die;
		}
	}


	function Wonder_mailerlite($subscribe_email,$user_name,$last_name=NULL,$phone_num=NULL,$comment=NULL,$msg_subscribed_success,$msg_subscribed_success,$msg_invalid_api)
	{

		if(defined('ml_api_key') && defined('ml_groupid'))
		{
			require_once 'mailerlite/Base/RestBase.php';
			require_once 'mailerlite/Base/Rest.php';
			require_once 'mailerlite/Subscribers.php';
			$ml_api_key = ml_api_key;
			$ml_groupid = ml_groupid;
			$sub_mailerlite = new MailerLite\Subscribers($ml_api_key);
			if(!isset($user_name)){
				$get_name = explode("@",$subscribe_email);
				$user_name = $get_name[0];
			}else{
				$user_name = $user_name;
			}
			$subscriber = array(
			    'email' => $subscribe_email,
			    'name' => $user_name,
			    'fields' => array( 
			       array( 'name' => 'last_name', 'value' => $last_name ),
			       array( 'name' => 'phone', 'value' => $phone_num ),
			       array( 'name' => 'comment', 'value' => $comment )
			    )
			);
			$subs_result = $sub_mailerlite->setId($ml_groupid)->add($subscriber);
			$result = json_decode($subs_result, true);
			if($result['email']){
				$Wonder_result = json_encode(array('type'=>'fs_message', 'text' => $msg_subscribed_success));
				echo $Wonder_result;die;
			}else{
				$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $result['error']['message']));
				echo $Wonder_result;die;
			}
		}else{
			$Wonder_result = json_encode(array('type'=>'fs_error', 'text' => $msg_invalid_api));
			echo $Wonder_result;die;
		}
	}

?>