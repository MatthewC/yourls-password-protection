<?php
/*
Plugin Name: YOURLSs Password Protection
Plugin URI: https://mateoc.net/b_plugin/yourls_PasswordProtection/
Description: This plugin enables the feature of password protecting your short URLs!
Version: 1.1
Author: Matthew
Author URI: https://mateoc.net/
*/

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Hook our custom function into the 'pre_redirect' event
yourls_add_action( 'pre_redirect', 'warning_redirection' );

// Custom function that will be triggered when the event occurs
function warning_redirection( $args ) {
	global $ydb;

	if( !isset($ydb->option[ 'matthew_pwprotection' ]) ){
		yourls_add_option( 'matthew_pwprotection', 'null' );
	}

	$matthew_pwprotection_fullurl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$matthew_pwprotection_urlpath = parse_url( $matthew_pwprotection_fullurl, PHP_URL_PATH );
	$matthew_pwprotection_pathFragments = explode( '/', $matthew_pwprotection_urlpath );
	$matthew_pwprotection_short = end( $matthew_pwprotection_pathFragments );
	
	$matthew_pwprotection_array = json_decode( $ydb->option[ 'matthew_pwprotection' ], true );

	if( array_key_exists( $matthew_pwprotection_short, $matthew_pwprotection_array ) ){
		if( isset( $_POST[ 'password' ] ) && $_POST[ 'password' ] == $matthew_pwprotection_array[ $matthew_pwprotection_short ] ){ //Check if password is submited, and if it matches the DB
			$url = $args[ 0 ];
			header("Location: $url"); //Redirects client
			die();
		} else {
			$error = ( isset( $_POST[ 'password' ] ) ? "\n<br><span style='color: red;'><u>". yourls__( "Incorrect Password", "matthew_pwp" ). "</u></span>" : "");
			$matthew_ppu =    yourls__( "Password Protected URL",                       "matthew_pwp" ); //Translate Password Title
			$matthew_ph =     yourls__( "Password"                                    , "matthew_pwp" ); //Translate the word Password
			$matthew_sm =     yourls__( "Please enter the password below to continue.", "matthew_pwp" ); //Translate the main message
			$matthew_submit = yourls__( "Send!"                                       , "matthew_pwp" ); //Translate the Submit button
			//Displays main "Insert Password" area
			echo <<<PWP
			<style>
				#password {
					background-color: #e8e8e8;
					box-shadow: 0 10px 16px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19) !important;

					width: 400px !important;
					height: 220px !important;

					position: fixed;
					top: 50%;
					left: 50%;
					/* bring your own prefixes */
					transform: translate(-50%, -50%);
				}

				#password form {
					margin-top: 30px;
					margin-left: 14px;
					width: 95%;
					height: 20px;
				}

				#password input[type="password"]{
					box-sizing: border-box;
					border-radius: 4px;
					margin-left: 14px;
					padding: 10px;
					border: none;
					height: 30px;
					width: 84%;
					/* background-color: #3CBC8D;
					color: white; */
				}

				#password input[type="password"]:focus{
					outline: none;
					background-color: inherit;
				}

				#password input[type="submit"]{
					box-sizing: border-box;
					border-radius: 4px;
					margin-left: 14px;
					border: none;
					height: 30px;
					width: 84%;

					background-color: lightgrey;
					outline: aqua !important;
					outline-color: grey;
				}
				#password input[type="submit"]:focus{
					box-shadow: 0 10px 16px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19) !important;
					background-color: inherit;
					outline: aqua !important;
					outline-color: red;
				}
			</style>
			<div id="password">
				<center>
					<br><span style="font-size: 35px;"><u>$matthew_ppu</u></span>$error
				</center>
				<form method="post">
					<p><i>$matthew_sm</i></p>
					<input type="password" name="password" placeholder="$matthew_ph"><br><br>
					<input type="submit" value="$matthew_submit"><br><br>
				</form>
			</div>
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
	global $ydb;
	if( isset( $_POST[ 'checked' ] ) && isset( $_POST[ 'password' ] ) || isset( $_POST[ 'unchecked' ] ) ) {
		matthew_pwprotection_process_new();
		matthew_pwprotection_process_display();
	} else {
		if( !isset( $ydb->option[ 'matthew_pwprotection' ] ) ){
			yourls_add_option( 'matthew_pwprotection', 'null' );
		}

		matthew_pwprotection_process_display();
	}
}

// Set/Delete password from DB
function matthew_pwprotection_process_new() {
	global $ydb;
	
	if( isset( $_POST[ 'checked' ] ) ){
		yourls_update_option( 'matthew_pwprotection', json_encode( $_POST[ 'password' ] ) );
	}
	if( isset( $_POST[ 'unchecked' ] ) ){
		$matthew_pwprotection_array = json_decode( $ydb->option[ 'matthew_pwprotection' ], true ); //Get's array of currently active Password Protected URLs
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
		$matthew_pwprotection_array = json_decode( $ydb->option[ 'matthew_pwprotection' ], true ); //Get's array of currently active Password Protected URLs
		if( strlen( $url ) > 31 ) { //If URL is too long it will shorten it
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
