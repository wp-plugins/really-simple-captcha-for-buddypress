<?php
/**
 * Plugin Name: Really Simple CAPTCHA for Buddypress
 * Plugin URI: http://b-ernie.com
 * Description: Integrates the Really Simple Captcha plugin to the Buddypress Registration page. Thus, it requires the activation of both RS Captcha and Buddypress plugins before it will work.
 * Version: 1.2
 * Author: Bernie Tisbe
 * Author URI: http://b-ernie.com
 * License: GPL2
 */
 
/*  Copyright 2015  Bernie Bustamante  (email : b@b-ernie.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$active_plugins = apply_filters('active_plugins',get_option('active_plugins'));


if (in_array('buddypress/bp-loader.php', $active_plugins) && in_array('really-simple-captcha/really-simple-captcha.php', $active_plugins)) {
	
	/** Display the form **/
	add_action( 'bp_account_details_fields', 'b_rcaptcha_bpress_bp_register_captcha');
	function b_rcaptcha_bpress_bp_register_captcha() {
	
			$captcha_instance = new ReallySimpleCaptcha();
			$word = $captcha_instance->generate_random_word();
			$prefix = mt_rand();
			$image_num = $captcha_instance->generate_image( $prefix, $word );
			?>
			<label for="rs_captcha">Human Test</label>
				<?php do_action( 'b_rcaptcha_bpress_bp_register_captcha_error' ); ?> 
				<img src="<?php echo plugins_url( 'really-simple-captcha/tmp/' . $image_num ); ?>" alt="Captcha" />
				<input type="text" name="rs_captcha" value="" id="rs_captcha" style="width:100px;"/>
				<input type="hidden" name="rs_captcha_val" value="<?php echo $prefix; ?>" />
			<?php
			
	}
	
	
	/** Validate the form **/	
	add_action( 'bp_signup_pre_validate', 'b_rcaptcha_bpress_bp_register_captcha_validate');
	function b_rcaptcha_bpress_bp_register_captcha_validate () {
		$captcha_instance = new ReallySimpleCaptcha();
		
		$rs_captcha_input = isset( $_POST['rs_captcha'] ) ? $_POST['rs_captcha'] : '';
		$rs_captcha_file = isset( $_POST['rs_captcha_val'] ) ? $_POST['rs_captcha_val'] : '';
		
		$correct = $captcha_instance->check( $rs_captcha_file, $rs_captcha_input );
		
		if ( class_exists( 'ReallySimpleCaptcha' ) ) {
			
			global $bp;
			
			if ( !$correct ) {
				
				$bp->signup->errors['rs_captcha'] = add_action( 'b_rcaptcha_bpress_bp_register_captcha_error', 'b_rcaptcha_bpress_bp_register_captcha_error');
						   
			} else {
				// validation success, remove the files
				$captcha_instance->remove( $rs_captcha_file );
			}
		}
				
	}
	function b_rcaptcha_bpress_bp_register_captcha_error () {
		echo '<div class="error">Human test validation failed!</div>';
	
	}
}

else {
	
	// deactivate the plugin, it won't work at all
	function b_rcaptcha_bpress_deactivate() {
		  deactivate_plugins( plugin_basename( __FILE__ ) );
	}
	add_action( 'admin_init', 'b_rcaptcha_bpress_deactivate' );
	
			
	// notice
	function b_rcaptcha_bpress_admin_notice() {
	?>
	<div class="error">
		<p>RSCaptcha for Buddypress plugin not installed: The plugin failed to install or was deactivated because neither <a href="https://wordpress.org/plugins/really-simple-captcha/" target="_blank">Really Simple Captcha</a> or <a href="https://wordpress.org/plugins/buddypress/" target="_blank">Buddypress</a> plugin is not <strong>activated</strong>. Please activate them first for this to work.</p>
	</div>
	<?php
	if ( isset( $_GET['activate'] ) )
	unset( $_GET['activate'] );
	}
	add_action( 'admin_notices', 'b_rcaptcha_bpress_admin_notice' );
	
}