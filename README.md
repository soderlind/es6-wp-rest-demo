# WordPress REST using native JavaScript

> If you prefer AJAX, take a look at [WordPress Ajax using native JavaScript](https://github.com/soderlind/es6-wp-ajax-demo)

## Prerequisite

Familiarize yourself with the [key technical concepts](https://developer.wordpress.org/rest-api/key-concepts/) behind how the REST API functions.

## Look at the code

I recommend that you take a look at the [JavaScript](https://github.com/soderlind/es6-wp-rest-demo/blob/master/es6-wp-rest-demo.js) and [PHP](https://github.com/soderlind/es6-wp-rest-demo/blob/master/es6-wp-rest-demo.php) code, it's not hard to understand what's happening.

### JavaScript (ES6)

First I create the `data` object using [JSON.stringify](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON/stringify).

```javascript
const data = JSON.stringify({
  sum: self.dataset.sum,
});
```

Then I use [aync/await](https://javascript.info/async-await) with [fetch](https://javascript.info/fetch) to do the REST call.

> I set the nonce in the header using [Headers](https://developer.mozilla.org/en-US/docs/Web/API/Headers). For WordPress, the nonce is set in the `X-WP-Nonce` header.

```javascript
const response = await fetch(url, {
  headers: new Headers({
    "X-WP-Nonce": pluginES6WPREST.nonce,
    "content-type": "application/json",
  }),
  method: "POST",
  credentials: "same-origin",
  body: data,
});

const res = await response.json();
if (res.response === "success") {
  self.dataset.sum = res.data;
  output.innerHTML = res.data;
  console.log(res);
} else {
  console.error(res);
}
```

### PHP

In PHP register the [WP REST API](https://developer.wordpress.org/rest-api/extending-the-rest-api/) endpoint.

```php
register_rest_route(
	REST_NAMESPACE,
	'/' . REST_BASE,
	[
		'methods'             => \WP_REST_Server::CREATABLE, // CREATABLE = POST. READABLE = GET.
		'callback'            => __NAMESPACE__ . '\\es6_rest',
		'permission_callback' => __NAMESPACE__ . '\\es6_rest_permissions_check',
		// 'permission_callback  => 'is_user_logged_in', // Only logged in users can access this endpoint.
		// 'permission_callback' => '__return_true', // Everyone can access this endpoint.
		'args'                => [
			'sum' => [
				'validate_callback' => function( $param, $request, $key ) {
					return is_numeric( $param );
				},
			],
		],
	]
);
```

> Note: `permission_callback` is optional, if missing will trigger a deprecated notice. The workaround is to return true

```php
function es6_rest_permissions_check( \WP_REST_Request $request ) : bool {
	return true; // Allow all.
	// return current_user_can( 'manage_options' ); // Give access to administrators.
	// return is_logged_in(); // Give access to logged in users.
}
```

The REST callback is similar to the [WP Ajax callback](https://github.com/soderlind/es6-wp-ajax-demo/blob/master/es6-wp-ajax-demo.php#L40-L59).

```php
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
```

Setting the nonce and rest_url is done using the `wp_add_inline_script` function.

```php
$data = wp_json_encode(
	[
		'nonce'   => wp_create_nonce( 'wp_rest' ), // NOTE: Must be "wp_rest" for the REST API.
		'restURL' => rest_url() . REST_ENDPOINT,
	]
)
wp_add_inline_script( 'es6-wp-rest', "const pluginES6WPREST = ${data};" );
```

## Demo

Not very exciting, the demo increments a number when you click on a button.

I you would like another example, take a look at https://github.com/soderlind/super-admin-all-sites-menu/blob/main/src/modules/rest.js

## Installation

- [Download the plugin](https://github.com/soderlind/es6-wp-rest-demo/archive/refs/heads/main.zip)
- Install and activate the plugin.
- Add the `[es6demo]` shortcode to a page.
- Click on the `+` button to increment the number.

## Copyright and License

es6-wp-rest-demo is copyright 2022+ Per Soderlind

es6-wp-rest-demo is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.

es6-wp-rest-demo is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU Lesser General Public License along with the Extension. If not, see http://www.gnu.org/licenses/.
