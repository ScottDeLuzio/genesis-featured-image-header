<?php
class Genesis_Featured_Image_Header {
	public function gfih_run() {
		global $wp_version;

		if ( 'genesis' !== basename( get_template_directory() ) ) {
			add_action( 'admin_init', array( $this, 'gfih_deactivate' ) );
			add_action( 'admin_notices', array( $this, 'gfih_error_message' ) );
			return;
		}

		if ( version_compare( $wp_version, '3.5', '<' ) ) {
			add_action ( 'admin_init', array( $this, 'gfih_not_wp_version' ) );
			return;
		}
	}
	/**	
	 * deactivates the plugin if Genesis isn't running
	 *
	 * @since  1.0.0
	 * 
	 */
	public function gfih_deactivate() {	
        deactivate_plugins( plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/genesis-featured-image-header.php' );
	}

	/**
	 * error message if we're not using the Genesis Framework
	 *
	 * @since  1.0.0
	 * 
	 */
	public function gfih_error_message() {
		$url = 'http://www.shareasale.com/r.cfm?B=346198&U=947944&M=28169&urllink=';

		$error = sprintf( wp_kses( __( 'Sorry, Genesis Featured Image Header works only with the <a href="%s">Genesis Framework</a>. It has been deactivated.', 'genesis-featured-image-header' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( $url ) );

		echo '<div id="message" class="error"><p>' . $error . '</p></div>';

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
	/**
	 * error message if the wp_version it's not the minimum needed
	 *
	 * @since 1.0.0
	 */
	function gfih_not_wp_version() {

		wp_die( __( 'This plugin requires WordPress version 3.5 or higher.', 'genesis-featured-image-header' ) );
	}
}