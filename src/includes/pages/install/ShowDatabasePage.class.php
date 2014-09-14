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

class ShowDatabasePage extends AbstractInstallPage
{
    public function show()
    {
        $this->display('page.database.default');
    }

    public function test()
    {
        global $LNG;

        $hostname   = HTTP::_GP('hostname', '');
        $port       = HTTP::_GP('port', '3306');
        $user       = HTTP::_GP('user', '', true);
        $password   = HTTP::_GP('password', '', true);
        $database   = HTTP::_GP('database', '', true);
        $prefix     = HTTP::_GP('prefix', 'uni1_');

        if (empty($database))
        {
            $this->_showMessage($LNG['step2_db_no_dbname'], 'fatalerror');
        }

        if (strlen($prefix) > 10)
        {
            $this->_showMessage($LNG['step2_db_too_long'], 'fatalerror');
        }

        if (strspn($prefix, '-./\\') !== 0 || strspn($prefix, '-./\\') !== 0)
        {
            $this->_showMessage($LNG['step2_prefix_invalid'], 'fatalerror');
        }

        if (is_file('includes/config.php') && filesize('includes/config.php') != 0)
        {
            $this->_showMessage($LNG['step2_config_exists'], 'fatalerror');
        }

        if (!is_writable('includes/config.php') && !touch('includes/config.php'))
        {
            $this->_showMessage($LNG['step2_conf_op_fail'], 'fatalerror');
        }

        $blowfish   = substr(str_shuffle('./0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 22);

        if($hostname === 'localhost' && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
        {
            //Speedup lcoal mysql on Windows.
            $hostname = '127.0.0.1';
        }

        $configFile = sprintf(
            file_get_contents('includes/config.sample.php'),
            $hostname,
            $port,
            $user,
            $password,
            $database,
            $prefix,
            $blowfish
        );

        file_put_contents('includes/config.php', $configFile);

        try {
            Database::get();
        }
        catch (Exception $e)
        {
            unlink('includes/config.php');
            $this->_showMessage($LNG['step2_db_con_fail'] . '</p><p>' . $e->getMessage(), 'fatalerror');
        }

        $this->_showMessage($LNG['step2_db_done']);
    }

    private function _showMessage($message, $class = 'noerror')
    {
        $this->assign(array(
            'class'   => $class,
            'message' => $message,
        ));

        $this->display('page.database.test');
    }
}