<?php

/**
 *  2Moons
 *  Copyright (C) 2012 Jan
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
 * @author Jan <info@2moons.cc>
 * @copyright 2006 Perberos <ugamela@perberos.com.ar> (UGamela)
 * @copyright 2008 Chlorel (XNova)
 * @copyright 2012 Jan <info@2moons.cc> (2Moons)
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 2.0.0 (2012-11-31)
 * @info $Id: ShowBoardPage.class.php 2771 2013-08-01 21:04:28Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowBoardPage extends AbstractIndexPage
{
	function show()
	{
		$boardUrl	= Config::get()->forum_url;
		if(filter_var($boardUrl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED))
		{
			HTTP::sendHeader('Location', $boardUrl);
		}
		else
		{
			$this->printMessage($this->lang->bad_forum_url);
		}
	}
}