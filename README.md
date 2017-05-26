# Cache contents of entire route in Laravel
This is a Laravel 5.0+/PHP 5.4+ package that caches the response to an entire "get" request in the cache so subsequent requests to the same url are speeded up substantially. 

## Installation
Every effort has been made to insure that the package is extremely easy to install and use. After you "require" the package, you are ready to use it! There is no need for setting up service providers, facades, config files etc. 
``` bash
$ composer require mnshankar/laravel-cache-route
```
## Usage
Edit your http kernel.php file to include the package like so:
```bash
'cache.route'=>'mnshankar\Cache\Middleware\CacheRoute',
```
Now, use the middleware to cache the HTML output of an entire page either from the controller or from your route like so:

1. In your controller:
    ```php
    function __construct()
    {
        $this->middleware('cache.route');
    }
    ```
2. In your route:

   Using Laravel 5.0
   ```php
   Route::get('my/page', ['middleware' => 'cache-route', function()
   {
       //
   }]);
   ```
   Using Laravel 5.1+
   
   You can continue using Laravel 5.0 style.. or use chaining:
   ```php
   Route::get('/', function () {
       //
   })->middleware(['cache-route']);
   ```
   
   You may also use route groups. Please look up Laravel documentation on Middleware to learn more
   https://laravel.com/docs/5.2/middleware
## Configuration Options
Two configuration options (set via env parameters) are used by the package
1. Cache TTL (Time-To-Live):

   CACHE_TTL=30
    
   This parameter specifies a cache ttl value of 30 minutes
    
   Note that you can always use php artisan cache:flush to clear your application cache
2. Enable Cache (defaults to true):

   CACHE_ENABLE=false
    
   This parameter can be used to turn off caching
   
## Thoughts
Be VERY cautions when using a whole page cache such as this. Remember contents of the cache are visible to ALL your users. 
1. For, "mostly static" content, go for it!
2. For, "mostly dynamic" content or heavily user-customized content, AVOID this strategy. User specific information is gathered server side. So, you essentially WANT to hit the server.

__Good rule of thumb__: If two different users see different pages on hitting the same URL, DO NOT cache the output using this strategy. An alternative may be to cache database queries.