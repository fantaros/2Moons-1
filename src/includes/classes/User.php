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
 * @info $Id: Session.class.php 2793 2013-09-29 12:33:56Z slaver7 $
 * @link http://2moons.cc/
 */

class User extends Model
{
    private $data = false;

    /* @var Planet */
    private $currentPlanet;

    /* @var Language */
    private $langObj;

    /* @var Array */
    private $planetList = array();

    public function __construct($userId = NULL, $whereData = NULL)
    {
        $this->db = Database::get();

        if(is_numeric($userId))
        {
            $this->userData = $this->db->selectSingle("SELECT * FROM %USERS% WHERE id = :userId;", array(
                ':userId'   => $userId
            ));

            if(empty($userData))
            {
                $this->data = false;
            }
        }
        elseif(!empty($whereData) && is_array($whereData))
        {
            $whereSql   = array();
            $whereData  = array();

            foreach($whereData as $colum => $value)
            {
                $whereData[] = '`'.$colum.'` = :'.$colum;
                $whereSql[':'.$colum] = $this->db->escape($value);
            }

            $this->data = $this->db->selectSingle("SELECT * FROM %USERS% WHERE ".implode(',', $whereData), $whereSql);

            if(empty($this->userData))
            {
                $this->data = false;
            }
        }
    }

    public function isValid()
    {
        return !!empty($this->userData);
    }

    public function getNewMessageCount()
    {
        return $this->db->selectSingle('SELECT COUNT(*) as count
            FROM %MESSAGES
            WHERE message_owner = :userID
            AND message.message_unread = :unread'
        , array(
            ':userID'   => $this->userData['id'],
            ':unread'   => 1
        ));
    }

    public function getCurrentPlanet()
    {
        $this->currentPlanet = new Planet(Session::get()->planetId);
    }

    private function initLangObj()
    {
        $this->langObj      = new Language($this->data['lang']);
        $this->langObj->includeData(array('INGAME', 'TECH'));
    }

    public function getLangObj()
    {
        if(is_null($this->langObj))
        {
            $this->initLangObj();
        }

        return $this->langObj;
    }

    public function can($acl)
    {
        switch($acl)
        {
            case ACL_CAN_ENTER_CLOSED_GAME:
                return $this->data['auth'] == AUTH_ADM;
            default:
                return false;
        }
    }

    public function translate($key)
    {
        return $this->getLangObj()->$key;
    }

    private function initPlanetList()
    {
        $sql = "SELECT id, name, galaxy, system, planet, planet_type, image
			FROM %%PLANETS%%
			WHERE id_owner = :userId
			AND destroyed = :destroyed ORDER BY ";

        switch($this->data['planet_sort'])
        {
            case 0:
                $sql	.= 'id';
                break;
            case 1:
                $sql	.= 'galaxy, system, planet, planet_type';
                break;
            case 2:
                $sql	.= 'name';
                break;
        }

        $sql    .= $this->data['planet_sort_order'] == 1 ? 'DESC' : 'ASC';

        $planetsResult = Database::get()->select($sql, array(
            ':userId'		=> $this->data['id'],
            ':destroyed'	=> 0
        ));

        foreach($planetsResult as $planetRow)
        {
            $this->planetList[$planetRow['id']]	= $planetRow;
        }

        $this->planetList;
    }

    public function getPlanetList()
    {
        if(empty($this->planetList))
        {
            $this->initPlanetList();
        }

        return $this->planetList;
    }

    public function hasVacationMode()
    {
        return $this->data['urlaubs_modus'] == 1;
    }

    public function getServerTimeDifference()
    {
        return DateUtil::getUserTimeOffset($this->data['timezone']);
    }

    public function verifyPassword($password)
    {
        return $this->data['password'] === PlayerUtil::cryptPassword($password);
    }
}