<?php
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
function rateUser($username) {
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
	$message = "Stats for <a href='http://www.reddit.com/r/Random_Acts_Of_Pizza/search?q=author%3A%27$username%27&restrict_sr=on'>$username</a> on RAoP<br>
---------------------------------------<br>
* $req Requested<br>
* $offers Offered<br>
* $given Been Thanked<br>
* $thanks Given Thanks<br>
---------------------------------------<br>
$acctage - total karma: $karma<br>
---------------------------------------";
	return $message;
}

#########################################################################################################

if(isset($_GET['username'])) { echo rateUser($_GET['username']); echo '<br><br><hr><br>'; }
?>
Usage: Rates A User On Reddit's Random_Acts_Of_Pizza<br><br>
<form method="get">Enter The Username You Would Like Rated: <input type="text" name="username"><br><br><button type="submit">Get Rating</button></form>