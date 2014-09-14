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
 * @info $Id: cronjob.php 2787 2013-08-13 20:30:56Z slaver7 $
 * @link http://2moons.cc/
 */

define('MODE', 'CRON');
define('ROOT_PATH', str_replace('\\', '/',dirname(__FILE__)).'/');
set_include_path(ROOT_PATH);
@set_time_limit(300);

$cronJobID	= HTTP::_GP('cronjobID', 0);

if(empty($cronJobID))
{
    exit;
}

require 'includes/common.php';

// Output transparent gif
clearGIF();

if(!Session::get()->isValid(false))
{
	exit;
}

if(!in_array($cronJobID, Cron::getNeedTodoExecutedJobs()))
{
	exit;
}

Cron::execute($cronJobID);