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
 * @info $Id: ShowAlliancePage.class.php 2776 2013-08-05 21:30:40Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowAccountPage extends AbstractInstallPage
{
    public function show()
    {
        $this->display('page.account.default');
    }

    public function create()
    {
        global $LNG;

        $username	= HTTP::_GP('username', '', UTF8_SUPPORT);
        $password	= HTTP::_GP('password', '', true);
        $mail		= HTTP::_GP('email', '');

        require 'includes/classes/BuildUtil.class.php';

        Vars::init();

        $hashPassword = PlayerUtil::cryptPassword($password);

        if (empty($username) || empty($password) || empty($mail))
        {
            $this->printMessage($LNG['step8_need_fields'], array(array(
                'label'	=> $LNG['back'],
                'url'	=> 'javascript:window.history.back()',
            )));
        }

        list($userId) = PlayerUtil::createPlayer(Universe::current(), $username, $hashPassword, $mail,
            $LNG->getLanguage(), 1, 1, 2, NULL, AUTH_ADM);

        $session	= Session::create();
        $session->userId		= $userId;
        $session->adminAccess	= 1;

        unlink(self::$enableInstallToolFile);

        $messageText = "<h2>{$LNG['step6_head']}</h2>
        <p>{$LNG['step6_desc']}</p>
        <h2>{$LNG['step6_info_head']}</h2>
        <p>{$LNG['step6_info_additional']}</p>";

        $this->printMessage($messageText, array(array(
            'label'	=> $LNG['login'],
            'url'	=> 'admin.php',
        )));
    }
}