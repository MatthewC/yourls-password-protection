<?php
/*
Plugin Name: YOURLSs Password Protection
Plugin URI: https://matc.io/yourls-password
Description: This plugin enables the feature of password protecting your short URLs!
Version: 1.5
Author: Matthew
Author URI: https://matc.io
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

	if( array_key_exists( $matthew_pwprotection_short, (array)$matthew_pwprotection_array ) ){
		// Check if password is submited, and if it matches the DB
		if( isset( $_POST[ 'password' ] ) && password_verify( $_POST[ 'password' ], $matthew_pwprotection_array[ $matthew_pwprotection_short ]) ){
			$url = $args[ 0 ];
			
			// Redirect client
			header("Location: $url");

			die();
		} else {
			$error = ( isset( $_POST[ 'password' ] ) ? "<script>alertify.error(\"Incorrect Password, try again\")</script>" : "");
			$matthew_ppu =    yourls__( "Password Protected URL",                       "matthew_pwp" ); // Translate Password Title
			$matthew_ph =     yourls__( "Password"                                    , "matthew_pwp" ); // Translate the word Password
			$matthew_sm =     yourls__( "Please enter the password below to continue.", "matthew_pwp" ); // Translate the main message
			$matthew_submit = yourls__( "Send!"                                       , "matthew_pwp" ); // Translate the Submit button
			// Displays main "Insert Password" area
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
	// Verify nonce token.
	yourls_verify_nonce( "matthew_pwprotection_update" );

	$matthew_pwprotection_array =  json_decode(yourls_get_option('matthew_pwprotection'), true);

	foreach( $_POST[ 'password' ] as $url => $url_password) {
		if($url_password != "DONOTCHANGE_8fggwrFrRXvqndzw") {
			$_POST[ 'password' ][ $url ] = password_hash($url_password, PASSWORD_BCRYPT);
		} else {
			$_POST[ 'password' ][ $url ] = $matthew_pwprotection_array[ $url ];
		}
	}

	// Update database
	yourls_update_option( 'matthew_pwprotection', json_encode( $_POST[ 'password' ] ) );
	
	echo "<p style='color: green'>Success!</p>";
}

// Display Form
function matthew_pwprotection_process_display() {
	$ydb = yourls_get_db();

	// get limit and offset for pagination
	$limit = 50;
	$offset = @$_GET['p'];
	if ($offset == NULL){
		$offset = 0;
	}else{
		if ((int)$offset < 0){
			$offset = 1;
		}
		$offset = ((int)$offset - 1) * $limit;
	}

	$where = '1=1';
	$binds = array(
		'limit'=> $limit,
		'offset'=> $offset,
	);

	$short_url_to_filter = @$_GET['q'];
	if ($short_url_to_filter != NULL && strlen($short_url_to_filter)>0){
		$where = 'keyword LIKE :keyword';
		$binds['keyword'] = '%'.$short_url_to_filter.'%';
	}

	$table = YOURLS_DB_TABLE_URL;
	$sql = "SELECT * FROM `$table` WHERE $where LIMIT :limit OFFSET :offset";
	
	$query = $ydb->fetchAll($sql, $binds);

	$matthew_su = yourls__( "Short URL"   , "matthew_pwp" ); // Translate "Short URL"
	$matthew_ou = yourls__( "Original URL", "matthew_pwp" ); // Translate "Original URL"
	$matthew_pw = yourls__( "Password"    , "matthew_pwp" ); // Translate "Password"

	// Protect action with nonce
	$matthew_pwprotection_noncefield = yourls_nonce_field( "matthew_pwprotection_update" );

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
		<form method="post" id="form_submit">
		<label>Search Short URL:</label>
		<input type="text" id="txt_search" size="20">
		<input id="btn_search" type="button" value="Search">
			<table>
				<tr>
					<th>$matthew_su</th>
					<th>$matthew_ou</th>
					<th>$matthew_pw</th>
				</tr>
TB;

	foreach( $query as $link ) { // Displays all shorturls in the YOURLS DB
		$short = $link["keyword"];
		$url = $link["url"];
		$matthew_pwprotection_array =  json_decode(yourls_get_option('matthew_pwprotection'), true); // Get array of currently active Password Protected URLs
		if( strlen( $url ) > 51 ) { // If URL is too long, shorten it with '...'
			$sURL = substr( $url, 0, 30 ). "...";
		} else {
			$sURL = $url;
		}
		if( array_key_exists( $short, (array)$matthew_pwprotection_array ) ){ // Check if URL is currently password protected or not
			$text = yourls__( "Enable?" );
			$password = "DONOTCHANGE_8fggwrFrRXvqndzw";
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
						<input id="$short" type="password" name="password[$short]" style="$style" value="$password" onkeypress="return checkIfSubmitPassword(event);" placeholder="Password..."$disabled ><br>
					</td>
				</tr>
TABLE;
	}

	$current_page = $offset/$limit+1;
	$previous_page = $current_page-1;
	$next_page = $current_page+1;
	$total_data = count($query);

	echo <<<END
			</table>
			$matthew_pwprotection_noncefield
			<input id="btn_previous" type="button" value="Previous">
			<input id="btn_next" type="button" value="Next">
			<p><input id="btn_submit" type="button" value="Submit"></p>
		</form>
	</div>
	<script>
		$("#txt_search").val("$short_url_to_filter");
		$("#txt_search").focus();

		function filterShortURL(){
			var current_url = window.location.href;
			current_url = current_url.replace(/\&p\=\d+/, "");
			let shortURLToFind = $("#txt_search").val();
			if (current_url.includes("&q=")){
				window.location.href = current_url.replace("&q=$short_url_to_filter", "&q="+shortURLToFind);
			}else{
				window.location.href += "&q="+shortURLToFind;
			}
		}

		function formSubmit(){
			$('#form_submit').submit();
		}

		function checkIfSubmitPassword(e) {
			e = e || window.event;
			if (e.which === 13) {
				formSubmit()
			}
			return true;
		}

		$(document).ready(function(){
			let total_data = $total_data;
			let current_page = $current_page;

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

			$( "#btn_previous" ).click(function() {
				if (current_page > 1 && window.location.href.includes("&p=$current_page")){
					window.location.href = window.location.href.replace( "&p=$current_page", "&p=$previous_page" );
				}
			});

			$( "#btn_next" ).click(function() {
				if (window.location.href.includes("&p=")){
					window.location.href = window.location.href.replace( "&p=$current_page", "&p=$next_page" );
				}else{
					window.location.href += "&p=$next_page";
				}
			});

			$( "#btn_search" ).click(function() {
				filterShortURL();
			});

			$( "#txt_search" ).on('keypress',function(e) {
				if(e.which === 13) {
					e.preventDefault();
					filterShortURL();
					e.stopPropagation();
				}
			});

			$( "#btn_submit" ).click(function() {
				formSubmit();
			});
			

			// go to previus page when not data
			if (current_page > 1 && total_data == 0){
				$("#btn_previous").trigger("click");
			}
		});
	</script>
END;
}
?>
