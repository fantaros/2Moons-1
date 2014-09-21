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

class ShowAlliancePage extends AbstractGamePage
{
	public static $requireModule = MODULE_ALLIANCE;

	private $allianceData;
	private $ranks;
	private $rights;
	private $hasAlliance = false;
	private $hasApply = false;
	public $availableRanks	= array(
		'MEMBERLIST',
		'ONLINESTATE',
		'TRANSFER',
		'SEEAPPLY',
		'MANAGEAPPLY',
		'ROUNDMAIL',
		'ADMIN',
		'KICK',
		'DIPLOMATIC',
		'RANKS',
		'MANAGEUSERS',
		'EVENTS'
	);
	
	function __construct() 
	{
		parent::__construct();
		$this->hasAlliance	= $this->user->ally_id != 0;
		$this->hasApply		= $this->isApply();
		if($this->hasAlliance && !$this->hasApply) {
			$this->setAllianceData($this->user->ally_id);
		}
	}
	
	private function setAllianceData($allianceId)
	{
		$db	= Database::get();

        $sql	= 'SELECT * FROM %%ALLIANCE%% WHERE id = :allianceId;';
        $this->allianceData = $db->selectSingle($sql, array(
            ':allianceId'	=> $allianceId
        ));

        if($this->user->ally_id == $allianceId)
		{
			if ($this->allianceData['ally_owner'] == $this->user->id) {
				$this->rights	= array_combine($this->availableRanks, array_fill(0, count($this->availableRanks), true));
			} elseif($this->user->ally_rank_id != 0) {
				$sql	= 'SELECT '.implode(', ', $this->availableRanks).' FROM %%ALLIANCE_RANK%% WHERE allianceId = :allianceId AND rankID = :ally_rank_id;';
                $this->rights = $db->selectSingle($sql, array(
                    ':allianceId'		=> $allianceId,
                    ':ally_rank_id'		=> $this->user->ally_rank_id,
                ));
            }
			
			if(!isset($this->rights)) {
				$this->rights	= array_combine($this->availableRanks, array_fill(0, count($this->availableRanks), false));
			}
		
			if(isset($this->tplObj))
			{
				$this->assign(array(
					'rights'		=> $this->rights,
					'AllianceOwner'	=> $this->allianceData['ally_owner'] == $this->user->id,
				));
			}
		}
	}
	
	private function isApply()
	{
        $db	= Database::get();
        $sql = "SELECT COUNT(*) as count FROM %%ALLIANCE_REQUEST%% WHERE userId = :userId;";
        return $db->selectSingle($sql, array(
            ':userId'	=> $this->user->id
        ), 'count');
    }
	
	function info() 
	{
		$allianceId = HTTP::_GP('id', 0);

		$statisticData	= array();
		$diplomaticData	= false;

		$this->setAllianceData($allianceId);

		if(!isset($this->allianceData))
		{
			$this->printMessage($this->lang['al_not_exists']);
		}
		
		require 'includes/classes/BBCode.php';
		
		if ($this->allianceData['ally_diplo'] == 1)
		{
			$diplomaticData	= $this->getDiplomacy();
		}
		
		if ($this->allianceData['ally_stats'] == 1)
		{
            $sql	= 'SELECT SUM(wons) as wons, SUM(loos) as loos, SUM(draws) as draws, SUM(kbmetal) as kbmetal,
            SUM(kbcrystal) as kbcrystal, SUM(lostunits) as lostunits, SUM(desunits) as desunits
            FROM %%USERS%% WHERE ally_id = :allyID;';

            $statisticResult = Database::get()->selectSingle($sql, array(
                ':allyID'	=> $this->allianceData['id']
            ));

			$statisticData	= array(
				'totalfight'	=> $statisticResult['wons'] + $statisticResult['loos'] + $statisticResult['draws'],
				'fightwon'		=> $statisticResult['wons'],
				'fightlose'		=> $statisticResult['loos'],
				'fightdraw'		=> $statisticResult['draws'],
				'unitsshot'		=> pretty_number($statisticResult['desunits']),
				'unitslose'		=> pretty_number($statisticResult['lostunits']),
				'dermetal'		=> pretty_number($statisticResult['kbmetal']),
				'dercrystal'	=> pretty_number($statisticResult['kbcrystal']),
			);
		}

		$sql	= 'SELECT total_points
		FROM %%STATPOINTS%%
		WHERE id_owner = :userId AND stat_type = :statType';

		$userPoints	= Database::get()->selectSingle($sql, array(
			':userId'	=> $this->user->id,
			':statType'	=> 1
		), 'total_points');

		$this->assign(array(
			'diplomaticData'				=> $diplomaticData,
			'statisticData'					=> $statisticData,
			'ally_description' 				=> BBCode::parse($this->allianceData['ally_description']),
			'ally_id'	 					=> $this->allianceData['id'],
			'ally_image' 					=> $this->allianceData['ally_image'],
			'ally_web'						=> $this->allianceData['ally_web'],
			'ally_member_scount' 			=> $this->allianceData['ally_members'],
			'ally_max_members' 				=> $this->allianceData['ally_max_members'],
			'ally_name' 					=> $this->allianceData['ally_name'],
			'ally_tag' 						=> $this->allianceData['ally_tag'],
			'ally_stats' 					=> $this->allianceData['ally_stats'],
			'ally_diplo' 					=> $this->allianceData['ally_diplo'],
			'ally_request'              	=> !$this->hasAlliance && !$this->hasApply && $this->allianceData['ally_request_notallow'] == 0 && $this->allianceData['ally_max_members'] > $this->allianceData['ally_members'],
			'ally_request_min_points'		=> $userPoints >= $this->allianceData['ally_request_min_points'],
			'ally_request_min_points_info'  => sprintf($this->lang['al_requests_min_points'], pretty_number($this->allianceData['ally_request_min_points']))
		));
		
		$this->display('page.alliance.info');
	}
	
	function show() 
	{
		if($this->hasAlliance) {
			$this->homeAlliance();
		} elseif($this->hasApply) {		
			$this->applyWaitScreen();
		} else {		
			$this->createSelection();
		}
	}
	
	private function redirectToHome()
	{
		$this->redirectTo('game.php?page=alliance');
	}
	
	private function getAction()
	{
		return HTTP::_GP('action', '');
	}
	
	private function applyWaitScreen()
	{
		
		$db	= Database::get();
        $sql	= "SELECT a.ally_tag FROM %%ALLIANCE_REQUEST%% r INNER JOIN %%ALLIANCE%% a ON a.id = r.allianceId WHERE r.userId = :userId;";
        $allianceResult = $db->selectSingle($sql, array(
            ':userId'	=> $this->user->id_planet
        ));

        $this->assign(array(
			'request_text'	=> sprintf($this->lang['al_request_wait_message'], $allianceResult['ally_tag']),
		));     

		$this->display('page.alliance.applyWait');
	}
	
	private function createSelection()
	{
		$this->display('page.alliance.createSelection');
	}

	function search() 
	{

		if($this->hasApply) {
			$this->redirectToHome();
		}
		
		$searchText	= HTTP::_GP('searchtext', '', UTF8_SUPPORT);
		$searchList	= array();

		if (!empty($searchText))
		{
			$db	= Database::get();
            $sql	= "SELECT id, ally_name, ally_tag, ally_members
			FROM %%ALLIANCE%%
			WHERE ally_universe = :universe AND ally_name LIKE :searchText
			ORDER BY (
			  IF(ally_name = :searchText, 1, 0) + IF(ally_name LIKE :searchText, 1, 0)
			) DESC,ally_name ASC LIMIT :limit;";

            $searchResult	= $db->select($sql, array(
                ':universe' => Universe::current(),
                ':searchText'   => '%'.$searchText.'%',
                ':limit'	=> 25
            ));

            foreach($searchResult as $searchRow)
            {
                $searchList[]	= array(
                    'id'		=> $searchRow['id'],
                    'tag'		=> $searchRow['ally_tag'],
                    'members'	=> $searchRow['ally_members'],
                    'name'		=> $searchRow['ally_name'],
                );
            }
		}
		
		$this->assign(array(
			'searchText'	=> $searchText,
			'searchList'	=> $searchList,
		));	
		
		$this->display('page.alliance.search');
	}
	
	function apply()
	{
		
		if($this->hasApply) {
			$this->redirectToHome();
		}
		
		$text		= HTTP::_GP('text' , '', true);
		$allianceId	= HTTP::_GP('id', 0);
			
		$db 	= Database::get();
        $sql	= "SELECT ally_tag, ally_request, ally_request_notallow FROM %%ALLIANCE%% WHERE id = :allianceId AND ally_universe = :universe;";
        $allianceResult = $db->selectSingle($sql, array(
            ':allianceId'	=> $allianceId,
            ':universe'     => Universe::current()
        ));

        if (!isset($allianceResult)) {
			$this->redirectToHome();
		}
		
		if($allianceResult['ally_request_notallow'] == 1)
		{
			$this->printMessage($this->lang['al_alliance_closed'], array(array(
				'label'	=> $this->lang['sys_forward'],
				'url'	=> '?page=alliance'
			)));
		}

		if (!empty($text))
		{
			$sql = "INSERT INTO %%ALLIANCE_REQUEST%% SET
                allianceId	= :allianceId,
                text		= :text,
                time		= :time,
                userId		= :userId;";

            $db->insert($sql, array(
                ':allianceId'	=> $allianceId,
                ':text'			=> $text,
                ':time'			=> TIMESTAMP,
                ':userId'		=> $this->user->id
            ));

            $this->printMessage($this->lang['al_request_confirmation_message'], array(array(
				'label'	=> $this->lang['sys_forward'],
				'url'	=> '?page=alliance'
			)));
		}
		
		$this->assign(array(
			'allyid'			=> $allianceId,
			'applytext'			=> $allianceResult['ally_request'],
			'al_write_request'	=> sprintf($this->lang['al_write_request'], $allianceResult['ally_tag']),
		));	
		
		$this->display('page.alliance.apply');
	}
	
	function cancelApply()
	{
		
		if(!$this->hasApply) {
			$this->redirectToHome();
		}

		$db = Database::get();
        $sql	= "SELECT a.ally_tag FROM %%ALLIANCE_REQUEST%% r INNER JOIN %%ALLIANCE%% a ON a.id = r.allianceId WHERE r.userId = :userId;";
        $allyTag = $db->selectSingle($sql, array(
            ':userId'	=> $this->user->id
        ), 'ally_tag');

		$sql = "DELETE FROM %%ALLIANCE_REQUEST%% WHERE userId = :userId;";
		$db->delete($sql, array(
			':userId'	=> $this->user->id
		), 'ally_tag');
		
		$this->printMessage(sprintf($this->lang['al_request_deleted'], $allyTag), array(array(
			'label'	=> $this->lang['sys_forward'],
			'url'	=> '?page=alliance'
		)));
	}
	
	function create()
	{

		if($this->hasApply) {
			$this->redirectToHome();
		}
		$sql	= 'SELECT total_points
		FROM %%STATPOINTS%%
		WHERE id_owner = :userId AND stat_type = :statType';

		$userPoints	= Database::get()->selectSingle($sql, array(
			':userId'	=> $this->user->id,
			':statType'	=> 1
		), 'total_points');

		$min_points = Config::get()->alliance_create_min_points;
		
		if($userPoints >= $min_points)
		{
			$action    = $this->getAction();
			if($action == "send") {
				$this->createAlliance();
			} else {
				$this->display('page.alliance.create');
			}
		}
		else
		{
			$diff_points 	= $min_points - $userPoints;
			$messageText	= sprintf($this->lang['al_make_ally_insufficient_points'],
				pretty_number($min_points), pretty_number($diff_points));

			$this->printMessage($messageText, array(array(
				'label'	=> $this->lang['sys_back'],
				'url'	=> '?page=alliance'
			)));
		}
	}
	
	private function createAlliance()
	{
		$action	= $this->getAction();
		if($action == "send") {
			$this->createAllianceProcessor();
		} else {
			$this->display('page.alliance.create');
		}
	}
		
	private function createAllianceProcessor() 
	{
		$allianceTag	= HTTP::_GP('atag' , '', UTF8_SUPPORT);
		$allianceName	= HTTP::_GP('aname', '', UTF8_SUPPORT);
		
		if (empty($allianceTag)) {
			$this->printMessage($this->lang['al_tag_required'], array(array(
				'label'	=> $this->lang['sys_back'],
				'url'	=> '?page=alliance&mode=create'
			)));
		}
		
		if (empty($allianceName)) {
			$this->printMessage($this->lang['al_name_required'], array(array(
				'label'	=> $this->lang['sys_back'],
				'url'	=> '?page=alliance&mode=create'
			)));
		}
		
		if (!PlayerUtil::isNameValid($allianceName) || !PlayerUtil::isNameValid($allianceTag)) {
			$this->printMessage($this->lang['al_newname_specialchar'], array(array(
				'label'	=> $this->lang['sys_back'],
				'url'	=> '?page=alliance&mode=create'
			)));
		}
		
		$db		= Database::get();

		$sql	= 'SELECT COUNT(*) as count FROM %%ALLIANCE%% WHERE ally_universe = :universe
        AND (ally_tag = :allianceTag OR ally_name = :allianceName);';

		$allianceCount = $db->selectSingle($sql, array(
            ':universe'	=> Universe::current(),
            ':allianceTag' => $allianceTag,
            ':allianceName' => $allianceName
        ), 'count');

        if ($allianceCount != 0) {
			$this->printMessage(sprintf($this->lang['al_already_exists'], $allianceName), array(array(
				'label'	=> $this->lang['sys_back'],
				'url'	=> '?page=alliance&mode=create'
			)));
		}

        $sql	= "INSERT INTO %%ALLIANCE%% SET ally_name = :allianceName, ally_tag = :allianceTag, ally_owner = :userId,
        ally_owner_range = :allianceOwnerRange, ally_members = 1, ally_register_time = :time, ally_universe = :universe;";
        $db->insert($sql, array(
            ':allianceName'			=> $allianceName,
            ':allianceTag'			=> $allianceTag,
            ':userId'			    => $this->user->id,
            ':allianceOwnerRange'	=> $this->lang['al_default_leader_name'],
            ':time'                 => TIMESTAMP,
            ':universe'             => Universe::current(),
        ));

        $allianceId = $db->lastInsertId();

        $sql	= "UPDATE %%USERS%% SET ally_id	= :allianceId, ally_rank_id	= 0, ally_register_time = :time WHERE id = :userId;";
        $db->update($sql, array(
            ':allianceId'	=> $allianceId,
            ':time'			=> TIMESTAMP,
            ':userId'       => $this->user->id
        ));

        $sql	= "UPDATE %%STATPOINTS%% SET id_ally = :allianceId WHERE id_owner = :userId;";
        $db->update($sql, array(
            ':allianceId'	=> $allianceId,
            ':userId'       => $this->user->id
        ));

        $this->printMessage(sprintf($this->lang['al_created'], $allianceName.' ['.$allianceTag.']'), array(array(
			'label'	=> $this->lang['sys_forward'],
			'url'	=> '?page=alliance'
		)));
	}

	private function getDiplomacy()
	{
		$Return	= array();
		$db     = Database::get();

        $sql	= "SELECT d.level, d.accept, d.accept_text, d.id, a.id as ally_id, a.ally_name, a.ally_tag, d.owner_1, d.owner_2
            FROM %%DIPLO%% as d
            INNER JOIN %%ALLIANCE%% as a ON IF(:allianceId = d.owner_1, a.id = d.owner_2, a.id = d.owner_1)
            WHERE :allianceId = d.owner_1 OR :allianceId = d.owner_2;";

        $diplomacyResult	= $db->select($sql, array(
            ':allianceId'		=> $this->allianceData['id'],
        ));

        foreach($diplomacyResult as $diplomacy)
        {
            if($diplomacy['accept'] == 0 && $diplomacy['owner_2'] == $this->allianceData['id'])
            {
                $Return[5][$diplomacy['id']] = array($diplomacy['ally_name'], $diplomacy['ally_id'], $diplomacy['level'], $diplomacy['accept_text'], $diplomacy['ally_tag']);
            }
            elseif($diplomacy['accept'] == 0 && $diplomacy['owner_1'] == $this->allianceData['id'])
            {
                $Return[6][$diplomacy['id']] = array($diplomacy['ally_name'], $diplomacy['ally_id'], $diplomacy['level'], $diplomacy['accept_text'], $diplomacy['ally_tag']);
            }
            else
            {
                $Return[$diplomacy['level']][$diplomacy['id']] = array($diplomacy['ally_name'], $diplomacy['ally_id'], $diplomacy['owner_1'], $diplomacy['ally_tag']);
            }
        }
        return $Return;
	}

	private function homeAlliance()
	{
		require 'includes/classes/BBCode.php';

        $db	= Database::get();

		if ($this->allianceData['ally_owner'] == $this->user->id) {
			$rankName	= ($this->allianceData['ally_owner_range'] != '') ? $this->allianceData['ally_owner_range'] : $this->lang['al_founder_rank_text'];
		} elseif ($this->user->ally_rank_id != 0) {
            $sql	= "SELECT rankName FROM &&ALLIANCE_RANK&& WHERE rankID = :UserRankID;";
            $rankName = $db->selectSingle($sql, array(
                ':UserRankID'	=> $this->user->ally_rank_id
            ),'rankName');
        }
		
		if (empty($rankName)) {
			$rankName	= $this->lang['al_new_member_rank_text'];
		}

        $sql	= "SELECT SUM(wons) as wons, SUM(loos) as loos, SUM(draws) as draws, SUM(kbmetal) as kbmetal, SUM(kbcrystal) as kbcrystal, SUM(lostunits) as lostunits, SUM(desunits) as desunits FROM %%USERS%% WHERE ally_id = :AllianceID;";
        $statisticResult = $db->selectSingle($sql, array(
            ':AllianceID'	=> $this->allianceData['id']
        ));

        $sql = "SELECT COUNT(*) as count FROM %%ALLIANCE_REQUEST%% WHERE allianceId = :AllianceID;";
        $ApplyCount = $db->selectSingle($sql, array(
            ':AllianceID'	=> $this->allianceData['id']
        ),'count');

		$ally_events = array();
		
		if(!empty($this->allianceData['ally_events']))
		{
			$sql = "SELECT id, username FROM %%USERS%% WHERE ally_id = :AllianceID;";
            $result = $db->select($sql, array(
                ':AllianceID'	=> $this->allianceData['id']
            ));


			require_once('includes/classes/FlyingFleetsTable.php');
			$FlyingFleetsTable = new FlyingFleetsTable;
			
			$this->tplObj->loadscript('overview.js');
			
			foreach($result as $row)
			{
				$FlyingFleetsTable->setUser($row['id']);
				$FlyingFleetsTable->setMissions($this->allianceData['ally_events']);
				$ally_events[$row['username']] = $FlyingFleetsTable->renderTable();
			}
			
			$ally_events = array_filter($ally_events);
		}
		
		$this->assign(array(
			'diplomacyInfo'					=> $this->getDiplomacy(),
			'ally_web'					=> $this->allianceData['ally_web'],
			'ally_tag'	 				=> $this->allianceData['ally_tag'],
			'ally_members'	 			=> $this->allianceData['ally_members'],
			'ally_max_members'	 		=> $this->allianceData['ally_members'],
			'ally_name'					=> $this->allianceData['ally_name'],
			'ally_image'				=> $this->allianceData['ally_image'],
			'ally_description'			=> BBCode::parse($this->allianceData['ally_description']),
			'ally_text' 				=> BBCode::parse($this->allianceData['ally_text']),
			'rankName'					=> $rankName,
			'requests'					=> sprintf($this->lang['al_new_requests'], $ApplyCount),
			'applyCount'				=> $ApplyCount,
			'totalfight'				=> $statisticResult['wons'] + $statisticResult['loos'] + $statisticResult['draws'],
			'fightwon'					=> $statisticResult['wons'],
			'fightlose'					=> $statisticResult['loos'],
			'fightdraw'					=> $statisticResult['draws'],
			'unitsshot'					=> pretty_number($statisticResult['desunits']),
			'unitslose'					=> pretty_number($statisticResult['lostunits']),
			'dermetal'					=> pretty_number($statisticResult['kbmetal']),
			'dercrystal'				=> pretty_number($statisticResult['kbcrystal']),
			'isOwner'					=> $this->allianceData['ally_owner'] == $this->user->id,
			'ally_events'				=> $ally_events
		));
		
		$this->display('page.alliance.home');
	}

	public function memberList()
	{
		if (!$this->rights['MEMBERLIST']) {
			$this->redirectToHome();
		}

        $rankList	= array();

        $db = Database::get();
        $sql = "SELECT rankID, rankName FROM %%ALLIANCE_RANK%% WHERE allianceId = :AllianceID";
        $rankResult = $db->select($sql, array(
            ':AllianceID'	=> $this->allianceData['id']
        ));

        foreach($rankResult as $rankRow)
			$rankList[$rankRow['rankID']]	= $rankRow['rankName'];

        $memberList	= array();

        $sql = "SELECT DISTINCT u.id, u.username,u.galaxy, u.system, u.planet, u.ally_register_time, u.onlinetime, u.ally_rank_id, s.total_points FROM %%USERS%% u LEFT JOIN %%STATPOINTS%% as s ON s.stat_type = '1' AND s.id_owner = u.id WHERE ally_id = :AllianceID;";
        $memberListResult = $db->select($sql, array(
            ':AllianceID'	=> $this->allianceData['id']
        ));

        foreach ($memberListResult as $memberListRow)
		{
			if ($this->allianceData['ally_owner'] == $memberListRow['id'])
				$memberListRow['ally_rankName'] = empty($this->allianceData['ally_owner_range']) ? $this->lang['al_founder_rank_text'] : $this->allianceData['ally_owner_range'];
			elseif ($memberListRow['ally_rank_id'] != 0 && isset($rankList[$memberListRow['ally_rank_id']]))
				$memberListRow['ally_rankName'] = $rankList[$memberListRow['ally_rank_id']];
			else
				$memberListRow['ally_rankName'] = $this->lang['al_new_member_rank_text'];
			
			$memberList[$memberListRow['id']]	= array(
				'username'		=> $memberListRow['username'],
				'galaxy'		=> $memberListRow['galaxy'],
				'system'		=> $memberListRow['system'],
				'planet'		=> $memberListRow['planet'],
				'register_time'	=> _date($this->lang['php_tdformat'], $memberListRow['ally_register_time'], $this->user->timezone),
				'points'		=> $memberListRow['total_points'],
				'rankName'		=> $memberListRow['ally_rankName'],
				'onlinetime'	=> floor((TIMESTAMP - $memberListRow['onlinetime']) / 60),
			);
		}		
		
		$this->assign(array(
			'memberList'		=> $memberList,
			'al_users_list'		=> sprintf($this->lang['al_users_list'], count($memberList)),
		));
		
		$this->display('page.alliance.memberList');
	}

	public function close()
	{
        $db = Database::get();

        $sql	= "UPDATE %%USERS%% SET ally_id = 0, ally_register_time = 0, ally_register_time = 5 WHERE id = :UserID;";
        $db->update($sql, array(
            ':UserID'			=> $this->user->id
        ));

        $sql	= "UPDATE %%STATPOINTS%% SET id_ally = 0 WHERE id_owner = :UserID AND stat_type = 1;";
        $db->update($sql, array(
            ':UserID'			=> $this->user->id
        ));

        $sql	= "UPDATE %%ALLIANCE%% SET ally_members = (SELECT COUNT(*) FROM %%USERS%% WHERE ally_id = :AllianceID) WHERE id = :AllianceID;";
        $db->update($sql, array(
            ':AllianceID'			=> $this->allianceData['id']
        ));

        $this->redirectTo('game.php?page=alliance');
	}

	public function circular()
	{

		if (!$this->rights['ROUNDMAIL'])
			$this->redirectToHome();
		
		$action	= HTTP::_GP('action', '');

		if ($action == "send")
		{
			$rankId		= HTTP::_GP('rankID', 0);
			$subject 	= HTTP::_GP('subject', '', true);
			$text 		= HTTP::_GP('text', $this->lang['mg_no_subject'], true);
			
			if(empty($text)) {
				$this->sendJSON(array('message' => $this->lang['mg_empty_text'], 'error' => true));
			}

            $db = Database::get();

			if($rankId == 0) {
				$sql	= 'SELECT id, username FROM %%USERS%% WHERE ally_id = :AllianceID;';
				$sendUsersResult	= $db->select($sql, array(
					':AllianceID'	=> $this->allianceData['id'],
				));
			} else {
				$sql	= 'SELECT id, username FROM %%USERS%% WHERE ally_id = :AllianceID AND ally_rank_id = :RankID;';
				$sendUsersResult	= $db->select($sql, array(
					':AllianceID'	=> $this->allianceData['id'],
					':RankID'	    => $rankId
				));
			}

            $sendList 	= $this->lang['al_circular_sended'];
			$title		= $this->lang['al_circular_alliance'].$this->allianceData['ally_tag'];
			$text		= sprintf($this->lang['al_circular_front_text'], $this->user->username)."\r\n".$text;
			
			foreach ($sendUsersResult as $sendUsersRow)
			{
				PlayerUtil::sendMessage($sendUsersRow['id'], $this->user->id, TIMESTAMP, 2, $title, $subject, makebr($text));
				$sendList	.= "\n".$sendUsersRow['username'];
			}
				
			$this->sendJSON(array('message' => $sendList, 'error' => false));
		}

		$this->initTemplate();
		$this->setWindow('popup');
		$RangeList[]	= $this->lang['al_all_players'];

		if (is_array($this->ranks))
		{
			foreach($this->ranks as $id => $array)
			{
				$RangeList[$id + 1]	= $array['name'];
			}
		}
		
		$this->assign(array(
			'RangeList'						=> $RangeList,
		));
		
		$this->display('page.alliance.circular');
	}
	
	public function admin()
	{
		$action		= HTTP::_GP('action', 'overview');
		$methodName	= 'admin'.ucfirst($action);
		
		if(!is_callable(array($this, $methodName))) {
			$this->printMessage($this->lang['page_doesnt_exist']);
		}

		$this->{$methodName}();
	}

	protected function adminOverview()
	{
		$send 		= HTTP::_GP('send', 0);
		$textMode  	= HTTP::_GP('textMode', 'external');
		
		if ($send)
		{
            $db = Database::get();

			$this->allianceData['ally_owner_range'] 		= HTTP::_GP('owner_range', '', true);
			$this->allianceData['ally_web'] 				= filter_var(HTTP::_GP('web', ''), FILTER_VALIDATE_URL);
			$this->allianceData['ally_image'] 				= filter_var(HTTP::_GP('image', ''), FILTER_VALIDATE_URL);
			$this->allianceData['ally_request_notallow'] 	= HTTP::_GP('request_notallow', 0);
			$this->allianceData['ally_max_members'] 		= max(HTTP::_GP('ally_max_members', ''), $this->allianceData['ally_members']);
			$this->allianceData['ally_request_min_points']  = filter_var(HTTP::_GP('request_min_points', 0), FILTER_VALIDATE_INT);
			$this->allianceData['ally_stats'] 				= HTTP::_GP('stats', 0);
			$this->allianceData['ally_diplo'] 				= HTTP::_GP('diplo', 0);
			$this->allianceData['ally_events'] 				= implode(',', HTTP::_GP('events', array()));

			$new_ally_tag 	= HTTP::_GP('ally_tag', $this->allianceData['ally_tag'], UTF8_SUPPORT);
			$new_ally_name	= HTTP::_GP('ally_name', $this->allianceData['ally_name'], UTF8_SUPPORT);
		
			if(!empty($new_ally_tag) && $this->allianceData['ally_tag'] != $new_ally_tag)
			{
				$sql = "SELECT COUNT(*) as count FROM %%ALLIANCE%% WHERE ally_universe = :universe AND ally_tag = :NewAllianceTag;";
                $allianceCount = $db->selectSingle($sql, array(
                    ':universe'	        => Universe::current(),
                    ':NewAllianceTag'   => $new_ally_tag
                ), 'count');

                if($allianceCount != 0)
				{
					$this->printMessage(sprintf($this->lang['al_already_exists'], $new_ally_tag), array(array(
						'label'	=> $this->lang['sys_back'],
						'url'	=> 'game.php?page=alliance&mode=admin'
					)));
				}
				else
				{
					$this->allianceData['ally_tag'] = $new_ally_tag;
				}
			}
			
			if(!empty($new_ally_name) && $this->allianceData['ally_name'] != $new_ally_name)
			{
                $sql = "SELECT COUNT(*) as count FROM %%ALLIANCE%% WHERE ally_universe = :universe AND ally_name = :NewAllianceName;";
                $allianceCount = $db->selectSingle($sql, array(
                    ':universe'	        => Universe::current(),
                    ':NewAllianceName'   => $new_ally_name
                ), 'count');

				if($allianceCount != 0)
				{
					$this->printMessage(sprintf($this->lang['al_already_exists'], $new_ally_name), array(array(
						'label'	=> $this->lang['sys_back'],
						'url'	=> 'game.php?page=alliance&mode=admin'
					)));
				}
				else
				{
					$this->allianceData['ally_name'] = $new_ally_name;
				}
			}
			
			if ($this->allianceData['ally_request_notallow'] != 0 && $this->allianceData['ally_request_notallow'] != 1) {
				$this->allianceData['ally_request_notallow'] = 0;
			}

			$text 		= HTTP::_GP('text', '', true);
			$textMode  	= HTTP::_GP('textMode', 'external');
			
			$textSQL	= "";
			
			switch($textMode)
			{
				case 'external':
					$textSQL	= "ally_description = :text, ";
				break;
				case 'internal':
					$textSQL	= "ally_text = :text, ";
				break;
				case 'apply':
					$textSQL	= "ally_request = :text, ";
				break;
			}
			
			$sql = "UPDATE %%ALLIANCE%% SET
			".$textSQL."
			ally_tag = :AllianceTag,
			ally_name = :AllianceName,
			ally_owner_range = :AllianceOwnerRange,
			ally_image = :AllianceImage,
			ally_web = :AllianceImage,
			ally_request_notallow = :AllianceWeb,
			ally_max_members = :AllianceRequestNotallow,
			ally_request_min_points = :AllianceRequestMinpoints,
			ally_stats = :AllianceStats,
			ally_diplo = :Alliancediplomacy,
			ally_events = :AllianceEvents
			WHERE id = :AllianceID;";

            $db->update($sql, array(
                ':AllianceTag'				=> $this->allianceData['ally_tag'],
                ':AllianceName'				=> $this->allianceData['ally_name'],
                ':AllianceOwnerRange'		=> $this->allianceData['ally_owner_range'],
                ':AllianceImage'			=> $this->allianceData['ally_image'],
                ':AllianceWeb'				=> $this->allianceData['ally_web'],
                ':AllianceRequestNotallow'	=> $this->allianceData['ally_request_notallow'],
                ':AllianceRequestMinpoints'	=> $this->allianceData['ally_request_min_points'],
                ':AllianceStats'			=> $this->allianceData['ally_stats'],
                ':Alliancediplomacy'			=> $this->allianceData['ally_diplo'],
                ':AllianceEvents'			=> $this->allianceData['ally_events'],
                ':AllianceID'				=> $this->allianceData['id'],
                ':text'						=> $text
            ));

		} else {
			switch($textMode)
			{
				case 'internal':
					$text	= $this->allianceData['ally_text'];
				break;
				case 'apply':
					$text	= $this->allianceData['ally_request'];
				break;
				default:
					$text	= $this->allianceData['ally_description'];
				break;
			}
		}
		
		$this->assign(array(
			'RequestSelector'			=> array(0 => $this->lang['al_requests_allowed'], 1 => $this->lang['al_requests_not_allowed']),
			'YesNoSelector'				=> array(1 => $this->lang['al_go_out_yes'], 0 => $this->lang['al_go_out_no']),
			'textMode' 					=> $textMode,
			'text' 						=> $text,
			'ally_tag' 					=> $this->allianceData['ally_tag'],
			'ally_name'					=> $this->allianceData['ally_name'],
			'ally_web' 					=> $this->allianceData['ally_web'],
			'ally_image'				=> $this->allianceData['ally_image'],
			'ally_request_notallow' 	=> $this->allianceData['ally_request_notallow'],
			'ally_members' 				=> $this->allianceData['ally_members'],
			'ally_max_members' 			=> $this->allianceData['ally_max_members'],
			'ally_request_min_points'   => $this->allianceData['ally_request_min_points'],
			'ally_owner_range'			=> $this->allianceData['ally_owner_range'],
			'ally_stats_data'			=> $this->allianceData['ally_stats'],
			'ally_diplo_data'			=> $this->allianceData['ally_diplo'],
			'ally_events'				=> explode(',', $this->allianceData['ally_events']),
			'aviable_events'			=> $this->lang['type_mission']
		));
		
		$this->display('page.alliance.admin.overview');
	}

	protected function adminClose()
	{
		if ($this->allianceData['ally_owner'] == $this->user->id) {
            $db = Database::get();

            $sql = "UPDATE %%USERS%% SET ally_id = '0' WHERE ally_id = :AllianceID;";
            $db->update($sql, array(
                ':AllianceID'	=> $this->allianceData['id']
            ));

            $sql = "UPDATE %%STATPOINTS%% SET id_ally = '0' WHERE id_ally = :AllianceID;";
            $db->update($sql, array(
                ':AllianceID'	=> $this->allianceData['id']
            ));

            $sql = "DELETE FROM %%STATPOINTS%% WHERE id_owner = :AllianceID AND stat_type = 2;";
            $db->delete($sql, array(
                ':AllianceID'	=> $this->allianceData['id']
            ));

            $sql = "DELETE FROM %%ALLIANCE%% WHERE id = :AllianceID;";
            $db->delete($sql, array(
                ':AllianceID'	=> $this->allianceData['id']
            ));

            $sql = "DELETE FROM %%ALLIANCE_REQUEST%% WHERE allianceId = :AllianceID;";
            $db->delete($sql, array(
                ':AllianceID'	=> $this->allianceData['id']
            ));

            $sql = "DELETE FROM %%DIPLO%% WHERE owner_1 = :AllianceID OR owner_2 = :AllianceID;";
            $db->delete($sql, array(
                ':AllianceID'	=> $this->allianceData['id']
            ));
        }
		
		$this->redirectToHome();
	}

	protected function adminTransfer()
	{

		if($this->allianceData['ally_owner'] != $this->user->id)
		{
			$this->redirectToHome();
		}

        $db	= Database::get();

		$postleader = HTTP::_GP('newleader', 0);
		if (!empty($postleader))
		{
            $sql = "SELECT ally_rank_id FROM %%USERS%% WHERE id = :LeaderID;";
            $Rank = $db->selectSingle($sql, array(
                ':LeaderID'	=> $postleader
            ));

            $sql = "UPDATE %%USERS%% SET ally_rank_id = :AllyRank WHERE id = :UserID;";
            $db->update($sql, array(
                ':UserID'	=> $this->user->id,
                ':AllyRank' => $Rank['ally_rank_id']
            ));

            $sql = "UPDATE %%USERS%% SET ally_rank_id = 0 WHERE id = :LeaderID;";
            $db->update($sql, array(
                ':LeaderID'	=> $postleader
            ));

            $sql = "UPDATE %%ALLIANCE%% SET ally_owner = :LeaderID WHERE id = :AllianceID;";
            $db->update($sql, array(
                ':LeaderID'	    => $postleader,
                ':AllianceID'   => $this->allianceData['id']
            ));

            $this->redirectToHome();
		}
		else
		{
			$sql = "SELECT u.id, r.rankName, u.username FROM %%USERS%% u INNER JOIN %%ALLIANCE_RANK%% r ON r.rankID = u.ally_rank_id AND r.TRANSFER = 1 WHERE u.ally_id = :allianceId AND id != ':allianceOwner;";
            $transferUserResult = $db->select($sql, array(
                ':allianceOwner'    => $this->allianceData['ally_owner'],
                ':allianceId'       => $this->allianceData['id']
            ));

            $transferUserList	= array();

			foreach ($transferUserResult as $transferUserRow)
			{
				$transferUserList[$transferUserRow['id']]	= $transferUserRow['username']." [".$transferUserRow['rankName']."]";
			}
			
			$this->assign(array(
				'transferUserList'	=> $transferUserList,
			));	
			
			$this->display('page.alliance.admin.transfer');
		}
	}

	protected function adminMangeApply()
	{
		if(!$this->rights['SEEAPPLY'] || !$this->rights['MANAGEAPPLY']) {
			$this->redirectToHome();
		}

		$db = Database::get();

        $sql = "SELECT applyID, u.username, r.time FROM %%ALLIANCE_REQUEST%% r INNER JOIN %%USERS%% u ON r.userId = u.id WHERE r.allianceId = :allianceId;";
        $applyResult = $db->select($sql, array(
            ':allianceId'	=> $this->allianceData['id']
        ));

        $applyList		= array();
		
		foreach ($applyResult as $applyRow)
		{
			$applyList[]	= array(
				'username'	=> $applyRow['username'],
				'id'		=> $applyRow['applyID'],
				'time' 		=> _date($this->lang['php_tdformat'], $applyRow['time'], $this->user->timezone),
			);
		}
		
		$this->assign(array(
			'applyList'		=> $applyList,
		));
		
		$this->display('page.alliance.admin.mangeApply');
	}

	protected function adminDetailApply()
	{
		if(!$this->rights['SEEAPPLY'] || !$this->rights['MANAGEAPPLY']) {
			$this->redirectToHome();
		}

		$id    = HTTP::_GP('id', 0);

        $db = Database::get();

		$sql = 'SELECT
			r.`applyID`,
			r.`time`,
			r.`text`,
			u.`username`,
			u.`register_time`,
			u.`onlinetime`,
			u.`galaxy`,
			u.`system`,
			u.`planet`,
			CONCAT_WS(\':\', u.`galaxy`, u.`system`, u.`planet`) AS `coordinates`,
			@total_fights := u.`wons` + u.`loos` + u.`draws`,
			@total_fights_percentage := @total_fights / 100,
			@total_fights AS `total_fights`,
			u.`wons`,
			ROUND(u.`wons` / @total_fights_percentage, 2) AS `wons_percentage`,
			u.`loos`,
			ROUND(u.`loos` / @total_fights_percentage, 2) AS `loos_percentage`,
			u.`draws`,
			ROUND(u.`draws` / @total_fights_percentage, 2) AS `draws_percentage`,
			u.`kbmetal`,
			u.`kbcrystal`,
			u.`lostunits`,
			u.`desunits`,
			stat.`tech_rank`,
			stat.`tech_points`,
			stat.`build_rank`,
			stat.`build_points`,
			stat.`defs_rank`,
			stat.`defs_points`,
			stat.`fleet_rank`,
			stat.`fleet_points`,
			stat.`total_rank`,
			stat.`total_points`,
			p.`name`
		FROM
			%%ALLIANCE_REQUEST%% AS r
		LEFT JOIN
			%%USERS%% AS u ON r.userId = u.id
		INNER JOIN
			%%STATPOINTS%% AS stat
		LEFT JOIN
			%%PLANETS%% AS p ON p.id = u.id_planet
		WHERE
			applyID = :applyID;';

        $applyDetail = $db->selectSingle($sql, array(
            ':applyID'	=> $id
        ));

        if(empty($applyDetail)) {
			$this->printMessage($this->lang['al_apply_not_exists'], array(array(
				'label'	=> $this->lang['sys_back'],
				'url'	=> 'game.php?page=alliance&mode=admin&action=mangeApply'
			)));
		}
		
		require 'includes/classes/BBCode.php';

		$applyDetail['text']    	= BBCode::parse($applyDetail['text']);
		$applyDetail['kbmetal']    	= pretty_number($applyDetail['kbmetal']);
		$applyDetail['kbcrystal']   = pretty_number($applyDetail['kbcrystal']);
		$applyDetail['lostunits']   = pretty_number($applyDetail['lostunits']);
		$applyDetail['desunits']    = pretty_number($applyDetail['desunits']);
		
		$this->assign(array(
			'applyDetail'	=> $applyDetail,
			'apply_time'    => _date($this->lang['php_tdformat'], $applyDetail['time'], $this->user->timezone),
			'register_time' => _date($this->lang['php_tdformat'], $applyDetail['register_time'], $this->user->timezone),
			'onlinetime'    => _date($this->lang['php_tdformat'], $applyDetail['onlinetime'], $this->user->timezone),
		));
		
		$this->display('page.alliance.admin.detailApply');
	}
	
	protected function adminSendAnswerToApply()
	{
		if(!$this->rights['SEEAPPLY'] || !$this->rights['MANAGEAPPLY']) {
			$this->redirectToHome();
		}

		$db = Database::get();

        $text  		= makebr(HTTP::_GP('text', '', true));
		$answer		= HTTP::_GP('answer', '');
		$applyID	= HTTP::_GP('id', 0);

        $sql = "SELECT userId FROM %%ALLIANCE_REQUEST%% WHERE applyID = :applyID;";
        $userId = $db->selectSingle($sql, array(
            ':applyID'	=> $applyID
        ), 'userId');

		if ($answer == 'yes')
		{
			$sql = "DELETE FROM %%ALLIANCE_REQUEST%% WHERE applyID = :applyID";
            $db->delete($sql, array(
                ':applyID'	=> $applyID
            ));

            $sql = "UPDATE %%USERS%% SET ally_id = :allianceId, ally_register_time = :time, ally_rank_id = 0 WHERE id = :userId;";
            $db->update($sql, array(
                ':allianceId'	=> $this->allianceData['id'],
                ':time'         => TIMESTAMP,
                ':userId'       => $userId
            ));

            $sql = "UPDATE %%STATPOINTS%% SET id_ally = :allianceId WHERE id_owner = :userId AND stat_type = 1;";
            $db->update($sql, array(
                ':allianceId'	=> $this->allianceData['id'],
                ':userId'       => $userId
            ));

            $sql = "UPDATE %%ALLIANCE%% SET ally_members = (SELECT COUNT(*) FROM %%USERS%% WHERE ally_id = :allianceId) WHERE id = :allianceId;";
            $db->update($sql, array(
                ':allianceId'	=> $this->allianceData['id'],
            ));

			$text		= $this->lang['al_hi_the_alliance'] . $this->allianceData['ally_name'] . $this->lang['al_has_accepted'] . $text;
			$subject	= $this->lang['al_you_was_acceted'] . $this->allianceData['ally_name'];
		}
		else
		{
            $sql = "DELETE FROM %%ALLIANCE_REQUEST%% WHERE applyID = :applyID";
            $db->delete($sql, array(
                ':applyID'	=> $applyID
            ));

			$text		= $this->lang['al_hi_the_alliance'] . $this->allianceData['ally_name'] . $this->lang['al_has_declined'] . $text;
			$subject	= $this->lang['al_you_was_declined'] . $this->allianceData['ally_name'];
        }

		$senderName	= $this->lang['al_the_alliance'] . $this->allianceData['ally_name'] . ' ['.$this->allianceData['ally_tag'].']';
		PlayerUtil::sendMessage($userId, $this->user->id, $senderName, 2, $subject, $text, TIMESTAMP);
		$this->redirectTo('game.php?page=alliance&mode=admin&action=mangeApply');
	}

	protected function adminPermissions()
	{	
		if(!$this->rights['RANKS']) {
			$this->redirectToHome();
		}

        $sql = "SELECT * FROM %%ALLIANCE_RANK%% WHERE allianceId = :allianceId;";
        $rankResult = Database::get()->select($sql, array(
            ':allianceId'	=> $this->allianceData['id']
        ));

        $rankList	= array();
		foreach ($rankResult as $rankRow)
		{
			$rankList[$rankRow['rankID']]	= $rankRow;
		}

		$availableRanks	= array();
		foreach($this->availableRanks as $rankId => $rankName)
		{
			if($this->rights[$rankName])
			{
				$availableRanks[$rankId]	= $rankName;
			}
		}

		$this->assign(array(
			'rankList'			=> $rankList,
			'ownRights'			=> $this->rights,
			'availableRanks'	=> $availableRanks,
		));	

		$this->display('page.alliance.admin.permissions');
	}
	
	protected function adminPermissionsSend()
	{
		if(!$this->rights['RANKS']) {
			$this->redirectToHome();
		}

		$newRank	= HTTP::_GP('newrank', array(), true);
		$delete		= HTTP::_GP('deleteRank', 0);
		$rankData	= HTTP::_GP('rank', array());

        $db = Database::get();

		if(!empty($newRank['rankName']))
		{
			if(!PlayerUtil::isNameValid($newRank['rankName']))
			{
				$this->printMessage($this->lang['al_invalid_rank_name'], array(array(
					'label'	=> $this->lang['sys_back'],
					'url'	=> '?page=alliance&mode=admin&action=permission'
				)));
			}

			$sql = 'INSERT INTO %%ALLIANCE_RANK%% SET rankName = :rankName, allianceID = :allianceID';
			$params	= array(
				':rankName'		=> $newRank['rankName'],
				':allianceID'	=> $this->allianceData['id'],
			);

			unset($newRank['rankName']);

			foreach($newRank as $key => $value)
			{
				if(isset($this->availableRanks[$key]) && $this->rights[$this->availableRanks[$key]])
				{
					$sql .= ', `'.$this->availableRanks[$key].'` = :'.$this->availableRanks[$key];
					$params[':'.$this->availableRanks[$key]]	= $value == 1 ? 1 : 0;
				}
			}

			$db->insert($sql, $params);
		}
		else
		{
			if(!empty($delete)) 
			{
				$sql = "DELETE FROM %%ALLIANCE_RANK%% WHERE rankID = :rankID AND allianceId = :allianceId;";
                $db->delete($sql, array(
                    ':allianceId'	=> $this->allianceData['id'],
                    ':rankID'       => $delete
                ));

                $sql = "UPDATE %%USERS%% SET ally_rank_id = 0 WHERE ally_rank_id = :rankID AND ally_id = :allianceId;";
                $db->update($sql, array(
                    ':allianceId'	=> $this->allianceData['id'],
                    ':rankID'       => $delete
                ));
			}
			else
			{
				foreach ($rankData as $rankId => $rowData)
				{
					$sql = 'UPDATE %%ALLIANCE_RANK%% SET rankName = :rankName';
					$params	= array(
						':rankName'		=> $rowData['rankName'],
						':allianceID'	=> $this->allianceData['id'],
						':rankId'		=> $rankId
					);

					unset($rowData['rankName']);

					foreach($rowData as $key => $value)
					{
						if(isset($this->availableRanks[$key]) && $this->rights[$this->availableRanks[$key]])
						{
							$sql .= ', `'.$this->availableRanks[$key].'` = :'.$this->availableRanks[$key];
							$params[':'.$this->availableRanks[$key]]	= $value == 1 ? 1 : 0;
						}
					}

					$sql .= ' WHERE rankID = :rankId AND allianceID = :allianceID';

					$db->update($sql, $params);
				}
			}
		}
		
		$this->redirectTo('game.php?page=alliance&mode=admin&action=permissions');
	}

	protected function adminMembers()
	{
		if (!$this->rights['MANAGEUSERS']) {
			$this->redirectToHome();
		}
		
		$db = Database::get();

        $sql = "SELECT rankID, rankName FROM %%ALLIANCE_RANK%% WHERE allianceId = :allianceId;";
        $rankResult = $db->select($sql, array(
            ':allianceId'	=> $this->allianceData['id'],
        ));

        $rankList		= array();
		$rankSelectList	= array();
		$rankList[0]	= $this->lang['al_new_member_rank_text'];

		foreach($rankResult as $rankRow)
		{
			$hasRankRight	= true;
			foreach($this->availableRanks as $rankName)
			{
				if(!$this->rights[$rankName])
				{
					$hasRankRight = false;
					break;
				}
			}

			if($hasRankRight)
			{
				$rankSelectList[$rankRow['rankID']]	= $rankRow;
			}

			$rankList[$rankRow['rankID']]	= $rankRow;
		}

		$sql = "SELECT DISTINCT u.id, u.username,u.galaxy, u.system, u.planet, u.ally_register_time, u.onlinetime, u.ally_rank_id, s.total_points
		FROM %%USERS%% u
		LEFT JOIN %%STATPOINTS%% as s ON s.stat_type = '1' AND s.id_owner = u.id
		WHERE ally_id = :allianceId;";
		
        $memberListResult = $db->select($sql, array(
            ':allianceId'	=> $this->allianceData['id'],
        ));

		$memberList	= array();
										
		foreach ($memberListResult as $memberListRow)
		{
			if ($this->allianceData['ally_owner'] == $memberListRow['id'])
				$memberListRow['ally_rank_id'] = -1;
			
			$memberList[$memberListRow['id']]	= array(
				'username'		=> $memberListRow['username'],
				'galaxy'		=> $memberListRow['galaxy'],
				'system'		=> $memberListRow['system'],
				'planet'		=> $memberListRow['planet'],
				'register_time'	=> _date($this->lang['php_tdformat'], $memberListRow['ally_register_time'], $this->user->timezone),
				'points'		=> $memberListRow['total_points'],
				'rankID'		=> $memberListRow['ally_rank_id'],
				'onlinetime'	=> floor((TIMESTAMP - $memberListRow['onlinetime']) / 60),
				'kickQuestion'	=> sprintf($this->lang['al_kick_player'], $memberListRow['username'])
			);
		}
		
		$this->assign(array(
			'memberList'	=> $memberList,
			'rankList'		=> $rankList,
			'founder'		=> empty($this->allianceData['ally_owner_range']) ? $this->lang['al_founder_rank_text'] : $this->allianceData['ally_owner_range'],
			'al_users_list'	=> sprintf($this->lang['al_users_list'], count($memberList)),
			'canKick'		=> $this->rights['KICK'],
		));
		
		$this->display('page.alliance.admin.members');
	}

	protected function adminMembersSave()
	{
		if (!$this->rights['MANAGEUSERS']) {
			$this->redirectToHome();
		}

		$userRanks	= HTTP::_GP('rank', array());

        $db = Database::get();

		$sql			= 'SELECT rankID, '.implode(', ', $this->availableRanks).' FROM %%ALLIANCE_RANK%% WHERE allianceID = :allianceId;';
		$rankResult		= $db->select($sql, array(
			':allianceId'	=> $this->allianceData['id']
		));
		$rankList		= array();
		$rankList[0]	= array_combine($this->availableRanks, array_fill(0, count($this->availableRanks), true));
		
		foreach($rankResult as $rankRow)
		{
			$hasRankRight	= true;
			foreach($this->availableRanks as $rankName)
			{
				if(!$this->rights[$rankName])
				{
					$hasRankRight = false;
					break;
				}
			}

			if($hasRankRight)
			{
				$rankList[$rankRow['rankID']]	= $rankRow;
			}
		}

		foreach($userRanks as $userId => $rankId)
		{
			if($userId == $this->allianceData['ally_owner'] || !isset($rankList[$rankId])) {
				continue;
			}

            $sql = 'UPDATE %%USERS%% SET ally_rank_id = :rankID WHERE id = :userId AND ally_id = :allianceId;';
            $db->update($sql, array(
                ':allianceId'	=> $this->allianceData['id'],
                ':rankID'       => (int) $rankId,
                ':userId'       => (int) $userId
            ));
		}
		
		$this->redirectTo('game.php?page=alliance&mode=admin&action=members');
	}

	protected function adminMembersKick()
	{
		if (!$this->rights['KICK']) {
			$this->redirectToHome();
		}

        $db = Database::get();

		$id	= HTTP::_GP('id', 0);

        $sql = "UPDATE %%USERS%% SET ally_id = 0, ally_register_time = 0, ally_rank_id = 0 WHERE id = :id;";
        $db->update($sql, array(
            ':id'	=> $id
        ));

        $sql = "UPDATE %%STATPOINTS%% SET id_ally = 0 WHERE id_owner = :id AND stat_type = 1;";
        $db->update($sql, array(
            ':id'	=> $id
        ));

        $sql = "UPDATE %%ALLIANCE%% SET ally_members = (SELECT COUNT(*) FROM %%USERS%% WHERE ally_id = :allianceId) WHERE id = :allianceId;";
        $db->update($sql, array(
            ':id'	        => $id,
            ':allianceId'   => $this->allianceData['id']
        ));

        $this->redirectTo('game.php?page=alliance&mode=admin&action=members');
	}

	protected function admindiplomacymacy()
	{
		if (!$this->rights['DIPLOMATIC']) {
			$this->redirectToHome();
		}

        $db = Database::get();
		
		$diploList	= array(
			0 => array(
				1 => array(),
				2 => array(),
				3 => array(),
				4 => array(),
				5 => array(),
				6 => array()
			),
			1 => array(
				1 => array(),
				2 => array(),
				3 => array(),
				4 => array(),
				5 => array(),
				6 => array()
			),
			2 => array(
				1 => array(),
				2 => array(),
				3 => array(),
				4 => array(),
				5 => array(),
				6 => array()
			)
		);

        $sql = "SELECT d.id, d.level, d.accept, d.owner_1, d.owner_2, a.ally_name FROM %%DIPLO%% d
		INNER JOIN %%ALLIANCE%% a ON IF(:allianceId = d.owner_1, a.id = d.owner_2, a.id = d.owner_1)
		WHERE owner_1 = :allianceId OR owner_2 = :allianceId;";
        $diploResult =  $db->select($sql, array(
            ':allianceId'   => $this->allianceData['id']
        ));

		foreach($diploResult as $diploRow) {
			$own	= $diploRow['owner_1'] == $this->allianceData['id'];
			if($diploRow['accept'] == 1) {
				$diploList[0][$diploRow['level']][$diploRow['id']] = $diploRow['ally_name'];
			} elseif($own) {
				$diploList[2][$diploRow['level']][$diploRow['id']] = $diploRow['ally_name'];
			} else {
				$diploList[1][$diploRow['level']][$diploRow['id']] = $diploRow['ally_name'];
			}
		}
		
		$this->assign(array(
			'diploList'	=> $diploList,
		));
		
		$this->display('page.alliance.admin.diplomacy.default');
	}

	protected function admindiplomacymacyAccept()
	{
		if (!$this->rights['DIPLOMATIC']) {
			$this->redirectToHome();
		}

        $db = Database::get();

        $sql = "UPDATE %%DIPLO%% SET accept = 1 WHERE id = :id AND owner_2 = :allianceId;";
        $db->update($sql, array(
            ':allianceId'   => $this->allianceData['id'],
            ':id'           => HTTP::_GP('id', 0)
        ));

        $this->redirectTo('game.php?page=alliance&mode=admin&action=diplomacy');
	}

	protected function admindiplomacymacyDelete()
	{
		if (!$this->rights['DIPLOMATIC']) {
			$this->redirectToHome();
		}

        $db = Database::get();

        $sql = "DELETE FROM %%DIPLO%% WHERE id = :id AND (owner_1 = :allianceId OR owner_2 = :allianceId);";
        $db->update($sql, array(
            ':allianceId'   => $this->allianceData['id'],
            ':id'           => HTTP::_GP('id', 0)
        ));

        $this->redirectTo('game.php?page=alliance&mode=admin&action=diplomacy');
	}

	protected function admindiplomacymacyCreate()
	{
		if (!$this->rights['DIPLOMATIC']) {
			$this->redirectToHome();
		}

        $db = Database::get();
		
		$this->initTemplate();
		$this->setWindow('popup');
		
		$diploMode	= HTTP::_GP('diploMode', 0);

        $sql = "SELECT ally_tag,ally_name,id FROM %%ALLIANCE%% WHERE id != :allianceId ORDER BY ally_tag ASC;";
        $diploAlly = $db->select($sql, array(
            ':allianceId'   => $this->user->ally_id
        ));

        $AllyList = array();
		$IdList = array();
		foreach ($diploAlly as $i)
		{
			$IdList[] = $i['id'];
			$AllyList[] = $i['ally_name'];
		}
		$this->assign(array(
			'diploMode'	=> $diploMode,
			'AllyList'	=> $AllyList,
			'IdList'	=> $IdList,
		));
		
		$this->display('page.alliance.admin.diplomacy.create');
	}

	protected function admindiplomacymacyCreateProcessor()
	{
		if (!$this->rights['DIPLOMATIC']) {
			$this->redirectToHome();
		}

        $db = Database::get();

		$id	= HTTP::_GP('ally_id', '', UTF8_SUPPORT);

        $sql = "SELECT id, ally_name, ally_owner, ally_tag, (SELECT level FROM %%DIPLO%% WHERE (owner_1 = :id AND owner_2 = :allianceId) OR (owner_2 = :id AND owner_1 = :allianceId)) as diplo FROM %%ALLIANCE%% WHERE ally_universe = :universe AND id = :id;";
        $targetAlliance = $db->selectSingle($sql, array(
            ':allianceId'   => $this->user->ally_id,
            ':id'           => $id,
            ':universe'     => Universe::current()
        ));

        if(empty($targetAlliance)) {
			$this->sendJSON(array(
				'error'		=> true,
				'message'	=> sprintf($this->lang['al_diplo_no_alliance'], $targetAlliance['id']),
			));	
		}
		
		if(!empty($targetAlliance['diplo'])) {
			$this->sendJSON(array(
				'error'		=> true,
				'message'	=> sprintf($this->lang['al_diplo_exists'], $targetAlliance['ally_name']),
			));	
		}
		if($targetAlliance['id'] == $this->allianceData['id']) {
			$this->sendJSON(array(
				'error'		=> true,
				'message'	=> $this->lang['al_diplo_same_alliance'],
			));	
		}
		
		$this->setWindow('ajax');
		
		$level	= HTTP::_GP('level', 0);
		$text	= HTTP::_GP('text', '', true);
		
		if($level == 5)
		{
			PlayerUtil::sendMessage($targetAlliance['ally_owner'], $this->user->id, TIMESTAMP, 1, $this->lang['al_circular_alliance'].$this->allianceData['ally_tag'], $this->lang['al_diplo_war'], sprintf($this->lang['al_diplo_war_mes'], "[".$this->allianceData['ally_tag']."] ".$this->allianceData['ally_name'], "[".$targetAlliance['ally_tag']."] ".$targetAlliance['ally_name'], $this->lang['al_diplo_level'][$level], $text));
		}
		else
		{
			PlayerUtil::sendMessage($targetAlliance['ally_owner'], $this->user->id, TIMESTAMP, 1, $this->lang['al_circular_alliance'].$this->allianceData['ally_tag'], $this->lang['al_diplo_ask'], sprintf($this->lang['al_diplo_ask_mes'], $this->lang['al_diplo_level'][$level], "[".$this->allianceData['ally_tag']."] ".$this->allianceData['ally_name'], "[".$targetAlliance['ally_tag']."] ".$targetAlliance['ally_name'], $text));
		}
		
		$sql = "INSERT INTO %%DIPLO%% SET owner_1 = :allianceId, owner_2 = :allianceTargetID, level	= :level, accept = 0, accept_text = :text, universe	= :universe";
        $db->insert($sql, array(
            ':allianceId'   => $this->user->ally_id,
            ':allianceTargetID'  => $targetAlliance['id'],
            ':level'             => $level,
            ':text'           => $text,
            ':universe'     => Universe::current()
        ));

        $this->sendJSON(array(
			'error'		=> false,
			'message'	=> $this->lang['al_diplo_create_done'],
		));
	}
}