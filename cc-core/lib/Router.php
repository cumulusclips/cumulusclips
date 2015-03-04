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
            'location' => 'cc-core/controllers/page.php'
        ));
        
        
        /** General Routes **/
        
        $routes['index'] = new Route(array(
            'path' => '/',
            'location' => 'cc-core/controllers/index.php'
        ));

        $routes['browseVideos'] = new Route(array(
            'path' => 'videos',
            'location' => 'cc-core/controllers/videos.php'
        ));

        $routes['browseVideosPaginated'] = new Route(array(
            'path' => 'videos/page/([0-9]+)',
            'location' => 'cc-core/controllers/videos.php',
            'mappings' => array('page')
        ));

        $routes['browseVideosCategories'] = new Route(array(
            'path' => 'videos/([a-z0-9\-]+)',
            'location' => 'cc-core/controllers/videos.php',
            'mappings' => array('category')
        ));

        $routes['browseVideosCategoriesPaginated'] = new Route(array(
            'path' => 'videos/([a-z0-9\-]+)/page/([0-9]+)',
            'location' => 'cc-core/controllers/videos.php',
            'mappings' => array('category', 'page')
        ));

        $routes['browseVideosSorted'] = new Route(array(
            'path' => 'videos/(most-recent|most-viewed|most-discussed|most-rated)',
            'location' => 'cc-core/controllers/videos.php',
            'mappings' => array('load')
        ));

        $routes['browseVideosSortedPaginated'] = new Route(array(
            'path' => 'videos/(most-recent|most-viewed|most-discussed|most-rated)/page/([0-9]+)',
            'location' => 'cc-core/controllers/videos.php',
            'mappings' => array('load', 'page')
        ));
        
        $routes['play'] = new Route(array(
            'path' => 'videos/([0-9]+)/[a-z0-9\-]+',
            'location' => 'cc-core/controllers/play.php',
            'mappings' => array('vid')
        ));  

        $routes['browseMembers'] = new Route(array(
            'path' => 'members',
            'location' => 'cc-core/controllers/members.php'
        ));

        $routes['browseMembersPaginated'] = new Route(array(
            'path' => 'members/page/([0-9]+)',
            'location' => 'cc-core/controllers/members.php',
            'mappings' => array('page')
        ));
        
        $routes['profile'] = new Route(array(
            'path' => 'members/([a-z0-9]+)',
            'location' => 'cc-core/controllers/profile.php',
            'mappings' => array('username')
        ));

        $routes['optOut'] = new Route(array(
            'path' => 'opt-out',
            'location' => 'cc-core/controllers/opt_out.php'
        ));

        $routes['register'] = new Route(array(
            'path' => 'register',
            'location' => 'cc-core/controllers/register.php'
        ));

        $routes['activate'] = new Route(array(
            'path' => 'activate',
            'location' => 'cc-core/controllers/activate.php'
        ));

        $routes['login'] = new Route(array(
            'path' => 'login',
            'location' => 'cc-core/controllers/login.php'
        ));

        $routes['forgotPassword'] = new Route(array(
            'path' => 'login/(forgot)',
            'location' => 'cc-core/controllers/login.php',
            'mappings' => array('action')
        ));

        $routes['logout'] = new Route(array(
            'path' => 'logout',
            'location' => 'cc-core/controllers/logout.php'
        ));

        $routes['search'] = new Route(array(
            'path' => 'search',
            'location' => 'cc-core/controllers/search.php'
        ));

        $routes['searchPaginated'] = new Route(array(
            'path' => 'search/page/([0-9]+)',
            'location' => 'cc-core/controllers/search.php',
            'mappings' => array('page')
        ));

        $routes['contact'] = new Route(array(
            'path' => 'contact',
            'location' => 'cc-core/controllers/contact.php'
        ));
        
        
        /** Private Videos Routes **/

        $routes['getPrivateCode'] = new Route(array(
            'path' => 'private/get',
            'location' => 'cc-core/controllers/play.php',
            'mappings' => array('get_private' => 'true'),
            'type' => Route::AGNOSTIC
        ));

        $routes['privateVideoPlay'] = new Route(array(
            'path' => 'private/videos/([a-z0-9]+)',
            'location' => 'cc-core/controllers/play.php',
            'mappings' => array('private')
        ));

        
        /** Account Routes **/
        
        $routes['account'] = new Route(array(
            'path' => 'account',
            'location' => 'cc-core/controllers/account/account.php'
        ));

        $routes['accountUpload'] = new Route(array(
            'path' => 'account/upload',
            'location' => 'cc-core/controllers/account/upload.php'
        ));

        $routes['accountUploadVideo'] = new Route(array(
            'path' => 'account/upload/video',
            'location' => 'cc-core/controllers/account/upload_video.php'
        ));

        $routes['accountUploadComplete'] = new Route(array(
            'path' => 'account/upload/complete',
            'location' => 'cc-core/controllers/account/upload_complete.php'
        ));

        $routes['accountVideos'] = new Route(array(
            'path' => 'account/videos',
            'location' => 'cc-core/controllers/account/videos.php'
        ));

        $routes['accountVideosDelete'] = new Route(array(
            'path' => 'account/videos/([0-9]+)',
            'location' => 'cc-core/controllers/account/videos.php',
            'mappings' => array('vid')
        ));

        $routes['accountVideosPaginated'] = new Route(array(
            'path' => 'account/videos/page/([0-9]+)',
            'location' => 'cc-core/controllers/account/videos.php',
            'mappings' => array('page')
        ));

        $routes['accountVideosEdit'] = new Route(array(
            'path' => 'account/videos/edit/([0-9]+)',
            'location' => 'cc-core/controllers/account/videos_edit.php',
            'mappings' => array('vid')
        ));

        $routes['accountPlaylists'] = new Route(array(
            'path' => 'account/playlists',
            'location' => 'cc-core/controllers/account/playlists.php'
        ));

        $routes['accountPlaylistsEdit'] = new Route(array(
            'path' => 'account/playlists/edit/([0-9]+)',
            'location' => 'cc-core/controllers/account/playlists_edit.php',
            'mappings' => array('playlist_id')
        ));

        $routes['accountUpdateProfile'] = new Route(array(
            'path' => 'account/profile',
            'location' => 'cc-core/controllers/account/update_profile.php'
        ));

        $routes['accountResetAvatar'] = new Route(array(
            'path' => 'account/profile/(reset)',
            'location' => 'cc-core/controllers/account/update_profile.php',
            'mappings' => array('action')
        ));

        $routes['accountPrivacySettings'] = new Route(array(
            'path' => 'account/privacy-settings',
            'location' => 'cc-core/controllers/account/privacy_settings.php'
        ));

        $routes['accountChangePassword'] = new Route(array(
            'path' => 'account/change-password',
            'location' => 'cc-core/controllers/account/change_password.php'
        ));

        $routes['accountSubscriptions'] = new Route(array(
            'path' => 'account/subscriptions',
            'location' => 'cc-core/controllers/account/subscriptions.php'
        ));

        $routes['accountSubscriptionsDelete'] = new Route(array(
            'path' => 'account/subscriptions/([0-9]+)',
            'location' => 'cc-core/controllers/account/subscriptions.php',
            'mappings' => array('id')
        ));

        $routes['accountSubscriptionsPaginated'] = new Route(array(
            'path' => 'account/subscriptions/page/([0-9]+)',
            'location' => 'cc-core/controllers/account/subscriptions.php',
            'mappings' => array('page')
        ));

        $routes['accountSubscribers'] = new Route(array(
            'path' => 'account/subscribers',
            'location' => 'cc-core/controllers/account/subscribers.php'
        ));

        $routes['accountSubscribersPaginated'] = new Route(array(
            'path' => 'account/subscribers/page/([0-9]+)',
            'location' => 'cc-core/controllers/account/subscribers.php',
            'mappings' => array('page')
        ));

        $routes['accountInbox'] = new Route(array(
            'path' => 'account/message/inbox',
            'location' => 'cc-core/controllers/account/message_inbox.php'
        ));

        $routes['accountInboxDelete'] = new Route(array(
            'path' => 'account/message/inbox/([0-9]+)',
            'location' => 'cc-core/controllers/account/message_inbox.php',
            'mappings' => array('delete')
        ));

        $routes['accountInboxPaginated'] = new Route(array(
            'path' => 'account/message/inbox/page/([0-9]+)',
            'location' => 'cc-core/controllers/account/message_inbox.php',
            'mappings' => array('page')
        ));

        $routes['accountReadMessage'] = new Route(array(
            'path' => 'account/message/read/([0-9]+)',
            'location' => 'cc-core/controllers/account/message_read.php',
            'mappings' => array('msg')
        ));

        $routes['accountSendMessage'] = new Route(array(
            'path' => 'account/message/send',
            'location' => 'cc-core/controllers/account/message_send.php'
        ));

        $routes['accountSendMessageUsername'] = new Route(array(
            'path' => 'account/message/send/([a-z0-9]+)',
            'location' => 'cc-core/controllers/account/message_send.php',
            'mappings' => array('username')
        ));

        $routes['accountSendMessageReply'] = new Route(array(
            'path' => 'account/message/reply/([0-9]+)',
            'location' => 'cc-core/controllers/account/message_send.php',
            'mappings' => array('msg')
        ));
        
        
        /** Mobile Routes **/
        
        $routes['mobile'] = new Route(array(
            'path' => 'm',
            'location' => 'cc-core/controllers/mobile/index.php',
            'mobile' => true
        ));
        
        $routes['mobileBrowseVideos'] = new Route(array(
            'path' => 'm/v',
            'location' => 'cc-core/controllers/mobile/videos.php',
            'mobile' => true
        ));
        
        $routes['mobilePlay'] = new Route(array(
            'path' => 'm/v/([0-9]+)',
            'location' => 'cc-core/controllers/mobile/play.php',
            'mappings' => array('vid'),
            'mobile' => true
        ));
        
        $routes['mobileSearch'] = new Route(array(
            'path' => 'm/s',
            'location' => 'cc-core/controllers/mobile/search.php',
            'mobile' => true
        ));
        
        $routes['mobileLanguages'] = new Route(array(
            'path' => 'm/l',
            'location' => 'cc-core/controllers/mobile/languages.php',
            'mobile' => true
        ));
        
        $routes['mobileWatchLater'] = new Route(array(
            'path' => 'm/a/wl',
            'location' => 'cc-core/controllers/mobile/account/watch_later.php',
            'mobile' => true
        ));
        
        $routes['mobileFavorites'] = new Route(array(
            'path' => 'm/a/f',
            'location' => 'cc-core/controllers/mobile/account/favorites.php',
            'mobile' => true
        ));
        
        $routes['mobileMyVideos'] = new Route(array(
            'path' => 'm/a/v',
            'location' => 'cc-core/controllers/mobile/account/videos.php',
            'mobile' => true
        ));
        
        $routes['mobileUpload'] = new Route(array(
            'path' => 'm/a/u',
            'location' => 'cc-core/controllers/mobile/account/upload.php',
            'mobile' => true
        ));
        
        
        /** System Routes **/
        
        $routes['system404'] = new Route(array(
            'path' => 'not-found',
            'location' => 'cc-core/controllers/system_404.php'
        ));
        
        $routes['systemError'] = new Route(array(
            'path' => 'system-error',
            'location' => 'cc-core/controllers/system_error.php'
        ));
                
        $routes['embed'] = new Route(array(
            'path' => 'embed/([0-9]+)',
            'location' => 'cc-core/controllers/embed.php',
            'mappings' => array('vid')
        ));

        $routes['language'] = new Route(array(
            'path' => 'language/([g|s]et)',
            'location' => 'cc-core/system/language.php',
            'mappings' => array('action'),
            'type' => Route::AGNOSTIC
        ));

        $routes['videoSitemap'] = new Route(array(
            'path' => 'video-sitemap\.xml',
            'location' => 'cc-core/system/video_sitemap.php'
        ));

        $routes['videoSitemapPaginated'] = new Route(array(
            'path' => 'video-sitemap-([0-9]+)\.xml',
            'location' => 'cc-core/system/video_sitemap.php',
            'mappings' => array('page')
        ));
        
        
        /** AJAX Routes **/

        $routes['ajaxLogin'] = new Route(array(
            'path' => 'actions/login',
            'location' => 'cc-core/controllers/ajax/login.php',
            'type' => Route::AGNOSTIC
        ));

        $routes['ajaxMemberVideos'] = new Route(array(
            'path' => 'members/videos',
            'location' => 'cc-core/controllers/ajax/member.videos.ajax.php'
        ));

        $routes['ajaxMemberPlaylists'] = new Route(array(
            'path' => 'members/playlists',
            'location' => 'cc-core/controllers/ajax/member.playlists.ajax.php'
        ));
        
        $routes['ajaxSearch'] = new Route(array(
            'path' => 'search/load-more',
            'location' => 'cc-core/controllers/ajax/search.php',
            'type' => Route::AGNOSTIC
        ));

        $routes['ajaxSearchSuggest'] = new Route(array(
            'path' => 'search/suggest',
            'location' => 'cc-core/controllers/ajax/search.suggest.ajax.php',
            'type' => Route::AGNOSTIC
        ));
        
        $routes['ajaxVideos'] = new Route(array(
            'path' => 'videos/load-more',
            'location' => 'cc-core/controllers/ajax/videos.php',
            'type' => Route::AGNOSTIC
        ));

        $routes['ajaxAvatarUpload'] = new Route(array(
            'path' => 'account/upload/avatar',
            'location' => 'cc-core/controllers/ajax/avatar.ajax.php'
        ));

        $routes['ajaxVideoUpload'] = new Route(array(
            'path' => 'account/upload/validate',
            'location' => 'cc-core/controllers/ajax/upload.ajax.php'
        ));

        $routes['ajaxVideoFileUpload'] = new Route(array(
            'path' => 'ajax/upload',
            'location' => 'cc-core/controllers/ajax/upload.php',
            'type' => Route::AGNOSTIC
        ));

        $routes['ajaxUsernameExists'] = new Route(array(
            'path' => 'actions/username',
            'location' => 'cc-core/controllers/ajax/username.ajax.php'
        ));

        $routes['ajaxFlag'] = new Route(array(
            'path' => 'actions/flag',
            'location' => 'cc-core/controllers/ajax/flag.ajax.php'
        ));

        $routes['ajaxPlaylist'] = new Route(array(
            'path' => 'actions/playlist',
            'location' => 'cc-core/controllers/ajax/playlist.ajax.php'
        ));

        $routes['ajaxSubscribe'] = new Route(array(
            'path' => 'actions/subscribe',
            'location' => 'cc-core/controllers/ajax/subscribe.ajax.php'
        ));

        $routes['ajaxRate'] = new Route(array(
            'path' => 'actions/rate',
            'location' => 'cc-core/controllers/ajax/rate.ajax.php'
        ));

        $routes['ajaxCommentAdd'] = new Route(array(
            'path' => 'actions/comment/add',
            'location' => 'cc-core/controllers/ajax/comment.add.ajax.php',
            'type' => Route::AGNOSTIC
        ));

        $routes['ajaxCommentGet'] = new Route(array(
            'path' => 'actions/comments/get',
            'location' => 'cc-core/controllers/ajax/comment.get.ajax.php'
        ));

        $routes['ajaxPost'] = new Route(array(
            'path' => 'actions/post',
            'location' => 'cc-core/controllers/ajax/post.ajax.php'
        ));


        /** API Routes **/

        $routes['apiGetVideo'] = new Route(array(
            'path' => 'api/video/([0-9]+)',
            'location' => 'cc-core/controllers/api/video.get.php',
            'mappings' => array('videoId')
        ));

        $routes['apiGetVideoList'] = new Route(array(
            'path' => 'api/video/list',
            'location' => 'cc-core/controllers/api/video_list.get.php'
        ));

        return $routes;
    }
}