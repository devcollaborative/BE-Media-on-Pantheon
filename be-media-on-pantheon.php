<?php
/*
  Plugin Name: BE Media on Pantheon
  Plugin URI: https://github.com/devcollaborative/BE-Media-on-Pantheon
  Description: Programatically activate and set BE Media from Production URL when on lando local
  Author URI: https://devcollaborative.com
*/

/**
 * Deactivate BE Media from Production on pantheon env
 * could be named dev, test, live, or a multidev name
 * so just check 'lando' for local
 */
function lightship_activate_be_media(){
    
    if ( isset( $_ENV['PANTHEON_ENVIRONMENT'] ) && ( 'lando' == $_ENV['PANTHEON_ENVIRONMENT'] ) ):

        activate_plugin( WP_PLUGIN_DIR . '/BE-Media-from-Production/be-media-from-production.php' );

    else:
        if ( is_plugin_active( 'BE-Media-from-Production/be-media-from-production.php' ) ){
            deactivate_plugins( 'BE-Media-from-Production/be-media-from-production.php' );
        }
    endif; 

}
add_action('admin_init', 'lightship_activate_be_media'); 

/**
 * If this is a local "lando" env
 * Set media URL for BE Media From Production plugin
 * If live site returns 200/OK, use live env media
 * Else use dev env media
 * if it's not a local lando site, return null
 * https://developer.wordpress.org/reference/functions/deactivate_plugins/
 */
function lightship_be_media_from_production(){

	if ( isset( $_ENV['PANTHEON_ENVIRONMENT'] ) && ( 'lando' == $_ENV['PANTHEON_ENVIRONMENT'] ) ):

		$live_env_url = 'https://live-'.PANTHEON_SITE_NAME.'.pantheonsite.io'; 
		$dev_env_url = 'https://dev-'.PANTHEON_SITE_NAME.'.pantheonsite.io'; 

		if ( lightship_check_url_200($live_env_url) ){
			return $live_env_url;
		}

		return $dev_env_url;
	else:
		return null;
	endif; 

}
add_filter( 'be_media_from_production_url', 'lightship_be_media_from_production'); 

/**
 * Check if a particular URL returns 200 "OK"
 * @param string URL
 * @return bool 
 * @link https://developer.wordpress.org/reference/functions/wp_safe_remote_head/
 */
function lightship_check_url_200( $url ) {

	$response = wp_safe_remote_head( $url, [ 'timeout' => 5 ] );

    if ( ! is_wp_error( $response ) ) {
        $http_code = wp_remote_retrieve_response_code( $response );
        return $http_code === 200;
    }

    return false;
}