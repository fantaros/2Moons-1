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
 * @info $Id: common.php 2803 2013-10-06 22:23:27Z slaver7 $
 * @link http://2moons.cc/
 */

if (function_exists('mb_internal_encoding')) {
	mb_internal_encoding("UTF-8");
}

ignore_user_abort(true);
error_reporting(E_ALL & ~E_STRICT);

// If date.timezone is invalid
date_default_timezone_set(@date_default_timezone_get());

ini_set('display_errors', 1);
ini_set('log_errors', 'On');
ini_set('error_log', ROOT_PATH.'includes/error.log');

define('TIMESTAMP',	time());

require 'includes/constants.php';
require 'includes/GeneralFunctions.php';
set_exception_handler('exceptionHandler');
set_error_handler('errorHandler');

// Say Browsers to Allow ThirdParty Cookies (Thanks to morktadela)
HTTP::sendHeader('P3P', 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
define('AJAX_REQUEST', HTTP::_GP('ajax', 0));

if (MODE === 'INSTALL')
{
	return;
}

if(!file_exists('includes/config.php'))
{
	HTTP::redirectTo('install/index.php');
}

$config = Config::get();
date_default_timezone_set($config->timezone);


if (MODE !== 'INGAME' && MODE !== 'ADMIN')
{
    return;
}

$session	= Session::get();
if(!$session->isValid())
{
    HTTP::redirectTo('index.php?code=3');
}

Vars::init();

$user = $session->getUser();

if($config->game_disable == 0 && $user->can(ACL_CAN_ENTER_CLOSED_GAME)) {
    ShowErrorPage::printError(sprintf('%s<br><br>%s', $user->translate('sys_closed_game'), $config->close_reason), false);
}

if($user->bana == 1) {
    ShowErrorPage::printError(
        sprintf('<font size="6px">%s</font><br><br>', $user->translate('css_account_banned_message')).
        sprintf($LNG['css_account_banned_expire'], _date($user->translate('php_tdformat'), $user->banaday, $user->timezone)).
        "<br><br>".$LNG['css_goto_homeside']
        , false);
}

unset($user, $config, $universeAmount);