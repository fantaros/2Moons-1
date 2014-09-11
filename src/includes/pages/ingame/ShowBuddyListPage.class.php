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
 * @info $Id: ShowBuddyListPage.class.php 2776 2013-08-05 21:30:40Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowBuddyListPage extends AbstractGamePage
{
	public static $requireModule = MODULE_BUDDYLIST;

    function request()
	{

		$this->initTemplate();
		$this->setWindow('popup');
		
		$id	= HTTP::_GP('id', 0);
		
		if($id == $this->user->id)
		{
			$this->printMessage($this->lang['bu_cannot_request_yourself']);
		}
		
		$db = Database::get();

        $sql = "SELECT COUNT(*) as count FROM %%BUDDY%% WHERE (sender = :userID AND owner = :friendID) OR (owner = :userID AND sender = :friendID);";
        $exists = $db->selectSingle($sql, array(
            ':userID'	=> $this->user->id,
            ':friendID' => $id
        ), 'count');

		if($exists != 0)
		{
			$this->printMessage($this->lang['bu_request_exists']);
		}
		
		$sql = "SELECT username, galaxy, system, planet FROM %%USERS%% WHERE id = :friendID;";
        $userData = $db->selectSingle($sql, array(
            ':friendID'  => $id
        ));

		$this->assign(array(
			'username'	=> $userData['username'],
			'galaxy'	=> $userData['galaxy'],
			'system'	=> $userData['system'],
			'planet'	=> $userData['planet'],
			'id'		=> $id,
		));
		
		$this->display('page.buddyList.request');
	}
	
	function send()
	{
		$this->initTemplate();
		$this->setWindow('popup');
		$this->tplObj->execscript('window.setTimeout(parent.$.fancybox.close, 2000);');
		
		$id		= HTTP::_GP('id', 0);
		$text	= HTTP::_GP('text', '', UTF8_SUPPORT);

		if($id == $this->user->id)
		{
			$this->printMessage($this->lang['bu_cannot_request_yourself']);
		}

        $db = Database::get();

        $sql = "SELECT COUNT(*) as count FROM %%BUDDY%% WHERE (sender = :userID AND owner = :friendID) OR (owner = :userID AND sender = :friendID);";
        $exists = $db->selectSingle($sql, array(
            ':userID'	=> $this->user->id,
            ':friendID'  => $id
        ), 'count');

        if($exists != 0)
		{
			$this->printMessage($this->lang['bu_request_exists']);
		}

        $sql = "INSERT INTO %%BUDDY%% SET sender = :userID,	owner = :friendID, universe = :universe;";
        $db->insert($sql, array(
            ':userID'	=> $this->user->id,
            ':friendID'  => $id,
            ':universe' => Universe::current()
        ));

        $buddyID	= $db->lastInsertId();

		$sql = "INSERT INTO %%BUDDY_REQUEST%% SET id = :buddyID, text = :text;";
        $db->insert($sql, array(
            ':buddyID'  => $buddyID,
            ':text' => $text
        ));

        $sql = "SELECT username FROM %%USERS%% WHERE id = :friendID;";
        $username = $db->selectSingle($sql, array(
            ':friendID'  => $id,
        ), 'username');

        PlayerUtil::sendMessage($id, $this->user->id, TIMESTAMP, 4, $this->user->username, $this->lang['bu_new_request_title'], sprintf($this->lang['bu_new_request_body'], $username, $this->user->username));

		$this->printMessage($this->lang['bu_request_send']);
	}
	
	function delete()
	{

		$id	= HTTP::_GP('id', 0);
		$db = Database::get();

        $sql = "SELECT COUNT(*) as count FROM %%BUDDY%% WHERE id = :id AND (sender = :userID OR owner = :userID);";
        $isAllowed = $db->selectSingle($sql, array(
            ':id'  => $id,
            ':userID' => $this->user->id
        ), 'count');

		if($isAllowed)
		{
			$sql = "SELECT COUNT(*) as count FROM %%BUDDY_REQUEST%% WHERE :id;";
            $isRequest = $db->selectSingle($sql, array(
                ':id'  => $id
            ), 'count');
			
			if($isRequest)
			{
                $sql = "SELECT u.username, u.id FROM %%BUDDY%% b INNER JOIN %%USERS%% u ON u.id = IF(b.sender = :userID,b.owner,b.sender) WHERE b.id = :id;";
                $requestData = $db->selectSingle($sql, array(
                    ':id'       => $id,
                    'userID'    => $this->user->id
                ));

                PlayerUtil::sendMessage($requestData['id'], $this->user->id, TIMESTAMP, 4, $this->user->username, $this->lang['bu_rejected_request_title'], sprintf($this->lang['bu_rejected_request_body'], $requestData['username'], $this->user->username));
			}

            $sql = "DELETE b.*, r.* FROM %%BUDDY%% b LEFT JOIN %%BUDDY_REQUEST%% r USING (id) WHERE b.id = :id;";
            $db->delete($sql, array(
                ':id'       => $id,
            ));
        }
		$this->redirectTo("game.php?page=buddyList");
	}
	
	function accept()
	{

		$id	= HTTP::_GP('id', 0);
		$db = Database::get();

        $sql = "DELETE FROM %%BUDDY_REQUEST%% WHERE id = :id;";
        $db->delete($sql, array(
            ':id'       => $id
        ));

        $sql = "SELECT sender, u.username FROM %%BUDDY%% b INNER JOIN %%USERS%% u ON sender = u.id WHERE b.id = :id;";
        $sender = $db->selectSingle($sql, array(
            ':id'       => $id
        ));

		PlayerUtil::sendMessage($sender['sender'], $this->user->id, TIMESTAMP, 4, $this->user->username, $this->lang['bu_accepted_request_title'], sprintf($this->lang['bu_accepted_request_body'], $sender['username'], $this->user->username));
		
		$this->redirectTo("game.php?page=buddyList");
	}
	
	function show()
	{

		$db = Database::get();
        $sql = "SELECT a.sender, a.id as buddyid, b.id, b.username, b.onlinetime, b.galaxy, b.system, b.planet, b.ally_id, c.ally_name, d.text
		FROM (%%BUDDY%% as a, %%USERS%% as b) LEFT JOIN %%ALLIANCE%% as c ON c.id = b.ally_id LEFT JOIN %%BUDDY_REQUEST%% as d ON a.id = d.id
		WHERE (a.sender = ".$this->user->id." AND a.owner = b.id) OR (a.owner = :userID AND a.sender = b.id);";
        $BuddyListResult = $db->select($sql, array(
            'userID'    => $this->user->id
        ));

        $myRequestList		= array();
		$otherRequestList	= array();
		$myBuddyList		= array();		
				
		foreach($BuddyListResult as $BuddyList)
		{
			if(isset($BuddyList['text']))
			{
				if($BuddyList['sender'] == $this->user->id)
					$myRequestList[$BuddyList['buddyid']]		= $BuddyList;
				else
					$otherRequestList[$BuddyList['buddyid']]	= $BuddyList;
			}
			else
			{
				$BuddyList['onlinetime']			= floor((TIMESTAMP - $BuddyList['onlinetime']) / 60);
				$myBuddyList[$BuddyList['buddyid']]	= $BuddyList;
			}
		}
		
		$this->assign(array(
			'myBuddyList'		=> $myBuddyList,
			'myRequestList'			=> $myRequestList,
			'otherRequestList'	=> $otherRequestList,
		));
		
		$this->display('page.buddyList.default');
	}
}