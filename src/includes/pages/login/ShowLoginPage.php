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
 * @version 2.0.0 (2015-01-01)
 * @info $Id: ShowLoginPage.php 2793 2013-09-29 12:33:56Z slaver7 $
 * @link http://2moons.cc/
 */


class ShowLoginPage extends AbstractIndexPage
{
	public static $requireModule = 0;

	function __construct() 
	{
        if (empty($_POST))
        {
            HTTP::redirectTo('index.php');
        }

		parent::__construct();
	}
	
	function show() 
	{
		$username = HTTP::_GP('username', '', UTF8_SUPPORT);
		$password = HTTP::_GP('password', '', true);

        $user = new User(NULL, array(
            'universe'  => Universe::current(),
            'username'  => $username,
        ), array('id', 'password'));

		if ($user !== false)
		{
			$hashedPassword = PlayerUtil::cryptPassword($password);
			if($user['password'] != $hashedPassword)
			{
				// Fallback pre 1.7
				if($user['password'] == md5($password))
                {
                    $user->password = $hashedPassword;
                    $user->save();
				}
                else
                {
					HTTP::redirectTo('index.php?code=1');
				}
			}

			$session	            = Session::create();
			$session->userId		= (int) $user['id'];
			$session->adminAccess	= 0;

			HTTP::redirectTo('game.php');	
		}
		else
		{
			HTTP::redirectTo('index.php?code=1');
		}
	}
}
