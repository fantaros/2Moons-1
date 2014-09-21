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
 * @info $Id: ShowOverviewPage.class.php 2794 2013-09-29 21:46:22Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowOverviewPage extends AbstractGamePage 
{

    private function GetTeamspeakData()
	{

		$config = Config::get();

		if ($config->ts_modon == 0)
		{
			return false;
		}
		
		Cache::get()->add('teamspeak', 'TeamspeakBuildCache');
		$tsInfo	= Cache::get()->getData('teamspeak', false);
		
		if(empty($tsInfo))
		{
			return array(
				'error'	=> $this->lang['ov_teamspeak_not_online']
			);
		}

		$url = '';

		switch($config->ts_version)
		{
			case 2:
				$url = 'teamspeak://%s:%s?nickname=%s';
			break;
			case 3:
				$url = 'ts3server://%s?port=%d&amp;nickname=%s&amp;password=%s';
			break;
		}
		
		return array(
			'url'		=> sprintf($url, $config->ts_server, $config->ts_tcpport, $this->user->username, $tsInfo['password']),
			'current'	=> $tsInfo['current'],
			'max'		=> $tsInfo['maxuser'],
			'error'		=> false,
		);
	}

	private function GetFleets() {
		require 'includes/classes/FlyingFleetsTable.php';
		$fleetTableObj = new FlyingFleetsTable;
		$fleetTableObj->setUser($this->user->id);
		$fleetTableObj->setPlanet($this->planet->id);
		return $fleetTableObj->renderTable();
	}
	
	function savePlanetAction()
	{
		$password =	HTTP::_GP('password', '', true);
		if (!empty($password))
		{
			$db = Database::get();
            $sql = "SELECT COUNT(*) as state FROM %%FLEETS%% WHERE
                      (fleet_owner = :userID AND (fleet_start_id = :planetID OR fleet_start_id = :lunaID)) OR
                      (fleet_target_owner = :userID AND (fleet_end_id = :planetID OR fleet_end_id = :lunaID));";
            $IfFleets = $db->selectSingle($sql, array(
                ':userID'   => $this->user->id,
                ':planetID' => $this->planet->id,
                ':lunaID'   => $this->planet->id_luna
            ), 'state');

            if ($IfFleets > 0)
				exit(json_encode(array('message' => $this->lang['ov_abandon_planet_not_possible'])));
			elseif ($this->user->id_planet == $this->planet->id)
				exit(json_encode(array('message' => $this->lang['ov_principal_planet_cant_abanone'])));
			elseif (PlayerUtil::cryptPassword($password) != $this->user->password)
				exit(json_encode(array('message' => $this->lang['ov_wrong_pass'])));
			else
			{
				if($this->planet->planet_type == 1) {
					$sql = "UPDATE %%PLANETS%% SET destroyed = :time WHERE id = :planetID;";
                    $db->update($sql, array(
                        ':time'   => TIMESTAMP + 86400,
                        ':planetID' => $this->planet->id,
                    ));
                    $sql = "DELETE FROM %%PLANETS%% WHERE id = :lunaID;";
                    $db->delete($sql, array(
                        ':lunaID' => $this->planet->id_luna
                    ));
                } else {
                    $sql = "UPDATE %%PLANETS%% SET id_luna = 0 WHERE id_luna = :planetID;";
                    $db->update($sql, array(
                        ':planetID' => $this->planet->id,
                    ));
                    $sql = "DELETE FROM %%PLANETS%% WHERE id = :planetID;";
                    $db->delete($sql, array(
                        ':planetID' => $this->planet->id,
                    ));
                }
				
				$this->planet->id	= $this->user->id_planet;
				exit(json_encode(array('ok' => true, 'message' => $this->lang['ov_planet_abandoned'])));
			}
		}
	}
		
	function show()
	{

		$AdminsOnline 	= array();
		$chatOnline 	= array();
		$AllPlanets		= array();
		$Moon 			= array();
		$RefLinks		= array();
        $currentTasks   = array();

        $db             = Database::get();

        $currentTasksResult   = $this->ecoObj->getQueueObj()->getCurrentTaskFromAllPlanetsByElementClass(Vars::CLASS_BUILDING);
        foreach($currentTasksResult as $task)
        {
            $currentTasks[$task['planetId']]    = $task;
        }


		foreach($this->user->PLANETS as $planetId => $planetData)
		{		
			if ($planetId == $this->planet->id || $planetData['planet_type'] == MOON) continue;

            if(!isset($currentTasks[$planetId]) || $currentTasks[$planetId]['endBuildTime'] <= TIMESTAMP)
            {
                $currentTask    = false;
            }
            else
            {
                $currentTask    = $currentTasks[$planetId];
            }

			$AllPlanets[] = array(
				'id'	        => $planetData['id'],
				'name'	        => $planetData['name'],
				'image'	        => $planetData['image'],
				'currentTask'	=> $currentTask,
			);
		}
		
		if ($this->planet->id_luna != 0) {
			$sql = "SELECT id, name FROM %%PLANETS%% WHERE id = :lunaID;";
            $Moon = $db->selectSingle($sql, array(
                ':lunaID'   => $this->planet->id_luna
            ));
        }

        if(!isset($currentTasks[$this->planet->id]) || $currentTasks[$this->planet->id]['endBuildTime'] <= TIMESTAMP)
        {
            $buildInfo['buildings']	= false;
        }
        else
        {
            $task   = $currentTasks[$this->planet->id];
			$buildInfo['buildings']	= array(
				'id'		=> $task['elementId'],
				'level'		=> $task['amount'],
				'timeleft'	=> $task['endBuildTime'] - TIMESTAMP,
				'time'		=> $task['endBuildTime'],
				'starttime'	=> pretty_time($task['endBuildTime'] - TIMESTAMP),
			);
		}
		
		$sql = "SELECT id,username FROM %%USERS%% WHERE universe = :universe AND onlinetime >= :onlinetime AND authlevel > :authlevel;";
        $onlineAdmins = $db->select($sql, array(
            ':universe'     => Universe::current(),
            ':onlinetime'   => TIMESTAMP-10*60,
            ':authlevel'    => AUTH_USR
        ));

        foreach ($onlineAdmins as $AdminRow) {
			$AdminsOnline[$AdminRow['id']]	= $AdminRow['username'];
		}

        $sql = "SELECT userName FROM %%CHAT_ON%% WHERE dateTime > DATE_SUB(NOW(), interval 2 MINUTE) AND channel = 0";
        $chatUsers = $db->select($sql);

        foreach ($chatUsers as $chatRow) {
			$chatOnline[]	= $chatRow['userName'];
		}

		$Messages		= $this->user->messages;
		
		// Fehler: Wenn Spieler gelöscht werden, werden sie nicht mehr in der Tabelle angezeigt.
		$sql = "SELECT u.id, u.username, s.total_points FROM %%USERS%% as u
		LEFT JOIN %%STATPOINTS%% as s ON s.id_owner = u.id AND s.stat_type = '1' WHERE ref_id = :userID;";
        $RefLinksRAW = $db->select($sql, array(
            ':userID'   => $this->user->id
        ));

		$config	= Config::get();

        if($config->ref_active)
		{
			foreach ($RefLinksRAW as $RefRow) {
				$RefLinks[$RefRow['id']]	= array(
					'username'	=> $RefRow['username'],
					'points'	=> min($RefRow['total_points'], $config->ref_minpoints)
				);
			}
		}

		$sql	= 'SELECT total_points, total_rank
		FROM %%STATPOINTS%%
		WHERE id_owner = :userId AND stat_type = :statType';

		$statData	= Database::get()->selectSingle($sql, array(
			':userId'	=> $this->user->id,
			':statType'	=> 1
		));

		if($statData['total_rank'] == 0) {
			$rankInfo	= "-";
		} else {
			$rankInfo	= sprintf($this->lang['ov_userrank_info'], pretty_number($statData['total_points']), $this->lang['ov_place'],
				$statData['total_rank'], $statData['total_rank'], $this->lang['ov_of'], $config->users_amount);
		}
		
		$this->assign(array(
			'rankInfo'					=> $rankInfo,
			'is_news'					=> $config->OverviewNewsFrame,
			'news'						=> makebr($config->OverviewNewsText),
			'planetname'				=> $this->planet->name,
			'planetimage'				=> $this->planet->image,
			'galaxy'					=> $this->planet->galaxy,
			'system'					=> $this->planet->system,
			'planet'					=> $this->planet->planet,
			'planet_type'				=> $this->planet->planet_type,
			'username'					=> $this->user->username,
			'userid'					=> $this->user->id,
			'buildInfo'					=> $buildInfo,
			'Moon'						=> $Moon,
			'fleets'					=> $this->GetFleets(),
			'AllPlanets'				=> $AllPlanets,
			'AdminsOnline'				=> $AdminsOnline,
			'teamspeakData'				=> $this->GetTeamspeakData(),
			'messages'					=> ($Messages > 0) ? (($Messages == 1) ? $this->lang['ov_have_new_message'] : sprintf($this->lang['ov_have_new_messages'], pretty_number($Messages))): false,
			'planet_diameter'			=> pretty_number($this->planet->diameter),
			'planet_field_current' 		=> $this->planet->field_current,
			'planet_field_max' 			=> CalculateMaxPlanetFields($PLANET),
			'planet_temp_min' 			=> $this->planet->temp_min,
			'planet_temp_max' 			=> $this->planet->temp_max,
			'ref_active'				=> $config->ref_active,
			'ref_minpoints'				=> $config->ref_minpoints,
			'RefLinks'					=> $RefLinks,
			'chatOnline'				=> $chatOnline,
			'servertime'				=> _date("M D d H:i:s", TIMESTAMP, $this->user->timezone),
			'path'						=> HTTP_PATH,
		));
		
		$this->display('page.overview.default');
	}
	
	function actions() 
	{

		$this->initTemplate();
		$this->setWindow('popup');

		$this->assign(array(
			'ov_security_confirm'		=> sprintf($this->lang['ov_security_confirm'], $this->planet->name.' ['.$this->planet->galaxy.':'.$this->planet->system.':'.$this->planet->planet.']'),
		));
		$this->display('page.overview.actions');
	}
	
	function rename() 
	{

		$newname        = HTTP::_GP('name', '', UTF8_SUPPORT);
		if (!empty($newname))
		{
			if (!PlayerUtil::isNameValid($newname)) {
				$this->sendJSON(array('message' => $this->lang['ov_newname_specialchar'], 'error' => true));
			} else {
				$db = Database::get();
                $sql = "UPDATE %%PLANETS%% SET name = :newName WHERE id = :planetID;";
                $db->update($sql, array(
                    ':newName'  => $newname,
                    ':planetID' => $this->planet->id
                ));

                $this->sendJSON(array('message' => $this->lang['ov_newname_done'], 'error' => false));
			}
		}
	}
	
	function delete() 
	{
		$password	= HTTP::_GP('password', '', true);
		
		if (!empty($password))
		{
            $db = Database::get();
            $sql = "SELECT COUNT(*) as state FROM %%FLEETS%% WHERE
                      (fleet_owner = :userID AND (fleet_start_id = :planetID OR fleet_start_id = :lunaID)) OR
                      (fleet_target_owner = :userID AND (fleet_end_id = :planetID OR fleet_end_id = :lunaID));";
            $IfFleets = $db->selectSingle($sql, array(
                ':userID'   => $this->user->id,
                ':planetID' => $this->planet->id,
                ':lunaID'   => $this->planet->id_luna
            ), 'state');

			if ($IfFleets > 0) {
				$this->sendJSON(array('message' => $this->lang['ov_abandon_planet_not_possible']));
			} elseif ($this->user->id_planet == $this->planet->id) {
				$this->sendJSON(array('message' => $this->lang['ov_principal_planet_cant_abanone']));
			} elseif (PlayerUtil::cryptPassword($password) != $this->user->password) {
				$this->sendJSON(array('message' => $this->lang['ov_wrong_pass']));
			} else {
                if($this->planet->planet_type == 1) {
                    $sql = "UPDATE %%PLANETS%% SET destroyed = :time WHERE id = :planetID;";
                    $db->update($sql, array(
                        ':time'   => TIMESTAMP+ 86400,
                        ':planetID' => $this->planet->id,
                    ));
                    $sql = "DELETE FROM %%PLANETS%% WHERE id = :lunaID;";
                    $db->delete($sql, array(
                        ':lunaID' => $this->planet->id_luna
                    ));
                } else {
                    $sql = "UPDATE %%PLANETS%% SET id_luna = 0 WHERE id_luna = :planetID;";
                    $db->update($sql, array(
                        ':planetID' => $this->planet->id,
                    ));
                    $sql = "DELETE FROM %%PLANETS%% WHERE id = :planetID;";
                    $db->delete($sql, array(
                        ':planetID' => $this->planet->id,
                    ));
                }

                $session	= Session::get();
                $session->planetId = $this->user->id_planet;
				$this->sendJSON(array('ok' => true, 'message' => $this->lang['ov_planet_abandoned']));
			}
		}
	}
}