<?php
/**
 * Function Helpers
 * @author Blake Nahin <blake@zseartcc.org>
 */

if (!function_exists('log_action')) {
    function log_action($message)
    {
        return App\ActivityLog::new($message);
    }
}
if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        return \App\Helpers\AuthHelper::isAdmin();
    }
}
if (!function_exists('isUser')) {
    function isUser()
    {
        return \App\Helpers\AuthHelper::isUser();
    }
}
if (!function_exists('isPD')) {
    function isPD()
    {
        return \App\Helpers\ClubHelper::isPD();
    }
}
if (!function_exists('getClubId')) {
    function getClubId()
    {
        return \App\Helpers\AuthHelper::getClubId();
    }
}