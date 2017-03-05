<?php

class Router
{
    /**
     * Perform a REGEX test of a route's path against a given URI
     * @param Route $route Route to test
     * @param string $uri URI to test route path against
     * @return array Returns array of preg_match matches when match is found
     * boolean false otherwise
     * @see http://php.net/manual/en/function.preg-match.php For $matches format
     */
    protected function _testPattern(Route $route, $uri)
    {
        $pattern = '#^' . trim($route->path,'/') . '/?$#i';
        if (preg_match($pattern, $uri, $matches) === 1) {
            return $matches;
        } else {
            return false;
        }
    }

    /**
     * Map REGEX pattern matches found in route's path to keys for $_GET superglobal
     * @param Route $route The matched route whose path matches are to be mapped
     * @param array $patternMatches REGEX pattern matches from given route against current URI
     * @return array Returns list of key/value pairs to be merged into $_GET superglobal
     * @see http://php.net/manual/en/function.preg-match.php For $patternMatches format
     */
    protected function _mapPatternMatches(Route $route, $patternMatches)
    {
        $mappedData = array();
        foreach($route->mappings as $key => $variableName) {
            if (!is_numeric($key)) {
                $mappedData[$key] = $variableName;
            } else if (isset($patternMatches[$key+1])) {
                $mappedData[$variableName] = $patternMatches[$key+1];
            } else {
                $mappedData[$variableName] = null;
            }
        }
        return $mappedData;
    }

    /**
     * Return the Apache request uri with the site base URL and query string removed
     * @return string Returns the intended request uri
     */
    public function getRequestUri()
    {
        // Strip query string from Apache request uri
        $apacheRequestUri = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);

        // Strip path part of base URL and query string from Apache request uri
        $urlParts = parse_url(HOST);
        if (!empty($urlParts['path'])) {
            return preg_replace('#^' . $urlParts['path'] . '#', '', $apacheRequestUri);
        } else {
            return $apacheRequestUri;
        }
    }

    /**
     * Detect which route matches current request URI, and merge it's pattern
     * matches into $_GET superglobal. URI is compared to ALL routes, and the
     * last matching route is used.
     * @return Route
     */
    public function getRoute()
    {
        $matchedRoute = new stdClass();
        $routes = $this->_staticRoutes();
        $requestUri = $this->getRequestUri();

        // Go through all routes to test if it matches current URI
        foreach ($routes as $route) {
            $patternMatches = $this->_testPattern($route, trim($requestUri, '/'));
            if ($patternMatches) {
                $matchedRoute->route = $route;
                $matchedRoute->matches = $patternMatches;
            }
        }

        // Merge user defined GET variables with original GET vars
        if (isset($matchedRoute->route->mappings)) {
            $additionalGetVars = $this->_mapPatternMatches($matchedRoute->route, $matchedRoute->matches);
            $_GET = array_merge($_GET, $additionalGetVars);
        }

        return $matchedRoute->route;
    }

    /**
     * Retrieve a static route
     * @param string $routeName The static route being requested
     * @return Route Returns the requested static route
     * @throws Exception If requested route does not exist
     */
    public function getStaticRoute($routeName)
    {
        $routes = $this->_staticRoutes();
        if (isset($routes[$routeName])) {
            return $routes[$routeName];
        } else {
            throw new Exception('Unknown Static Route');
        }
    }

    /**
     * Retrieve static built-in routes
     * @return array List of static routes
     */
    protected function _staticRoutes()
    {
        $routes = array();

        // Catch all route
        $routes['pages'] = new Route(array(
            'path' => '.*',
            'location' => DOC_ROOT . '/cc-core/controllers/page.php',
            'name' => 'pages'
        ));


        /** General Routes **/

        $routes['index'] = new Route(array(
            'path' => '/',
            'location' => DOC_ROOT . '/cc-core/controllers/index.php',
            'name' => 'index'
        ));

        /**
         * @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use browse instead
         */
        $routes['browse-videos'] = new Route(array(
            'path' => 'videos',
            'location' => DOC_ROOT . '/cc-core/controllers/videos.php',
            'name' => 'browse-videos'
        ));

        /**
         * @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use browse-paginated instead
         */
        $routes['browse-videos-paginated'] = new Route(array(
            'path' => 'videos/page/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/videos.php',
            'mappings' => array('page'),
            'name' => 'browse-videos-paginated',
            'canonical' => 'browse-videos'
        ));

        /**
         * @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use browse-categories instead
         */
        $routes['browse-videos-categories'] = new Route(array(
            'path' => 'videos/([a-z0-9\-]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/videos.php',
            'mappings' => array('category'),
            'name' => 'browse-videos-categories',
            'canonical' => 'browse-videos'
        ));

        /**
         * @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use browse-categories-paginated instead
         */
        $routes['browse-videos-categories-paginated'] = new Route(array(
            'path' => 'videos/([a-z0-9\-]+)/page/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/videos.php',
            'mappings' => array('category', 'page'),
            'name' => 'browse-videos-categories-paginated',
            'canonical' => 'browse-videos'
        ));

        /**
         * @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use browse-sorted instead
         */
        $routes['browse-videos-sorted'] = new Route(array(
            'path' => 'videos/(most-recent|most-viewed|most-discussed|most-rated)',
            'location' => DOC_ROOT . '/cc-core/controllers/videos.php',
            'mappings' => array('load'),
            'name' => 'browse-videos-sorted',
            'canonical' => 'browse-videos'
        ));

        /**
         * @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use browse-sorted-paginated instead
         */
        $routes['browse-videos-sorted-paginated'] = new Route(array(
            'path' => 'videos/(most-recent|most-viewed|most-discussed|most-rated)/page/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/videos.php',
            'mappings' => array('load', 'page'),
            'name' => 'browse-videos-sorted-paginated',
            'canonical' => 'browse-videos'
        ));

        $routes['browse'] = new Route(array(
            'path' => 'browse',
            'location' => DOC_ROOT . '/cc-core/controllers/browse.php',
            'name' => 'browse'
        ));

        $routes['browse-paginated'] = new Route(array(
            'path' => 'browse/page/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/browse.php',
            'mappings' => array('page'),
            'name' => 'browse-paginated',
            'canonical' => 'browse'
        ));

        $routes['browse-categories'] = new Route(array(
            'path' => 'browse/([a-z0-9\-]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/browse.php',
            'mappings' => array('category'),
            'name' => 'browse-categories',
            'canonical' => 'browse'
        ));

        $routes['browse-categories-paginated'] = new Route(array(
            'path' => 'browse/([a-z0-9\-]+)/page/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/browse.php',
            'mappings' => array('category', 'page'),
            'name' => 'browse-categories-paginated',
            'canonical' => 'browse'
        ));

        $routes['browse-sorted'] = new Route(array(
            'path' => 'browse/(most-recent|most-viewed|most-discussed|most-rated)',
            'location' => DOC_ROOT . '/cc-core/controllers/browse.php',
            'mappings' => array('load'),
            'name' => 'browse-sorted',
            'canonical' => 'browse'
        ));

        $routes['browse-sorted-paginated'] = new Route(array(
            'path' => 'browse/(most-recent|most-viewed|most-discussed|most-rated)/page/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/browse.php',
            'mappings' => array('load', 'page'),
            'name' => 'browse-sorted-paginated',
            'canonical' => 'browse'
        ));

        /**
         * @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use watch instead
         */
        $routes['play'] = new Route(array(
            'path' => 'videos/([0-9]+)(/[a-z0-9\-]+)?',
            'location' => DOC_ROOT . '/cc-core/controllers/play.php',
            'mappings' => array('vid'),
            'name' => 'play'
        ));

        $routes['watch'] = new Route(array(
            'path' => 'watch/([0-9]+)(/[a-z0-9\-]+)?',
            'location' => DOC_ROOT . '/cc-core/controllers/watch.php',
            'mappings' => array('video_id'),
            'name' => 'watch'
        ));

        $routes['browse-members'] = new Route(array(
            'path' => 'members',
            'location' => DOC_ROOT . '/cc-core/controllers/members.php',
            'name' => 'browse-members'
        ));

        $routes['browse-members-paginated'] = new Route(array(
            'path' => 'members/page/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/members.php',
            'mappings' => array('page'),
            'name' => 'browse-members-paginated',
            'canonical' => 'browse-members'
        ));

        $routes['profile'] = new Route(array(
            'path' => 'members/([a-z0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/profile.php',
            'mappings' => array('username'),
            'name' => 'profile'
        ));

        $routes['opt-out'] = new Route(array(
            'path' => 'opt-out',
            'location' => DOC_ROOT . '/cc-core/controllers/opt_out.php',
            'type' => Route::AGNOSTIC,
            'name' => 'opt-out'
        ));

        $routes['register'] = new Route(array(
            'path' => 'register',
            'location' => DOC_ROOT . '/cc-core/controllers/register.php',
            'type' => Route::AGNOSTIC,
            'name' => 'register'
        ));

        $routes['activate'] = new Route(array(
            'path' => 'activate',
            'location' => DOC_ROOT . '/cc-core/controllers/activate.php',
            'type' => Route::AGNOSTIC,
            'name' => 'activate'
        ));

        $routes['login'] = new Route(array(
            'path' => 'login',
            'location' => DOC_ROOT . '/cc-core/controllers/login.php',
            'name' => 'login'
        ));

        $routes['logout'] = new Route(array(
            'path' => 'logout',
            'location' => DOC_ROOT . '/cc-core/controllers/logout.php',
            'name' => 'logout'
        ));

        $routes['search'] = new Route(array(
            'path' => 'search',
            'location' => DOC_ROOT . '/cc-core/controllers/search.php',
            'name' => 'search'
        ));

        $routes['search-paginated'] = new Route(array(
            'path' => 'search/page/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/search.php',
            'mappings' => array('page'),
            'name' => 'search-paginated',
            'canonical' => 'search'
        ));

        $routes['contact'] = new Route(array(
            'path' => 'contact',
            'location' => DOC_ROOT . '/cc-core/controllers/contact.php',
            'name' => 'contact'
        ));


        /** Private Videos Routes **/

        $routes['get-private-code'] = new Route(array(
            'path' => 'private/get',
            'location' => DOC_ROOT . '/cc-core/controllers/watch.php',
            'mappings' => array('get_private' => 'true'),
            'type' => Route::AGNOSTIC,
            'name' => 'get-private-code'
        ));

        $routes['play-private'] = new Route(array(
            'path' => 'private/videos/([a-z0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/watch.php',
            'mappings' => array('private'),
            'name' => 'play-private',
            'canonical' => 'play'
        ));


        /** Account Routes **/

        $routes['account-index'] = new Route(array(
            'path' => 'account',
            'location' => DOC_ROOT . '/cc-core/controllers/account/account.php',
            'name' => 'account-index'
        ));

        /**
         * @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use account-upload-info instead
         */
        $routes['account-upload'] = new Route(array(
            'path' => 'account/upload',
            'location' => DOC_ROOT . '/cc-core/controllers/account/upload.php',
            'name' => 'account-upload'
        ));

        $routes['account-upload-video'] = new Route(array(
            'path' => 'account/upload/video',
            'location' => DOC_ROOT . '/cc-core/controllers/account/upload_video.php',
            'name' => 'account-upload-video'
        ));

        $routes['account-upload-info'] = new Route(array(
            'path' => 'account/upload/info',
            'location' => DOC_ROOT . '/cc-core/controllers/account/upload_info.php',
            'name' => 'account-upload-info'
        ));

        $routes['account-upload-complete'] = new Route(array(
            'path' => 'account/upload/complete',
            'location' => DOC_ROOT . '/cc-core/controllers/account/upload_complete.php',
            'name' => 'account-upload-complete'
        ));

        $routes['account-videos'] = new Route(array(
            'path' => 'account/videos',
            'location' => DOC_ROOT . '/cc-core/controllers/account/videos.php',
            'name' => 'account-videos'
        ));

        $routes['account-videos-delete'] = new Route(array(
            'path' => 'account/videos/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/videos.php',
            'mappings' => array('vid'),
            'name' => 'account-videos-delete',
            'canonical' => 'account-videos'
        ));

        $routes['account-videos-paginated'] = new Route(array(
            'path' => 'account/videos/page/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/videos.php',
            'mappings' => array('page'),
            'name' => 'account-videos-paginated',
            'canonical' => 'account-videos'
        ));

        $routes['account-videos-edit'] = new Route(array(
            'path' => 'account/videos/edit/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/videos_edit.php',
            'mappings' => array('vid'),
            'name' => 'account-videos-edit'
        ));

        $routes['account-attachments'] = new Route(array(
            'path' => 'account/attachments',
            'location' => DOC_ROOT . '/cc-core/controllers/account/attachments.php',
            'name' => 'account-attachments'
        ));

        $routes['account-playlists'] = new Route(array(
            'path' => 'account/playlists',
            'location' => DOC_ROOT . '/cc-core/controllers/account/playlists.php',
            'name' => 'account-playlists'
        ));

        $routes['account-playlists-edit'] = new Route(array(
            'path' => 'account/playlists/edit/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/playlists_edit.php',
            'mappings' => array('playlist_id'),
            'name' => 'account-playlists-edit'
        ));

        $routes['account-update-profile'] = new Route(array(
            'path' => 'account/profile',
            'location' => DOC_ROOT . '/cc-core/controllers/account/update_profile.php',
            'name' => 'account-update-profile'
        ));

        $routes['account-reset-avatar'] = new Route(array(
            'path' => 'account/profile/(reset)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/update_profile.php',
            'mappings' => array('action'),
            'name' => 'account-reset-avatar',
            'canonical' => 'account-update-profile'
        ));

        $routes['account-privacy-settings'] = new Route(array(
            'path' => 'account/privacy-settings',
            'location' => DOC_ROOT . '/cc-core/controllers/account/privacy_settings.php',
            'name' => 'account-privacy-settings'
        ));

        $routes['account-change-password'] = new Route(array(
            'path' => 'account/change-password',
            'location' => DOC_ROOT . '/cc-core/controllers/account/change_password.php',
            'name' => 'account-change-password'
        ));

        $routes['account-subscriptions'] = new Route(array(
            'path' => 'account/subscriptions',
            'location' => DOC_ROOT . '/cc-core/controllers/account/subscriptions.php',
            'name' => 'account-subscriptions'
        ));

        $routes['account-subscriptions-delete'] = new Route(array(
            'path' => 'account/subscriptions/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/subscriptions.php',
            'mappings' => array('id'),
            'name' => 'account-subscriptions-delete',
            'canonical' => 'account-subscriptions'
        ));

        $routes['account-subscriptions-paginated'] = new Route(array(
            'path' => 'account/subscriptions/page/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/subscriptions.php',
            'mappings' => array('page'),
            'name' => 'account-subscriptions-paginated',
            'canonical' => 'account-subscriptions'
        ));

        $routes['account-subscribers'] = new Route(array(
            'path' => 'account/subscribers',
            'location' => DOC_ROOT . '/cc-core/controllers/account/subscribers.php',
            'name' => 'account-subscribers'
        ));

        $routes['account-subscribers-paginated'] = new Route(array(
            'path' => 'account/subscribers/page/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/subscribers.php',
            'mappings' => array('page'),
            'name' => 'account-subscribers-paginated',
            'canonical' => 'account-subscribers'
        ));

        $routes['account-inbox'] = new Route(array(
            'path' => 'account/message/inbox',
            'location' => DOC_ROOT . '/cc-core/controllers/account/message_inbox.php',
            'name' => 'account-inbox'
        ));

        $routes['account-inbox-delete'] = new Route(array(
            'path' => 'account/message/inbox/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/message_inbox.php',
            'mappings' => array('delete'),
            'name' => 'account-inbox-delete',
            'canonical' => 'account-inbox'
        ));

        $routes['account-inbox-paginated'] = new Route(array(
            'path' => 'account/message/inbox/page/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/message_inbox.php',
            'mappings' => array('page'),
            'name' => 'account-inbox-paginated',
            'canonical' => 'account-inbox'
        ));

        $routes['account-message-read'] = new Route(array(
            'path' => 'account/message/read/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/message_read.php',
            'mappings' => array('msg'),
            'name' => 'account-message-read'
        ));

        $routes['account-message-send'] = new Route(array(
            'path' => 'account/message/send',
            'location' => DOC_ROOT . '/cc-core/controllers/account/message_send.php',
            'name' => 'account-message-send'
        ));

        $routes['account-message-send-username'] = new Route(array(
            'path' => 'account/message/send/([a-z0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/message_send.php',
            'mappings' => array('username'),
            'name' => 'account-message-send-username',
            'canonical' => 'account-message-send'
        ));

        $routes['account-message-reply'] = new Route(array(
            'path' => 'account/message/reply/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/account/message_send.php',
            'mappings' => array('msg'),
            'name' => 'account-message-reply',
            'canonical' => 'account-message-send'
        ));


        /** Mobile Routes **/

        $routes['mobile-index'] = new Route(array(
            'path' => 'm',
            'location' => DOC_ROOT . '/cc-core/controllers/mobile/index.php',
            'type' => Route::MOBILE,
            'name' => 'mobile-index'
        ));

        $routes['mobile-browse-videos'] = new Route(array(
            'path' => 'm/v',
            'location' => DOC_ROOT . '/cc-core/controllers/mobile/videos.php',
            'type' => Route::MOBILE,
            'name' => 'mobile-browse-videos'
        ));

        $routes['mobile-play'] = new Route(array(
            'path' => 'm/v/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/mobile/play.php',
            'mappings' => array('vid'),
            'type' => Route::MOBILE,
            'name' => 'mobile-play'
        ));

        $routes['mobile-play-private'] = new Route(array(
            'path' => 'm/p/([a-z0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/mobile/play.php',
            'mappings' => array('private'),
            'type' => Route::MOBILE,
            'name' => 'mobile-play-private',
            'canonical' => 'mobile-play'
        ));

        $routes['mobile-search'] = new Route(array(
            'path' => 'm/s',
            'location' => DOC_ROOT . '/cc-core/controllers/mobile/search.php',
            'type' => Route::MOBILE,
            'name' => 'mobile-search'
        ));

        $routes['mobile-languages'] = new Route(array(
            'path' => 'm/l',
            'location' => DOC_ROOT . '/cc-core/controllers/mobile/languages.php',
            'type' => Route::MOBILE,
            'name' => 'mobile-languages'
        ));

        $routes['mobile-watch-later'] = new Route(array(
            'path' => 'm/a/wl',
            'location' => DOC_ROOT . '/cc-core/controllers/mobile/account/watch_later.php',
            'type' => Route::MOBILE,
            'name' => 'mobile-watch-later'
        ));

        $routes['mobile-favorites'] = new Route(array(
            'path' => 'm/a/f',
            'location' => DOC_ROOT . '/cc-core/controllers/mobile/account/favorites.php',
            'type' => Route::MOBILE,
            'name' => 'mobile-favorites'
        ));

        $routes['mobile-my-videos'] = new Route(array(
            'path' => 'm/a/v',
            'location' => DOC_ROOT . '/cc-core/controllers/mobile/account/videos.php',
            'type' => Route::MOBILE,
            'name' => 'mobile-my-videos'
        ));

        $routes['mobile-upload'] = new Route(array(
            'path' => 'm/a/u',
            'location' => DOC_ROOT . '/cc-core/controllers/mobile/account/upload.php',
            'type' => Route::MOBILE,
            'name' => 'mobile-upload'
        ));


        /** System Routes **/

        $routes['system-404'] = new Route(array(
            'path' => 'not-found',
            'location' => DOC_ROOT . '/cc-core/controllers/system_404.php',
            'type' => Route::AGNOSTIC,
            'name' => 'system-404'
        ));

        $routes['system-500'] = new Route(array(
            'path' => 'system-error',
            'location' => DOC_ROOT . '/cc-core/controllers/system_error.php',
            'type' => Route::AGNOSTIC,
            'name' => 'system-500'
        ));

        $routes['embed'] = new Route(array(
            'path' => 'embed/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/embed.php',
            'mappings' => array('vid'),
            'type' => Route::AGNOSTIC,
            'name' => 'embed'
        ));

        $routes['language-get'] = new Route(array(
            'path' => 'language/(get)',
            'location' => DOC_ROOT . '/cc-core/system/language.php',
            'mappings' => array('action'),
            'type' => Route::AGNOSTIC,
            'name' => 'language-get'
        ));

        $routes['language-set'] = new Route(array(
            'path' => 'language/(set)/([a-z_]+)',
            'location' => DOC_ROOT . '/cc-core/system/language.php',
            'mappings' => array('action', 'language'),
            'type' => Route::AGNOSTIC,
            'name' => 'language-set'
        ));

        $routes['system-css'] = new Route(array(
            'path' => 'css/system\.css',
            'location' => DOC_ROOT . '/cc-core/system/css.php',
            'type' => Route::AGNOSTIC,
            'name' => 'system-css'
        ));

        $routes['system-js'] = new Route(array(
            'path' => 'js/system\.js',
            'location' => DOC_ROOT . '/cc-core/system/js.php',
            'type' => Route::AGNOSTIC,
            'name' => 'system-js'
        ));

        $routes['sitemap-video'] = new Route(array(
            'path' => 'video-sitemap\.xml',
            'location' => DOC_ROOT . '/cc-core/system/video_sitemap.php',
            'name' => 'sitemap-video'
        ));

        $routes['sitemap-video-paginated'] = new Route(array(
            'path' => 'video-sitemap-([0-9]+)\.xml',
            'location' => DOC_ROOT . '/cc-core/system/video_sitemap.php',
            'mappings' => array('page'),
            'name' => 'sitemap-video-paginated'
        ));


        /** AJAX Routes **/

        $routes['ajax-login'] = new Route(array(
            'path' => 'actions/login',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/login.php',
            'type' => Route::AGNOSTIC,
            'name' => 'ajax-login'
        ));

        $routes['ajax-member-videos'] = new Route(array(
            'path' => 'members/videos',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/member.videos.ajax.php',
            'name' => 'ajax-member-videos'
        ));

        $routes['ajax-member-playlists'] = new Route(array(
            'path' => 'members/playlists',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/member.playlists.ajax.php',
            'name' => 'ajax-member-playlists'
        ));

        $routes['ajax-search'] = new Route(array(
            'path' => 'search/load-more',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/search.php',
            'type' => Route::AGNOSTIC,
            'name' => 'ajax-search'
        ));

        $routes['ajax-search-suggest'] = new Route(array(
            'path' => 'search/suggest',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/search.suggest.ajax.php',
            'type' => Route::AGNOSTIC,
            'name' => 'ajax-search-suggest'
        ));

        $routes['ajax-videos-more'] = new Route(array(
            'path' => 'videos/load-more',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/videos.php',
            'type' => Route::AGNOSTIC,
            'name' => 'ajax-videos-more'
        ));

        $routes['ajax-upload'] = new Route(array(
            'path' => 'ajax/upload',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/upload.php',
            'type' => Route::AGNOSTIC,
            'name' => 'ajax-upload'
        ));

        $routes['ajax-username-exists'] = new Route(array(
            'path' => 'actions/username',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/username.ajax.php',
            'type' => Route::AGNOSTIC,
            'name' => 'ajax-username-exists'
        ));

        $routes['ajax-flag'] = new Route(array(
            'path' => 'actions/flag',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/flag.ajax.php',
            'name' => 'ajax-flag'
        ));

        $routes['ajax-playlist'] = new Route(array(
            'path' => 'actions/playlist',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/playlist.ajax.php',
            'name' => 'ajax-playlist'
        ));

        $routes['ajax-subscribe'] = new Route(array(
            'path' => 'actions/subscribe',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/subscribe.ajax.php',
            'name' => 'ajax-subscribe'
        ));

        $routes['ajax-rate'] = new Route(array(
            'path' => 'actions/rate',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/rate.ajax.php',
            'name' => 'ajax-rate'
        ));

        $routes['ajax-comment-add'] = new Route(array(
            'path' => 'actions/comment/add',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/comment.add.ajax.php',
            'type' => Route::AGNOSTIC,
            'name' => 'ajax-comment-add'
        ));

        $routes['ajax-comment-get'] = new Route(array(
            'path' => 'actions/comments/get',
            'location' => DOC_ROOT . '/cc-core/controllers/ajax/comment.get.ajax.php',
            'type' => Route::AGNOSTIC,
            'name' => 'ajax-comment-get'
        ));


        /** API Routes **/

        $routes['api-video'] = new Route(array(
            'path' => 'api/video/([0-9]+)',
            'location' => DOC_ROOT . '/cc-core/controllers/api/video.get.php',
            'mappings' => array('videoId'),
            'name' => 'api-video'
        ));

        $routes['api-video-list'] = new Route(array(
            'path' => 'api/video/list',
            'location' => DOC_ROOT . '/cc-core/controllers/api/video_list.get.php',
            'name' => 'api-video-list'
        ));

        return Plugin::triggerFilter('router.static_routes', $routes);
    }
}