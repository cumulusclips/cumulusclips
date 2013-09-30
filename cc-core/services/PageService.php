<?php

class PageService extends ServiceAbstract
{
    public $found;
    private $db;
    protected static $table = 'pages';
    protected static $id_name = 'page_id';
    protected static $reserved = array (
        'videos',
        'members',
        'myaccount',
        'contact',
        'system-error',
        'nofound',
        'register',
        'login',
        'logout',
        'activate',
        'comments',
        'opt-out',
        'search',
        'actions',
        'feed',
        'page',
        'video-sitemap',
        'language'
    );

    /**
     * Delete a record
     * @param integer $id ID of record to be deleted
     * @return void Record is deleted from database
     */
    static function Delete ($id) {
        $db = Database::GetInstance();
        Plugin::Trigger ('page.delete');
        $query = "DELETE FROM " . DB_PREFIX . self::$table . " WHERE " . self::$id_name . " = $id";
        $db->Query ($query);
    }
    
    /**
     * Check if slug is reserved by system
     * @param string $slug The slug to check if reserved
     * @return boolean Returns true if slug is reserved, false otherwise
     */
    static function IsReserved ($slug) {
        return in_array ($slug, self::$reserved);
    }

    /**
     * Check if slug is available
     * @param string $slug The slug to check if available
     * @return string Returns available version of requested slug
     */
    static function GetAvailableSlug ($slug) {
        $count = 1;
        $slug_check = $slug;
        while (self::IsReserved ($slug_check) || self::Exist (array ('slug' => $slug_check))) {
            $count++;
            $slug_check = "$slug-$count";
        }
        return $slug_check;
    }
}