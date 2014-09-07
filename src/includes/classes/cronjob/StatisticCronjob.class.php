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
 * @version 2.0.0 (2011-12-10)
 * @info $Id: StatisticCronjob.class.php 2787 2013-08-13 20:30:56Z slaver7 $
 * @link http://code.google.com/p/2moons/
 */

class StatisticCronjob implements CronjobTask
{
    public function run()
    {
        $this->getUsers();
        $this->getPlanets();
        $this->getFleets();
    }

    private function insertData($category, $data)
    {
        $db     = Database::get();
        $sql = "INSERT INTO %%STATISTIC%% SET
                statisticCategory = :category,
                staticValue = :value,
                staticDate = :date,
                universe = :universe;";

        foreach($data as $userRow)
        {
            $db->insert($sql, array(
                ':category' => $category,
                ':value'    => $userRow['count'],
                ':universe' => $userRow['universe'],
                ':date'     => mktime(0, 0, 0),
            ));
        }
    }

    private function getFleets()
    {
        $db     = Database::get();

        $fleetData = $db->select("SELECT COUNT(*) as count, fleet_universe FROM %%LOG_FLEETS%%
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
    }
}