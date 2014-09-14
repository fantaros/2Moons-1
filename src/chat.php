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
 * @info $Id: index.php 2776 2013-08-05 21:30:40Z slaver7 $
 * @link http://2moons.cc/
 */

define('MODE', 'CHAT');
define('ROOT_PATH', str_replace('\\', '/',dirname(__FILE__)).'/');
set_include_path(ROOT_PATH);

require 'includes/common.php';

if(!Session::get()->isValid(false))
{
    HTTP::redirectTo('index.php?code=3');
}

if(!isModulAvalible(MODULE_CHAT))
{
    /** @var $LNG array */
    ShowErrorPage::printError($LNG['sys_module_inactive']);
}

// Include Class libraries:
require AJAX_CHAT_PATH.'lib/classes.php';

// Initialize the chat:
$ajaxChat = new CustomAJAXChat();