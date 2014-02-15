<?php
/**
 * Created by PhpStorm.
 * User: Jan
 * Date: 02.02.14
 * Time: 18:16
 */

class GameLink
{
    static function get($file, $page, $mode, $query = '')
    {
        return "$file/$page/$mode?$query";

        $page   = empty($page) ? '' : 'page='.$page;

        return "$file.php?page=$page&mode=$mode&$query";
    }

    static function parse($url)
    {
        return $url;

        preg_match("!^(admin|game|index)\.php(\?page=([^&]+)((&amp;|&)mode=([^&]+)((&amp;|&).*)?)?)?!", $url, $match);


        if(!isset($match[1]))
        {
            return $url;
        }

        $file = $match[1];

        $page = isset($match[3]) ? $match[3] : '';
        $mode = isset($match[6]) ? $match[6] : '';
        $query = isset($match[7]) ? $match[7] : '';

        return self::get($file, $page, $mode, $query);
    }
} 