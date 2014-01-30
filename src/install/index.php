<?php

/**
 *  2Moons
 *  Copyright (C) 2011 Jan Kröpke
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
 * @package   2Moons
 * @author    Jan Kröpke <info@2moons.cc>
 * @copyright 2009 Lucky
 * @copyright 2011 Jan Kröpke <info@2moons.cc>
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version   1.5 (2011-07-31)
 * @info $Id: index.php 2803 2013-10-06 22:23:27Z slaver7 $
 * @link      http://2moons.cc/
 */

define('MODE', 'INSTALL');
define('ROOT_PATH', str_replace('\\', '/', dirname(dirname(__FILE__))) . '/');

set_include_path(ROOT_PATH);
chdir(ROOT_PATH);

require 'includes/pages/install/AbstractInstallPage.class.php';
require 'includes/pages/install/ShowErrorPage.class.php';
require 'includes/common.php';

$LNG = new Language;
$LNG->getUserAgentLanguage();
$LNG->includeData(array('L18N', 'INGAME', 'INSTALL', 'CUSTOM'));

$page 		= HTTP::_GP('page', 'index');
$mode 		= HTTP::_GP('mode', 'show');
$page		= str_replace(array('_', '\\', '/', '.', "\0"), '', $page);
$pageClass	= 'Show'.ucfirst($page).'Page';

$path		= 'includes/pages/login/'.$pageClass.'.class.php';

if(!file_exists($path)) {
    ShowErrorPage::printError($LNG['page_doesnt_exist']);
}

// Added Autoload in feature Versions
require($path);

$pageObj	= new $pageClass;
// PHP 5.2 FIX
// can't use $pageObj::$requireModule
$pageProps	= get_class_vars(get_class($pageObj));

if(!is_callable(array($pageObj, $mode)))
{
    if(!isset($pageProps['defaultController']) || !is_callable(array($pageObj, $pageProps['defaultController'])))
    {
        ShowErrorPage::printError($LNG['page_doesnt_exist']);
    }

    $mode	= $pageProps['defaultController'];
}

$pageObj->{$mode}();