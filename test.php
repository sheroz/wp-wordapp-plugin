<?php
// https://developer.wordpress.com/docs/oauth2/

// currently we have:
// - OAuth2 authentication
// - WORDAPP API
// -- Event Registration Mechanism: [event: task_approved] +documented
// -- requesting/pooling data from wordapp: projects (could not find yet documentation )

// wordapp plugin uses client_id & client_secret based authentication - per domain_id (or may be per publisher with subscribed list of domain_id (s) ???)

// how to centrally change access codes for plugins?
// How to easily connect and manage installed plugins to wordapp api centrally?
// domain url based sending of plugin's credentials?

/*
	$curl = curl_init( 'https://public-api.wordpress.com/oauth2/token' );
	curl_setopt( $curl, CURLOPT_POST, true );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, array(
		'client_id' => your_client_id,
		'client_secret' => your_client_secret_key,
		'grant_type' => 'password',
		'username' => your_wpcom_username,
		'password' => your_wpcom_password,
	) );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
	$auth = curl_exec( $curl );
	$auth = json_decode($auth);
	$access_key = $auth->access_token;
*/
/*
//Oauth 2.0: exchange token for session token so multiple calls can be made to api
if(isset($_REQUEST['code'])){
    $_SESSION['accessToken'] = wordapp_io_get_oauth2_token($_REQUEST['code']);
}
*/

// test
$auth = wordapp_io_get_oauth2_token('');

if(!is_null($auth))
{
	$accessToken = $auth->access_token;
	echo "accessToken: ".$auth->access_token;
	echo "<br/>";
	echo "token_type: ".$auth->token_type;
	echo "<br/>";
	echo "expires_in: ".$auth->expires_in;
	echo "<br/>";
	echo "refresh_token: ".$auth->refresh_token;
	echo "<br/>";
	echo "scope: ".$auth->scope;
	echo "<br/>";
	echo "created_at: ".$auth->created_at;
}
exit;

//returns session token for calls to API using oauth 2.0
function wordapp_io_get_oauth2_token($code) {

    global $client_id;
    global $client_secret;
    global $redirect_uri;

    $oauth2token_url = "https://app.wordapp.io/oauth/token";
	
    $clienttoken_post = array(
		"username" => "sheroz@copypanthers.com",
		"password" => "ALLRhD8d",
		"grant_type" => "password",
    );

	/*
	// client_id & client_secret based authentication
	
    $clienttoken_post = array(
		"grant_type" => "client_credentials"
		"client_id" => ""
		"client_secret" => ""
    );
	
	*/
    $curl = curl_init($oauth2token_url);

    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $clienttoken_post);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $json_response = curl_exec($curl);
    curl_close($curl);

    $auth = json_decode($json_response);

    if (isset($auth->refresh_token)){
        //refresh token only granted on first authorization for offline access
        //save to db for future use (db saving not included in example)
        global $refreshToken;
        $refreshToken = $auth->refresh_token;
    }

    return $auth;
}

?>