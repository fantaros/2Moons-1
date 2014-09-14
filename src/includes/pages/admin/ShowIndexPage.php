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
 * @info $Id: ShowLoginPage.php 2776 2013-08-05 21:30:40Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowIndexPage extends AbstractAdminPage
{
    public function show()
    {
        $db = Database::get();

        $fleetData = $this->_getStatisticData('staticDate');

        $fleetData = $db->select("SELECT COUNT(*) as count , fleet_universe FROM %%LOG_FLEETS%%
        WHERE fleet_owner != 0
        AND fleet_start_time BETWEEN :startDate AND :endDate
        GROUP BY fleet_universe;", array(
            ':startDate'    => strtotime('-1 day', mktime(0, 0, 0)),
            ':endDate'      => mktime(0, 0, 0),
        ));
        $this->insertData('fleetsTotal', $fleetData);

        $fleetData = $db->select("SELECT COUNT(*) as count, fleet_universe FROM %%LOG_FLEETS%%
        WHERE fleet_owner != 0
        AND fleet_type IN (1,2,9)
        AND fleet_start_time BETWEEN :startDate AND :endDate
        GROUP BY fleet_universe;", array(
            ':startDate'    => strtotime('-1 day', mktime(0, 0, 0)),
            ':endDate'      => mktime(0, 0, 0),
        ));
        $this->insertData('fleetsTotal', $fleetData);
    }

    private function getPlanets()
    {
        $db     = Database::get();

        $planetData = $db->select("SELECT COUNT(*) as count, universe FROM %%PLANETS%% WHERE universe != 0 GROUP BY universe;");
        $this->insertData('totalAllPlanets', $planetData);

        $planetData = $db->select("SELECT COUNT(*) as count, universe FROM %%PLANETS%% WHERE universe != 0 AND planet_type = '1' GROUP BY universe;");
        $this->insertData('totalPlanets', $planetData);

        $planetData = $db->select("SELECT COUNT(*) as count, universe FROM %%PLANETS%% WHERE universe != 0 AND planet_type = '3' GROUP BY universe;");
        $this->insertData('totalMoons', $planetData);
    }

    private function getUsers()
    {
        $db = Database::get();
        $userData = $db->select("SELECT COUNT(*) as count, universe FROM %%USERS%% WHERE universe != 0 GROUP BY universe;");
        $this->insertData('totalUsers', $userData);


        $userData = $db->select("SELECT COUNT(*) as count, universe FROM %%USERS%% WHERE register_time BETWEEN :startDate AND :endDate GROUP BY universe;", array(
            ':startDate'    => strtotime('-1 day', mktime(0, 0, 0)),
            ':endDate'      => mktime(0, 0, 0),
        ));
        $this->insertData('registeredUsers', $userData);


        $userData = $db->select("SELECT COUNT(*) as count, universe FROM %%USERS%% WHERE onlinetime BETWEEN :startDate AND :endDate GROUP BY universe;", array(
            ':startDate'    => strtotime('-1 day', mktime(0, 0, 0)),
            ':endDate'      => mktime(0, 0, 0),
        ));
        $this->insertData('onlineUsers', $userData);


        $this->display('page.index.default');
    }
}