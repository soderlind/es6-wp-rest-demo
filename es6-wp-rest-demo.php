<?php
/**
 * ES6 WP REST Demo
 *
 * @package     Soderlind\Demo\REST
 * @author      Per Soderlind
 * @copyright   2019 Per Soderlind
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: ES6 WP REST Demo
 * Plugin URI: https://github.com/soderlind/es6-wp-rest-demo
 * GitHub Plugin URI: https://github.com/soderlind/es6-wp-rest-demo
 * Description: Use native JavaScript (ES6) when doing REST calls.
 * Version:     1.0.0
 * Author:      Per Soderlind
 * Author URI:  https://soderlind.no
 * Text Domain: es6-wp-rest-demo
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

declare( strict_types = 1 );
namespace Soderlind\Demo\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ES6_WP_REST_DEMO_VERSION = '1.0.0';
const REST_NAMESPACE           = 'es6-wp-rest-demo/v1'; // REST API namespace.
const REST_BASE                = 'increment'; // REST API route.
const REST_ENDPOINT            = REST_NAMESPACE . '/' . REST_BASE; // REST API endpoint.

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\wp_scripts' );
add_action( 'rest_api_init', __NAMESPACE__ . '\\es6_rest_api_init' );

/**
 * Prepare to serve a REST API request.
 *
 * @param \WP_REST_Server $wp_rest_server Server object.
 */
function es6_rest_api_init( \WP_REST_Server $wp_rest_server ) : void {
	register_rest_route(
		REST_NAMESPACE,
		'/' . REST_BASE,
		[
			'methods'             => \WP_REST_Server::CREATABLE, // CREATABLE = POST. READABLE = GET.
			'callback'            => __NAMESPACE__ . '\\es6_rest', // What to do when the request is received.
			'permission_callback' => __NAMESPACE__ . '\\es6_rest_permissions_check', // NOT Optional, if missing will trigger a deprecated notice.
			'args'                => [
				'sum' => [
					'validate_callback' => function( $param, $request, $key ) {
						return is_numeric( $param );
					},
				],
			],
		]
	);
}

/**
 * Do the REST API response.
 *
 * @param \WP_REST_Request $request The REST API request.
 *
 * @return array
 */
function es6_rest( \WP_REST_Request $request ) : array {

	$params = $request->get_params();
	$sum    = $params['sum'] ?? 0;
	if ( isset( $sum ) ) {
		$response['response'] = 'success';
		$sum                  = ++$sum;
		$response['data']     = $sum;
		update_option( 'es6demo_sum', $sum );
	} else {
		$response['response'] = 'failed';
		$response['data']     = 'something went wrong ...';
	}

	return $response;
}

/**
 * Do the permissions check.
 *
 * @param \WP_REST_Request $request The request.
 * @return bool|\WP_Error
 */
function es6_rest_permissions_check( \WP_REST_Request $request ) : bool {
	return true; // Allow all.
	// return current_user_can( 'edit_posts' ); // Give access to administrators.
	// return is_logged_in(); // Give access to logged in users.
}

/**
 * Add Scripts.
 *
 * @return void
 */
function wp_scripts() {
	$url = plugins_url( '', __FILE__ );

	// Load fetch polyfill, url via https://polyfill.io/v3/url-builder/.
	wp_enqueue_script( 'polyfill-fetch', 'https://polyfill.io/v3/polyfill.min.js?features=fetch', [], ES6_WP_REST_DEMO_VERSION, true );
	wp_enqueue_script( 'es6-wp-rest', $url . '/es6-wp-rest-demo.js', [ 'polyfill-fetch' ], ES6_WP_REST_DEMO_VERSION, true );
	$data = wp_json_encode(
		[
			'nonce'   => wp_create_nonce( 'wp_rest' ), // NOTE: Must be "wp_rest" for the REST API.
			'restURL' => rest_url() . REST_ENDPOINT,
		]
	);
	wp_add_inline_script( 'es6-wp-rest', "const pluginES6WPREST = ${data};" );
}


/**
 * Demo Form
 */
add_shortcode( 'es6demo', __NAMESPACE__ . '\es6demo_form' );

/**
 * Create Demo Form.
 *
 * @param array $args Shortcode arguments.
 * @return string
 */
function es6demo_form( $args ) {
	$o   = '';
	$sum = get_option( 'es6demo_sum', 0 );
	$o  .= '<div id="es6-demo">';
	$o  .= '<div id="es6-demo-output">' . $sum . '</div>';
	$o  .= '<form><input id="es6-demo-input" type="button" value="+" data-sum="' . $sum . '"></form>';
	$o  .= '</div>';

	return $o;
}

add_action(
	'wp_enqueue_scripts',
	function() {
		$url = plugins_url( '', __FILE__ );
		wp_enqueue_style( 'es6-wp-form', $url . '/es6-wp-rest-demo.css', [], ES6_WP_REST_DEMO_VERSION );
	}
);
