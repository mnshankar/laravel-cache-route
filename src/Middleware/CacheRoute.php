<?php namespace mnshankar\Cache\Middleware;

use Closure;

use Illuminate\Contracts\Support\Renderable;

class CacheRoute
{
    const DEFAULT_TTL=30;
    protected $request;
    protected $cacheKey;
    protected $ttl;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->okToCache($request)) {
            return $next($request);
        }
        $this->request = $request;
        $this->cacheKey = $this->makeCacheKey($request->fullUrl());
        $this->ttl = env('CACHE_TTL',self::DEFAULT_TTL);
        return $this->getResponse($next);
    }

    /**
     * Never cache non-GET requests
     * Do not cache if CACHE_ENABLED env variable is set to false
     * @param $request
     * @return bool
     */
    protected function okToCache($request)
    {
        if (!$request->isMethod('get')) {
            return false;
        }
        return env('CACHE_ENABLED',true);
    }

    /**
     * Return a string key that is based on the FULL request url
     * @return string
     */
    protected function makeCacheKey($url)
    {
        return 'route:' . str_slug($url);
    }


    protected function getResponse(Closure $next)
    {
        if (!\Cache::has($this->cacheKey)) {
            $response = $next($this->request);
            $pageContents = $this->getPagePayload($response);
            $this->storeInCache($this->cacheKey, $pageContents, $this->ttl);
            return $pageContents;
        }
        //in cache.. make a call and return
        return \Cache::get($this->cacheKey);
    }

    /**
     * In Laravel 5.0, middleware returns View or String
     * In Laravel 5.1+, middleware returns Response
     * Account for this
     * @param $response
     * @return string
     */
    protected function getPagePayload($response)
    {
        if ($response instanceof Renderable) {
            return $response->render();
        }
        return $response;
    }

    /**
     * Store page contents in the cache.
     * TTL (time-to-live is taken from env -defaults to 30 mins)
     * Exception is thrown if user tries to cache an uncacheable/non-serializable object (ex. redirect)
     * @param $cacheKey
     * @param $pageContents
     * @param $ttl
     * @throws \Exception
     */
    protected function storeInCache($cacheKey, $pageContents, $ttl)
    {
        try {
            \Cache::put($cacheKey, $pageContents, $ttl);
        } catch (\Exception $ex) {
            throw new \Exception('Sorry. Response could not be cached.');
        }
    }
}