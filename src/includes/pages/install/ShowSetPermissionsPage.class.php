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
 * @version 2.0.0 (2013-03-18)
 * @info $Id: ShowAlliancePage.class.php 2776 2013-08-05 21:30:40Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowSetPermissionPage extends AbstractInstallPage
{
    public function ftp()
    {
        global $LNG;

        $LNG->includeData(array('ADMIN'));

        require 'includes/libs/ftp/ftp.class.php';
        require 'includes/libs/ftp/ftpexception.class.php';
        require 'includes/pages/install/ShowRequirementsPage.class.php';

        $connectionConfig = array(
            'host'     => $_GET['host'],
            'username' => $_GET['user'],
            'password' => $_GET['pass'],
            'port'     => 21
        );

        $ftp = FTP::getInstance();

        try
        {
            $ftp->connect($connectionConfig);
        }
        catch (FTPException $error)
        {
            $this->printMessage($LNG['req_ftp_error_data'], array(array(
                'label'	=> $LNG['back'],
                'url'	=> 'install/index.php?page=requirements',
            )));
        }

        if (!$ftp->changeDir($_GET['path']))
        {
            $this->printMessage($LNG['req_ftp_error_dir'], array(array(
                'label'	=> $LNG['back'],
                'url'	=> 'install/index.php?page=requirements',
            )));
        }

        foreach(ShowRequirementsPage::$requiredDirectories as $directory)
        {
            $ftp->makeDir($directory, 1);
            $ftp->chmod($directory, 0777);
        }

        foreach(ShowRequirementsPage::$requiredFiles as $file)
        {
            $ftp->chmod($file, 0777);
        }

        $this->printMessage($LNG['req_ftp_success'], array(array(
            'label'	=> $LNG['continue'],
            'url'	=> 'install/index.php?page=requirements',
        )));
    }
}