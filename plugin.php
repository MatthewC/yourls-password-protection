<?php
/*
Plugin Name: YOURLSs Password Protection
Plugin URI: https://mateoc.net/b_plugin/yourls_PasswordProtection/
Description: This plugin enables the feature of password protecting your short URLs!
Version: 1.3
Author: Matthew
Author URI: https://mateoc.net/
*/

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Hook our custom function into the 'pre_redirect' event
yourls_add_action( 'pre_redirect', 'warning_redirection' );

// Custom function that will be triggered when the event occurs
function warning_redirection( $args ) {		
	$matthew_pwprotection_array = json_decode(yourls_get_option('matthew_pwprotection'), true);
	if ($matthew_pwprotection_array === false) {
		yourls_add_option('matthew_pwprotection', 'null');
		$matthew_pwprotection_array = json_decode(yourls_get_option('matthew_pwprotection'), true);
		if ($matthew_pwprotection_array === false) {
			die("Unable to properly enable password protection due to an apparent problem with the database.");
		}
	}

	$matthew_pwprotection_fullurl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$matthew_pwprotection_urlpath = parse_url( $matthew_pwprotection_fullurl, PHP_URL_PATH );
	$matthew_pwprotection_pathFragments = explode( '/', $matthew_pwprotection_urlpath );
	$matthew_pwprotection_short = end( $matthew_pwprotection_pathFragments );
	
	if( array_key_exists( $matthew_pwprotection_short, $matthew_pwprotection_array ) ){
		if( isset( $_POST[ 'password' ] ) && $_POST[ 'password' ] == $matthew_pwprotection_array[ $matthew_pwprotection_short ] ){ //Check if password is submited, and if it matches the DB
			$url = $args[ 0 ];
			header("Location: $url"); //Redirects client
			die();
		} else {
			$error = ( isset( $_POST[ 'password' ] ) ? "<script>alertify.error(\"Incorrect Password, try again\")</script>" : "");
			$matthew_ppu =    yourls__( "Password Protected URL",                       "matthew_pwp" ); //Translate Password Title
			$matthew_ph =     yourls__( "Password"                                    , "matthew_pwp" ); //Translate the word Password
			$matthew_sm =     yourls__( "Please enter the password below to continue.", "matthew_pwp" ); //Translate the main message
			$matthew_submit = yourls__( "Send!"                                       , "matthew_pwp" ); //Translate the Submit button
			//Displays main "Insert Password" area
			echo <<<PWP
			<html>
				<head>
					<title>Redirection Notice</title>
					<style>
						@import url(https://weloveiconfonts.com/api/?family=fontawesome);
						@import url(https://meyerweb.com/eric/tools/css/reset/reset.css);
						[class*="fontawesome-"]:before {
						  font-family: 'FontAwesome', sans-serif;
						}
						* {
						  -moz-box-sizing: border-box;
							   box-sizing: border-box;
						}
						*:before, *:after {
						  -moz-box-sizing: border-box;
							   box-sizing: border-box;
						}

						body {
						  background: #2c3338;
						  color: #606468;
						  font: 87.5%/1.5em 'Open Sans', sans-serif;
						  margin: 0;
						}

						a {
						  color: #eee;
						  text-decoration: none;
						}

						a:hover {
						  text-decoration: underline;
						}

						input {
						  border: none;
						  font-family: 'Open Sans', Arial, sans-serif;
						  font-size: 14px;
						  line-height: 1.5em;
						  padding: 0;
						  -webkit-appearance: none;
						}

						p {
						  line-height: 1.5em;
						}

						.clearfix {
						  *zoom: 1;
						}
						.clearfix:before, .clearfix:after {
						  content: ' ';
						  display: table;
						}
						.clearfix:after {
						  clear: both;
						}

						.container {
						  left: 50%;
						  position: fixed;
						  top: 50%;
						  -webkit-transform: translate(-50%, -50%);
							  -ms-transform: translate(-50%, -50%);
								  transform: translate(-50%, -50%);
						}
						#login {
						  width: 280px;
						}

						#login form span {
						  background-color: #363b41;
						  border-radius: 3px 0px 0px 3px;
						  color: #606468;
						  display: block;
						  float: left;
						  height: 50px;
						  line-height: 50px;
						  text-align: center;
						  width: 50px;
						}

						#login form input {
						  height: 50px;
						}

						#login form input[type="text"], input[type="password"] {
						  background-color: #3b4148;
						  border-radius: 0px 3px 3px 0px;
						  color: #606468;
						  margin-bottom: 1em;
						  padding: 0 16px;
						  width: 230px;
						}

						#login form input[type="submit"] {
						  border-radius: 3px;
						  -moz-border-radius: 3px;
						  -webkit-border-radius: 3px;
						  background-color: #ea4c88;
						  color: #eee;
						  font-weight: bold;
						  margin-bottom: 2em;
						  text-transform: uppercase;
						  width: 280px;
						}

						#login form input[type="submit"]:hover {
						  background-color: #d44179;
						}

						#login > p {
						  text-align: center;
						}

						#login > p span {
						  padding-left: 5px;
						}
					</style>
					<!-- JavaScript -->
					<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.11.4/build/alertify.min.js"></script>

					<!-- CSS -->
					<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.11.4/build/css/alertify.min.css"/>
					<!-- Default theme -->
					<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.11.4/build/css/themes/default.min.css"/>
				</head>
				<body>
					<div class="container">
						<div id="login">
							<form method="post">
								<fieldset class="clearfix">
									<p><span class="fontawesome-lock"></span><input type="password" name="password" value="Password" onBlur="if(this.value == '') this.value = 'Password'" onFocus="if(this.value == 'Password') this.value = ''" required></p>
									<p><input type="submit" value="$matthew_submit"></p>
								</fieldset>
							</form>
						</div>
					</div>
					$error
				</body>
		</html>
PWP;
			die();
		}
	}
}

// Register plugin page in admin page
yourls_add_action( 'plugins_loaded', 'matthew_pwprotection_display_panel' );
function matthew_pwprotection_display_panel() {
	yourls_register_plugin_page( 'matthew_pwp', 'Password Protection', 'matthew_pwprotection_display_page' );
}

// Function which will draw the admin page
function matthew_pwprotection_display_page() {
	if( isset( $_POST[ 'checked' ] ) && isset( $_POST[ 'password' ] ) || isset( $_POST[ 'unchecked' ] ) ) {
		matthew_pwprotection_process_new();
		matthew_pwprotection_process_display();
	} else {
		if(yourls_get_option('matthew_pwprotection') !== false){
			yourls_add_option( 'matthew_pwprotection', 'null' );
		}
		matthew_pwprotection_process_display();
	}
}

// Set/Delete password from DB
function matthew_pwprotection_process_new() {	
	if( isset( $_POST[ 'checked' ] ) ){
		yourls_update_option( 'matthew_pwprotection', json_encode( $_POST[ 'password' ] ) );
	}
	if( isset( $_POST[ 'unchecked' ] ) ){
		$matthew_pwprotection_array = json_decode(yourls_get_option('matthew_pwprotection'), true); //Get's array of currently active Password Protected URLs
		foreach ( $_POST[ 'unchecked' ] as $matthew_pwprotection_unchecked ){
			unset($matthew_pwprotection_array[ matthew_pwprotection_unchecked ]);
		}
		yourls_update_option( 'matthew_pwprotection', json_encode( $_POST[ 'password' ] ) );
	}
	echo "<p style='color: green'>Success!</p>";
}

//Display Form
function matthew_pwprotection_process_display() {
	global $ydb;

	$table = YOURLS_DB_TABLE_URL;
	$query = $ydb->get_results( "SELECT * FROM `$table` WHERE 1=1" );

	$matthew_su = yourls__( "Short URL"   , "matthew_pwp" ); //Translate "Short URL"
	$matthew_ou = yourls__( "Original URL", "matthew_pwp" ); //Translate "Original URL"
	$matthew_pw = yourls__( "Password"    , "matthew_pwp" ); //Translate "Password"

	echo <<<TB
	<style>
	table {
		border-collapse: collapse;
		width: 100%;
	}

	th, td {
		text-align: left;
		padding: 8px;
	}

	tr:nth-child(even){background-color: #f2f2f2}
	tr:nth-child(odd){background-color: #fff}
	</style>
	<div style="overflow-x:auto;">
		<form method="post">
			<table>
				<tr>
					<th>$matthew_su</th>
					<th>$matthew_ou</th>
					<th>$matthew_pw</th>
				</tr>
TB;
	foreach( $query as $link ) { // Displays all shorturls in the YOURLS DB
		$short = $link->keyword;
		$url = $link->url;
		$matthew_pwprotection_array =  json_decode(yourls_get_option('matthew_pwprotection'), true); //Get's array of currently active Password Protected URLs
		if( strlen( $url ) > 51 ) { //If URL is too long it will shorten it
			$sURL = substr( $url, 0, 30 ). "...";
		} else {
			$sURL = $url;
		}
		if( array_key_exists( $short, $matthew_pwprotection_array ) ){ //Check's if URL is currently password protected or not
			$text = yourls__( "Enable?" );
			$password = $matthew_pwprotection_array[ $short ];
			$checked = " checked";
			$unchecked = '';
			$style = '';
			$disabled = '';
		} else {
			$text = yourls__( "Enable?" );
			$password = '';
			$checked = '';
			$unchecked = ' disabled';
			$style = 'display: none';
			$disabled = ' disabled';
		}

		echo <<<TABLE
				<tr>
					<td>$short</td>
					<td><span title="$url">$sURL</span></td>
					<td>
						<input type="checkbox" name="checked[{$short}]" class="matthew_pwprotection_checkbox" value="enable" data-input="$short"$checked> $text
						<input type="hidden" name="unchecked[{$short}]" id="{$short}_hidden" value="true"$unchecked>
						<input id="$short" type="password" name="password[$short]" style="$style" value="$password" placeholder="Password..."$disabled ><br>
					</td>
				</tr>
TABLE;
	}
	echo <<<END
			</table>
			<input type="submit" value="Submit">
		</form>
	</div>
	<script>
		$( ".matthew_pwprotection_checkbox" ).click(function() {
			var dataAttr = "#" + this.dataset.input;
			$( dataAttr ).toggle();
			if( $( dataAttr ).attr( 'disabled' ) ) {
				$( dataAttr ).removeAttr( 'disabled' );
				
				$( dataAttr + "_hidden" ).attr( 'disabled' );
				$( dataAttr + "_hidden" ).prop('disabled', true);
			} else {
				$( dataAttr ).attr( 'disabled' );
				$( dataAttr ).prop('disabled', true);				
				
				$( dataAttr + "_hidden" ).removeAttr( 'disabled' );
			}
		});
	</script>
END;
}
?>
