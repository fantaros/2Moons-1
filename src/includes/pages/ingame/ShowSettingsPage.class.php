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
 * @info $Id: ShowSettingsPage.class.php 2801 2013-10-05 23:55:41Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowSettingsPage extends AbstractGamePage
{

    public function show()
	{
		if($this->user->urlaubs_modus == 1)
		{
			$this->assign(array(
				'vacationUntil'			=> _date($this->lang['php_tdformat'], $this->user->urlaubs_until, $this->user->timezone),
				'delete'				=> $this->user->db_deaktjava,
				'canVacationDisbaled'	=> $this->user->urlaubs_until < TIMESTAMP,
			));
			
			$this->display('page.settings.vacation');
		}
		else
		{
			$this->assign(array(
				'Selectors'			=> array(
					'timezones' => get_timezone_selector(), 
					'Sort' => array(
						0 => $this->lang['op_sort_normal'],
						1 => $this->lang['op_sort_koords'],
						2 => $this->lang['op_sort_abc']),
					'SortUpDown' => array(
						0 => $this->lang['op_sort_up'],
						1 => $this->lang['op_sort_down']
					), 
					'Skins' => Theme::getAvailableSkins(),
					'lang' => $this->lang->getAvailableLanguages(false)
					),
				'adminProtection'	=> $this->user->authattack,
				'userAuthlevel'		=> $this->user->authlevel,
				'changeNickTime'	=> ($this->user->uctime + USERNAME_CHANGETIME) - TIMESTAMP,
				'username'			=> $this->user->username,
				'email'				=> $this->user->email,
				'permaEmail'		=> $this->user->email_2,
				'userLang'			=> $this->user->lang,
				'theme'				=> substr($this->user->dpath, 13, -1),
				'planetSort'		=> $this->user->planet_sort,
				'planetOrder'		=> $this->user->planet_sort_order,
				'spycount'			=> $this->user->spio_anz,
				'fleetActions'		=> $this->user->settings_fleetactions,
				'timezone'			=> $this->user->timezone,
				'delete'			=> $this->user->db_deaktjava,
				'queueMessages'		=> $this->user->hof,
				'spyMessagesMode'	=> $this->user->spyMessagesMode,
				'galaxySpy' 		=> $this->user->settings_esp,
				'galaxyBuddyList' 	=> $this->user->settings_bud,
				'galaxyMissle' 		=> $this->user->settings_mis,
				'galaxyMessage' 	=> $this->user->settings_wri,
				'blockPM' 			=> $this->user->settings_blockPM,
				'userid'		 	=> $this->user->id,
				'ref_active'		=> Config::get()->ref_active,
			));
			
			$this->display('page.settings.default');
		}
	}
	
	private function CheckVMode()
	{

		if(!empty($this->user->b_tech) || !empty($PLANET['b_building']) || !empty($PLANET['b_hangar']))
			return false;

		$db = Database::get();

		$sql = "SELECT COUNT(*) as state FROM %%FLEETS%% WHERE `fleet_owner` = :userID;";
		$fleets = $db->selectSingle($sql, array(
			':userID'	=> $this->user->id
		), 'state');

		if($fleets != 0)
			return false;

		$sql = "SELECT * FROM %%PLANETS%% WHERE id_owner = :userID AND id != :planetID AND destroyed = 0;";
		$query = $db->select($sql, array(
			':userID'	=> $this->user->id,
			':planetID'	=> $PLANET['id']
		));

		foreach($query as $CPLANET)
		{
			list($USER, $CPLANET)	= $this->ecoObj->CalcResource($USER, $CPLANET, true);
		
			if(!empty($CPLANET['b_building']) || !empty($CPLANET['b_hangar']))
				return false;
			
			unset($CPLANET);
		}

		return true;
	}
	
	public function send()
	{
		if($this->user->urlaubs_modus == 1) {
			$this->sendVacation();
		} else {
			$this->sendDefault();
		}
	}
	
	private function sendVacation() 
	{

		$delete		= HTTP::_GP('delete', 0);
		$vacation	= HTTP::_GP('vacation', 0);
		
		$db = Database::get();
		
		if($vacation == 1 && $this->user->urlaubs_until <= TIMESTAMP) {
			$sql = "UPDATE %%USERS%% SET
						urlaubs_modus = '0',
						urlaubs_until = '0'
						WHERE id = :userID;";
			$db->update($sql, array(
				':userID'	=> $this->user->id
			));

			$sql = "UPDATE %%PLANETS%% SET
						last_update = :timestamp,
						energy_used = '10',
						energy = '10',
						metal_mine_porcent = '10',
						crystal_mine_porcent = '10',
						deuterium_sintetizer_porcent = '10',
						solar_plant_porcent = '10',
						fusion_plant_porcent = '10',
						solar_satelit_porcent = '10'
						WHERE id_owner = :userID;";
			$db->update($sql, array(
				':userID'		=> $this->user->id,
				':timestamp'	=> TIMESTAMP
			));
		}
		
		if($delete == 1) {
			$sql	= "UPDATE %%USERS%% SET db_deaktjava = :timestamp WHERE id = :userID;";
			$db->update($sql, array(
				':userID'		=> $this->user->id,
				':timestamp'	=> TIMESTAMP
			));
		} else {
			$sql	= "UPDATE %%USERS%% SET db_deaktjava = 0 WHERE id = :userID;";
			$db->update($sql, array(
				':userID'	=> $this->user->id,
			));
		}
		
		$this->printMessage($this->lang['op_options_changed'], array(array(
			'label'	=> $this->lang['sys_forward'],
			'url'	=> 'game.php?page=settings'
		)));
	}
	
	private function sendDefault()
	{

		$adminprotection	= HTTP::_GP('adminprotection', 0);
		
		$username			= HTTP::_GP('username', $this->user->username, UTF8_SUPPORT);
		$password			= HTTP::_GP('password', '');
		
		$newpassword		= HTTP::_GP('newpassword', '');
		$newpassword2		= HTTP::_GP('newpassword2', '');
		
		$email				= HTTP::_GP('email', $this->user->email);
		
		$timezone			= HTTP::_GP('timezone', '');	
		$language			= HTTP::_GP('language', '');	
		
		$planetSort			= HTTP::_GP('planetSort', 0);	
		$planetOrder		= HTTP::_GP('planetOrder', 0);
				
		$theme				= HTTP::_GP('theme', $THEME->getThemeName());	
	
		$queueMessages		= HTTP::_GP('queueMessages', 0);	
		$spyMessagesMode	= HTTP::_GP('spyMessagesMode', 0);

		$spycount			= HTTP::_GP('spycount', 1.0);	
		$fleetactions		= HTTP::_GP('fleetactions', 5);	
		
		$galaxySpy			= HTTP::_GP('galaxySpy', 0);	
		$galaxyMessage		= HTTP::_GP('galaxyMessage', 0);	
		$galaxyBuddyList	= HTTP::_GP('galaxyBuddyList', 0);	
		$galaxyMissle		= HTTP::_GP('galaxyMissle', 0);
		$blockPM			= HTTP::_GP('blockPM', 0);
		
		$vacation			= HTTP::_GP('vacation', 0);	
		$delete				= HTTP::_GP('delete', 0);
		
		// Verify
		
		$adminprotection	= ($adminprotection == 1 && $this->user->authlevel != AUTH_USR) ? $this->user->authlevel : 0;
		
		$spycount			= min(max(round($spycount), 1), 4294967295);
		$fleetactions		= min(max($fleetactions, 1), 99);
		
		$language			= array_key_exists($language, $this->lang->getAvailableLanguages(false)) ? $language : $this->lang->getLanguage();
		$theme				= array_key_exists($theme, Theme::getAvailableSkins()) ? $theme : $THEME->getThemeName();
		
		$db = Database::get();
		
		if (!empty($username) && $this->user->username != $username)
		{
			if (PlayerUtil::isNameValid($username))
			{
				$this->printMessage($this->lang['op_user_name_no_alphanumeric'], array(array(
					'label'	=> $this->lang['sys_back'],
					'url'	=> 'game.php?page=settings'
				)));
			}
			elseif($this->user->uctime >= TIMESTAMP - USERNAME_CHANGETIME)
			{
				$this->printMessage($this->lang['op_change_name_pro_week'], array(array(
					'label'	=> $this->lang['sys_back'],
					'url'	=> 'game.php?page=settings'
				)));
			}
			else
			{
				$sql = "SELECT
					(SELECT COUNT(*) FROM %%USERS%% WHERE universe = :universe AND username = :username) +
					(SELECT COUNT(*) FROM %%USERS_VALID%% WHERE universe = :universe AND username = :username)
				AS count";
				$Count = $db->selectSingle($sql, array(
					':universe'	=> Universe::current(),
					':username'	=> $username
				), 'count');

				if (!empty($Count)) {
					$this->printMessage(sprintf($this->lang['op_change_name_exist'], $username), array(array(
						'label'	=> $this->lang['sys_back'],
						'url'	=> 'game.php?page=settings'
					)));
				} else {
					$sql = "UPDATE %%USERS%% SET username = :username, uctime = :timestampt WHERE id = :userID;";
					$db->update($sql, array(
						':username'	=> $username,
						':userID'	=> $this->user->id,
						':timestamp'=> TIMESTAMP
					));

					Session::get()->delete();
				}
			}
		}
		
		if (!empty($newpassword) && PlayerUtil::cryptPassword($password) == $USER["password"] && $newpassword == $newpassword2)
		{
			$newpass 	 = PlayerUtil::cryptPassword($newpassword);
			$sql = "UPDATE %%USERS%% SET password = :newpass WHERE id = :userID;";
			$db->update($sql, array(
				':newpass'	=> $newpass,
				':userID'	=> $this->user->id
			));
			Session::get()->delete();
		}

		if (!empty($email) && $email != $this->user->email)
		{
			if(PlayerUtil::cryptPassword($password) != $this->user->password)
			{
				$this->printMessage($this->lang['op_need_pass_mail'], array(array(
					'label'	=> $this->lang['sys_back'],
					'url'	=> 'game.php?page=settings'
				)));
			}
			elseif(!ValidateAddress($email))
			{
				$this->printMessage($this->lang['op_not_vaild_mail'], array(array(
					'label'	=> $this->lang['sys_back'],
					'url'	=> 'game.php?page=settings'
				)));
			}
			else
			{
				$sql = "SELECT
							(SELECT COUNT(*) FROM %%USERS%% WHERE id != :userID AND universe = :universe AND (email = :email OR email_2 = :email)) +
							(SELECT COUNT(*) FROM %%USERS_VALID%% WHERE universe = :universe AND email = :email)
						as COUNT";
				$Count = $db->selectSingle($sql, array(
					':universe'	=> Universe::current(),
					':userID'	=> $this->user->id,
					':email'	=> $email
				), 'count');

				if (!empty($Count)) {
					$this->printMessage(sprintf($this->lang['op_change_mail_exist'], $email), array(array(
						'label'	=> $this->lang['sys_back'],
						'url'	=> 'game.php?page=settings'
					)));
				} else {
					$sql	= "UPDATE %%USERS%% SET email = :email, setmail = :time WHERE id = :userID;";
					$db->update($sql, array(
						':email'	=> $email,
						':time'		=> (TIMESTAMP + 604800),
						':userID'	=> $this->user->id
					));
				}
			}
		}		
			
		
		if ($vacation == 1)
		{
			if(!$this->CheckVMode())
			{
				$this->printMessage($this->lang['op_cant_activate_vacation_mode'], array(array(
					'label'	=> $this->lang['sys_back'],
					'url'	=> 'game.php?page=settings'
				)));
			}
			else
			{
				$sql = "UPDATE %%USERS%% SET urlaubs_modus = '1', urlaubs_until = :time WHERE id = :userID";
				$db->update($sql, array(
					':userID'	=> $this->user->id,
					':time'		=> (TIMESTAMP + Config::get()->vmode_min_time),
				));

				$sql = "UPDATE %%PLANETS%% SET energy_used = '0', energy = '0', metal_mine_porcent = '0', crystal_mine_porcent = '0', deuterium_sintetizer_porcent = '0', solar_plant_porcent = '0', fusion_plant_porcent = '0', solar_satelit_porcent = '0', metal_perhour = '0', crystal_perhour = '0', deuterium_perhour = '0' WHERE id_owner = :userID;";
				$db->update($sql, array(
					':userID'	=> $this->user->id,
				));
			}
		}

		if($delete == 1) {
			$sql	= "UPDATE %%USERS%% SET db_deaktjava = :timestamp WHERE id = :userID;";
			$db->update($sql, array(
				':userID'	=> $this->user->id,
				':timestamp'	=> TIMESTAMP
			));
		} else {
			$sql	= "UPDATE %%USERS%% SET db_deaktjava = 0 WHERE id = :userID;";
			$db->update($sql, array(
				':userID'	=> $this->user->id,
			));
		}

		$sql =  "UPDATE %%USERS%% SET
		dpath					= :theme,
		timezone				= :timezone,
		planet_sort				= :planetSort,
		planet_sort_order		= :planetOrder,
		spio_anz				= :spyCount,
		settings_fleetactions	= :fleetActions,
		settings_esp			= :galaxySpy,
		settings_wri			= :galaxyMessage,
		settings_bud			= :galaxyBuddyList,
		settings_mis			= :galaxyMissle,
		settings_blockPM		= :blockPM,
		authattack				= :adminProtection,
		lang					= :language,
		hof						= :queueMessages,
		spyMessagesMode			= :spyMessagesMode
		WHERE id = :userID;";
		$db->update($sql, array(
			':theme'			=> $theme,
			':timezone'			=> $timezone,
			':planetSort'		=> $planetSort,
			':planetOrder'		=> $planetOrder,
			':spyCount'			=> $spycount,
			':fleetActions'		=> $fleetactions,
			':galaxySpy'		=> $galaxySpy,
			':galaxyMessage'	=> $galaxyMessage,
			':galaxyBuddyList'	=> $galaxyBuddyList,
			':galaxyMissle'		=> $galaxyMissle,
			':blockPM'			=> $blockPM,
			':adminProtection'	=> $adminprotection,
			':language'			=> $language,
			':queueMessages'	=> $queueMessages,
			':spyMessagesMode'	=> $spyMessagesMode,
			':userID'			=> $this->user->id
		));
		
		$this->printMessage($this->lang['op_options_changed'], array(array(
			'label'	=> $this->lang['sys_forward'],
			'url'	=> 'game.php?page=settings'
		)));
	}
}