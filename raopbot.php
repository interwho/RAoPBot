<?php
//RAoP Rep Bot - by interwhos
error_reporting(0);
set_time_limit(0);

#########################################################################################################

//Set Initial Variables
$offers = 0;
$thanks = 0;
$req = 0;
$given = 0;

#########################################################################################################

//Curl Grabber For Search
function curlGet($url)	{
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_USERAGENT, "r/Random_Acts_Of_Pizza Rep Bot by u/interwhos");
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	$return = curl_exec($curl);
	curl_close($curl);
	return $return;
}

//Date Converter
function time_ago($date,$granularity=2) {
    $difference = time() - $date;
    $periods = array(
        'year' => 31536000,
        'month' => 2628000,
        'week' => 604800, 
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1);

    foreach ($periods as $key => $value) {
        if ($difference >= $value) {
            $time = floor($difference/$value);
            $difference %= $value;
            $retval .= ($retval ? ' ' : '').$time.' ';
            $retval .= (($time > 1) ? $key.'s' : $key);
            $granularity--;
        }
        if ($granularity == '0') { break; }
    }
    if(strlen($retval) == 0) {
    	$retval = 'an instant';
    }
    return 'joined: '.$retval.' ago';      
}

//User Rater
function rateUser($post,$id) {
	//Get Username By Parsing Post Description
	preg_match('#http://www.reddit.com/user/(.*?)">#', $post->description, $username);
	$username = $username[0];
	$username = preg_replace('#http://www.reddit.com/user/#', '', $username);
	$username = preg_replace('#">#', '', $username);	
	//Get Username Info
	$response = curlGet("http://www.reddit.com/user/$username/about.json");
	$response = json_decode($response);
	$response = $response->{'data'};
	$acctage = $response->{'created'};
	$karma = $response->{'link_karma'};
	$karma = $karma + $response->{'comment_karma'};
	$acctage = time_ago($acctage);
	//Search Username And Get Variables
	$response = curlGet("http://www.reddit.com/r/Random_Acts_Of_Pizza/search.xml?syntax=cloudsearch&q=author%3A%27$username%27&restrict_sr=on&sort=new");
	$req = substr_count(strtoupper($response), '<TITLE>[REQUEST]');
	$req = $req + substr_count(strtoupper($response), '<TITLE>{REQUEST}');
	$req = $req + substr_count(strtoupper($response), '<TITLE>(REQUEST)');
	$req = $req + substr_count(strtoupper($response), '<TITLE>[REQ]');
	$req = $req + substr_count(strtoupper($response), '<TITLE>(REQ)');
	$req = $req + substr_count(strtoupper($response), '<TITLE>{REQ}');
	$req = $req + substr_count(strtoupper($response), '<TITLE>REQUEST');
	$offers = substr_count(strtoupper($response), '<TITLE>[OFFER]');
	$offers = $offers + substr_count(strtoupper($response), '<TITLE>(OFFER)');
	$offers = $offers + substr_count(strtoupper($response), '<TITLE>{OFFER}');
	$offers = $offers + substr_count(strtoupper($response), '<TITLE>OFFER');
	$offers = $offers + substr_count(strtoupper($response), '<TITLE>[CONTEST]');
	$offers = $offers + substr_count(strtoupper($response), '<TITLE>(CONTEST)');
	$offers = $offers + substr_count(strtoupper($response), '<TITLE>{CONTEST}');
	$offers = $offers + substr_count(strtoupper($response), '<TITLE>CONTEST');
	$offers = $offers + substr_count(strtoupper($response), '<TITLE>[CONTEST/OFFER]');
	$offers = $offers + substr_count(strtoupper($response), '<TITLE>[OFFER/CONTEST]');
	$thanks = substr_count(strtoupper($response), '<TITLE>[THANKS]');
	$thanks = $thanks + substr_count(strtoupper($response), '<TITLE>THANKS');
	$thanks = $thanks + substr_count(strtoupper($response), '<TITLE>(THANKS)');
	$thanks = $thanks + substr_count(strtoupper($response), '<TITLE>{THANKS}');
	$response = curlGet("http://www.reddit.com/r/Random_Acts_Of_Pizza/search.xml?syntax=cloudsearch&q=$username+%5BTHANKS%5D&restrict_sr=on&sort=new");
	$given = substr_count(strtoupper($response), '<TITLE>[THANKS]') - $thanks;
	$given = $given + substr_count(strtoupper($response), '<TITLE>(THANKS)');
	$given = $given + substr_count(strtoupper($response), '<TITLE>THANK');
	$given = $given + substr_count(strtoupper($response), '<TITLE>{THANKS}');
	//Add The Comment
	$urltopost = "https://ssl.reddit.com/api/login/RAoPBot";
	$datatopost = array (
		"user" => "RAoPBot",
		"passwd" => "PASSWORD",
		"api_type" => "json",
	);
	$ch = curl_init ($urltopost);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $datatopost);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "r/Random_Acts_Of_Pizza Rep Bot by u/interwhos");
	$loginvars = curl_exec($ch);
	$loginvars = json_decode($loginvars);
	$loginvars = $loginvars->{'json'};
	$loginvars = $loginvars->{'data'};
	$hash = $loginvars->{'modhash'};
	$cookie = $loginvars->{'cookie'};
	$cookie = urlencode($cookie);
	$idl = $id;
	$id = 't3_'.$id;
	if($username == 'interwhos') {
		$given = 'Over 9000';
	}
	$message = "Stats for **[$username](http://www.reddit.com/r/Random_Acts_Of_Pizza/search?q=author%3A%27$username%27&restrict_sr=on)** on RAoP\n\n
---------------------------------------\n\n
* $req Requested
* $offers Offered
* $given Been Thanked
* $thanks Given Thanks\n\n
---------------------------------------\n\n
$acctage - total karma: $karma\n\n
---------------------------------------\n\n
[report link](http://www.reddit.com/message/compose?to=%2Fr%2FRandom_Acts_Of_Pizza&subject=RAoP%20Bot%20Link%20Reported%20-%20".urlencode('http://redd.it/'.$idl).") or [send feedback](http://www.reddit.com/message/compose?to=interwhos&subject=RAoP%20Bot%20Feedback!)\n\n
---------------------------------------\n\n
[Just Married! I <3 U mcredson!!!1!](http://www.reddit.com/r/Random_Acts_Of_Pizza/comments/tynt8/contest_will_u_mary_me/c4qtq53)\n\n
---------------------------------------";
// Oops sorry I droped my egg on your face, but you did win a pizza
	$urltopost = "http://www.reddit.com/api/comment";
	$datatopost = array(
		"thing_id" => $id,
		"text" => $message,
		"uh" => $hash
	);
	$cookie = "reddit_session=".$cookie;
	$ch = curl_init($urltopost);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $datatopost);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "r/Random_Acts_Of_Pizza Rep Bot by u/interwhos");
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	$returndata = curl_exec($ch);
	//10 Minute Timeout (For New Accounts)
	sleep(600);
}

#########################################################################################################

//Start Script Logic
$Random_Acts_Of_Pizzabase = curlGet("http://www.reddit.com/r/Random_Acts_Of_Pizza/new.xml?sort=new");
$Random_Acts_Of_Pizzabase = preg_replace('#<title>(.*?)</image>#', '//', $Random_Acts_Of_Pizzabase);
$Random_Acts_Of_Pizzabase = simplexml_load_string($Random_Acts_Of_Pizzabase);
$Random_Acts_Of_Pizzabase = $Random_Acts_Of_Pizzabase->channel;

foreach ($Random_Acts_Of_Pizzabase->item as $post) {
	$url = $post->guid;
	preg_match('#/r/Random_Acts_Of_Pizza/comments/(.*?)/#', $url, $id);
	$id = $id[0];
	$id = preg_replace('#/r/Random_Acts_Of_Pizza/comments/#', '', $id);
	$id = preg_replace('#/#', '', $id);
	$ratingcheck = curlGet($url);
	if(preg_match("/RAoPBot/i", $ratingcheck)) {
		exit();
	} else {
		rateUser($post,$id);
	}
}
?>