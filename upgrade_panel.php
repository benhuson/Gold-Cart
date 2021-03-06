<?php
/**
 * Activate Gold Cart plugin
 */
function wpsc_activate_gold_module() {
	if ( isset( $_POST['activate_gold_module'] ) && $_POST['activate_gold_module'] == 'true' ) {
		if ( $_POST['activation_name'] != null ) {
			update_option( 'activation_name', $_POST['activation_name'] );
		}

		if ( isset( $_POST['activation_key'] ) ){
			update_option( 'activation_key', $_POST['activation_key'] );
		}
		
		//Action to perform
		if (isset($_POST['submit_values'])) {
			//register api
			$action = 'register';
		} 
		if (isset($_POST['reset_values'])) {
			//reset api
			$action = 'reset';
			update_option( 'activation_key', '');
		}

		$url = "https://wpecommerce.org/wp-goldcart-api/api_register.php";
		$params = array (
			'api'		=> 'v2',
			'name'		=> base64_encode( $_POST['activation_name'] ),
			'key'		=> base64_encode( $_POST['activation_key'] ),
			'url'		=> base64_encode( esc_url_raw( site_url() ) ),
			'action'	=> $action
		);
		$url = add_query_arg( $params, $url );
		
		$args = array(
			'httpversion' => '1.0',
			'sslverify'	  => false,
			'timeout'	  => 15,
			'user-agent'  => 'Gold Cart/' . WPSC_GOLD_VERSION . '; ' . get_bloginfo( 'url' ),	
		);
		$returned_value = wp_remote_retrieve_body( wp_remote_get( $url, $args ) );

		if ( $returned_value === 'MQ==' ) {
			if( get_option( 'activation_state' ) != 'true' ) {
				update_option( 'activation_state','true' );
				gold_shpcrt_install();
			}
			?>
				<div class="updated" style="min-width:45%; max-width:463px;">
					<p>
						<?php _e( 'Thanks! The Gold Cart upgrade has been activated.', 'wpsc_gold_cart' ); ?><br />
						<?php printf( __( 'New options have been added to %s, and your payment gateway list has been extended.', 'wpsc_gold_cart' ), sprintf( '<a href="options-general.php?page=wpsc-settings&tab=presentation">%s</a>', __( 'Settings -> Presentation', 'wpsc_gold_cart' ) ) ); ?>
					</p>
				</div>
			<?php
		} else if  ( $returned_value === 'Mg==' ){
			//Reset API
			update_option( 'activation_state',"false" );
			echo '<div class="error"><p>Gold Cart upgrade has been deactivated!</p></div>';
		} else {
			echo '<div class="error"><p>'.$returned_value.'</p></div>';
		}
	}
}
add_action( 'wpsc_gold_module_activation','wpsc_activate_gold_module' );

/**
 * Registration Form
 */
function wpsc_gold_activation_form() {
	?>
	<div class="postbox">
		<h3 class="hndle"><?php _e( 'Gold Cart Registration', 'wpsc_gold_cart' );?></h3>
		<?php if ( get_option( 'activation_state' ) == 'true' ) { ?>
		<p>
			<?php _e( 'Gold Cart is currently registered.', 'wpsc_gold_cart' ); ?>
			<img align="middle" src="<?php echo WPSC_CORE_IMAGES_URL; ?>/tick.png" alt="" />
		</p>
		<?php	} else { ?>
		<p>
			<img align="middle" src="<?php echo WPSC_CORE_IMAGES_URL; ?>/cross.png" alt="" />
			<?php _e( 'Gold Cart is currently not registered.', 'wpsc_gold_cart' ); ?>
		</p>
		<?php } // End Registration state ?>
		<p>
			<label for="activation_name"><?php _e( 'Name ', 'wpsc_gold_cart' ); ?>:</label>
			<input type="text" id="activation_name" name="activation_name" size="48" value="<?php echo get_option( 'activation_name' ); ?>" class="text" />
		</p>
		<p>
			<label for="activation_key"><?php _e( 'API Key ', 'wpsc_gold_cart' ); ?>:</label>
			<input type="text" id="activation_key" name="activation_key" size="48" value="<?php echo get_option( 'activation_key' ); ?>" class="text" />
		</p>
		<p>
			<input type="hidden" value="true" name="activate_gold_module" />
			<input type="submit" class="button-primary" value="<?php _e( 'Submit', 'wpsc_gold_cart' ); ?>" name="submit_values" />
			<input type="submit" class="button" value="<?php _e( 'Reset API Key', 'wpsc_gold_cart' ); ?>" name="reset_values" />
		</p>
		<?php if ( ! function_exists( 'curl_init' ) ) : ?>
			<p style='color: red; font-size:8pt; line-height:10pt;'>
				<?php _e( 'In order to register your API information your server requires cURL which is not installed on this server. We will attempt to use fsockopen as an alternate method to register Gold Cart. ', 'wpsc_gold_cart' ); ?>
			</p>
		<?php endif; ?>
		<?php if ( ! function_exists( 'fsockopen' ) && ! function_exists( 'curl_init' )) : ?>
			<p style='color: red; font-size:8pt; line-height:10pt;'>
				<?php _e( 'In order to register your API information your server requires cURL or the fscockopen extension which are not installed on this server, you may need to contact your web hosting provider to get them set up. ', 'wpsc_gold_cart' ); ?>
			</p>
		<?php endif; ?>	
	</div>
	<?php
}
add_action( 'wpsc_gold_module_activation_forms','wpsc_gold_activation_form' );
?>