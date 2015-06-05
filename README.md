# PHP Router class

A simple Rails inspired PHP router class.

* Usage of different HTTP Methods
* REST / Resourceful routing
* Reversed routing using named routes
* Dynamic URL's: use URL segments as parameters.

# Easy to install with **composer**

```javascript
{
    "require": {
        "uniplug/php-api-router": "dev-master"
    }
}
```

## Usage

### Friendly URL

Create a simple .htaccess file on your root directory.

```apache
Options +FollowSymLinks
RewriteEngine On
RewriteRule ^(.*)$ index.php [NC,L]
```

It's a simple example of routers in action

```php
<?php
require __DIR__ . '/vendor/uniplug/autoload.php';

use APIRouter\Router;

class api {
	public static function index($id, $gg) {
		echo "This is application index page";
	}
}

class user {
	public static function index() {
		echo "This is user index page";
	}

	public static function show($id) {
		echo "User id is $id";
		return 3;
	}
}


$routes = [
	'get index'         => 'api#index',
	'get index/:id/:gg' => 'api#index',
	'resources'         => 'user',
];

$router = Router::load($routes);
$router->setBasePath('/api');
$router->matchCurrentRequest();

var_dump($route);
```

## License
MIT Licensed, http://www.opensource.org/licenses/MIT
