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
 * @version 1.8.0 (2013-03-18)
 * @info $Id: ShowAlliancePage.class.php 2776 2013-08-05 21:30:40Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowInstallPage extends AbstractInstallPage
{
    public function show()
    {
        $this->display('page.install.default');
    }

    public function execute()
    {
        global $LNG;

        $db = Database::get();
        $installSQL      = file_get_contents('install/install.sql');
        $installVersion  = file_get_contents('install/VERSION');

        preg_match('!\$' . 'Id: install.sql ([0-9]+)!', $installSQL, $match);

        $installVersion = explode('.', $installVersion);

        if (isset($match[1]) && $installVersion[2] < $match[1])
        {
            $installRevision    = (int) $match[1];
            $installVersion[2]  = $installRevision;
        }
        else
        {
            $installRevision    = (int) $installVersion[2];
        }

        $installVersion = implode('.', $installVersion);

        try {
            $db->query(str_replace(array(
                'prefix_',
            ), array(
                DB_PREFIX,
            ), $installSQL));

            $config = Config::get(Universe::current());
            $config->sql_revision		= $installRevision;
            $config->timezone			= @date_default_timezone_get();
            $config->lang	 			= $LNG->getLanguage();
            $config->OverviewNewsText	= $LNG['sql_welcome'] . $installVersion;
            $config->uni_name			= $LNG['fcm_universe'] . ' ' . Universe::current();
            $config->close_reason		= $LNG['sql_close_reason'];
            $config->moduls				= implode(';', array_fill(0, MODULE_AMOUNT - 1, 1));
            $config->VERSION			= $installVersion;
            $config->sql_revision		= $installSQL;

            $config->save();
        }
        catch (Exception $e)
        {
            @unlink('includes/config.php');

            $error = $e->getMessage();

            $this->assign(array(
                'class'   => 'fatalerror',
                'message' => $LNG['step3_db_error'].'</p><p>'.$error,
            ));
            $this->display('page.database.test');
        }

        HTTP::redirectTo('index.php?page=account');
    }
}