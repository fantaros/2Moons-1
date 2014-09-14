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
 * @package 2Moons
 * @author Jan Kröpke <info@2moons.cc>
 * @copyright 2009 Lucky
 * @copyright 2011 Jan Kröpke <info@2moons.cc>
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 2.0.0 (2015-01-01)
 * @info $Id: TrackingCronjob.class.php 2747 2013-05-18 16:55:49Z slaver7 $
 * @link http://code.google.com/p/2moons/
 */

class TrackingCronjob implements CronjobTask
{
	function run()
	{
		$serverData['php']			= PHP_VERSION;
		
		try
		{
			$sql	= 'SELECT register_time FROM %%USERS%% WHERE id = :userId';
			$serverData['installSince']	= Database::get()->selectSingle($sql, array(
				':userId'	=> ROOT_USER
			), 'register_time');
		}
		catch (Exception $e)
		{
			$serverData['installSince']	= NULL;
		}
		
		try
		{
			$sql	= 'SELECT COUNT(*) as state FROM %%USERS%%;';
			$serverData['users']		= Database::get()->selectSingle($sql, array(), 'state');
		}
		catch (Exception $e)
		{
			$serverData['users']		= NULL;
		}
		
		try {
			$sql	= 'SELECT COUNT(*) as state FROM %%CONFIG%%;';
			$serverData['unis']			= Database::get()->selectSingle($sql, array(), 'state');
		} catch (Exception $e) {
			$serverData['unis']			= NULL;
		}

		$serverData['version']		= Config::get(ROOT_UNI)->VERSION;

        require_once 'includes/classes/HTTPRequest.php';
        $httpRequest = new HTTPRequest();
        $httpRequest->postData($serverData);
        $httpRequest->send();
	}
}