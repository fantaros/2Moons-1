<?php

/**
 *  2Moons
 *  Copyright (C) 2012 Jan Kröpke
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package 2Moons
 * @author Jan Kröpke <info@2moons.cc>
 * @copyright 2012 Jan Kröpke <info@2moons.cc>
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 2.0.0 (2015-01-01)
 * @info $Id: game.php 2776 2013-08-05 21:30:40Z slaver7 $
 * @link http://2moons.cc/
 */

define('MODE', 'ADMIN');
define('ROOT_PATH', str_replace('\\', '/',dirname(__FILE__)).'/');
set_include_path(ROOT_PATH);

require 'includes/common.php';
/** @var $LNG Language */

if(Session::get()->hasAdminAccess == 1)
{
	$page 	= HTTP::_GP('page', 'overview');
	$page	= str_replace(array('_', '\\', '/', '.', "\0"), '', $page);
    if(HTTP::_GP('s', '') !== session_id())
    {
        exit;
    }
}
else
{
	$page	= 'Login';
}

Session::get()
    ->getUser()
    ->getLangObj()
    ->includeData(array('ADMIN'));

$pageClass	= 'Show'.ucfirst($page).'Page';
$mode 		= HTTP::_GP('mode', 'show');

if(!file_exists($path)) {
	ShowErrorPage::printError($LNG['page_doesnt_exist']);
}

$pageObj	= new $pageClass;

if(!is_callable(array($pageObj, $mode))) {
	if(!isset($pageProps['defaultController']) || !is_callable(array($pageObj, $pageProps['defaultController']))) {
		ShowErrorPage::printError($LNG['page_doesnt_exist']);
	}
	$mode	= $pageProps['defaultController'];
}

$pageObj->{$mode}();