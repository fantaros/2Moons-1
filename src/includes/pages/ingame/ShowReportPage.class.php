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
 * @info $Id: ShowReportPage.class.php 2776 2013-08-05 21:30:40Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowReportPage extends AbstractGamePage
{
	protected $disableEcoSystem = true;

    public function __construct()
    {
        parent::__construct();
        $this->lang->includeData(array('FLEET'));
        $this->setWindow('popup');
    }

    private function BCWrapperPreRev2321($combatReport)
	{
		if(isset($combatReport['moon']['desfail']))
		{
			$combatReport['moon']	= array(
				'moonName'				=> $combatReport['moon']['name'],
				'moonChance'			=> $combatReport['moon']['chance'],
				'moonDestroySuccess'	=> !$combatReport['moon']['desfail'],
				'fleetDestroyChance'	=> $combatReport['moon']['chance2'],
				'fleetDestroySuccess'	=> !$combatReport['moon']['fleetfail']
			);			
		}
		elseif(isset($combatReport['moon'][0]))
		{
			$combatReport['moon']	= array(
				'moonName'				=> $combatReport['moon'][1],
				'moonChance'			=> $combatReport['moon'][0],
				'moonDestroySuccess'	=> !$combatReport['moon'][2],
				'fleetDestroyChance'	=> $combatReport['moon'][3],
				'fleetDestroySuccess'	=> !$combatReport['moon'][4]
			);			
		}
		
		if(isset($combatReport['simu']))
		{
			$combatReport['additionalInfo'] = $combatReport['simu'];
		}
		
		if(isset($combatReport['debris'][0]))
		{
            $combatReport['debris'] = array(
                901	=> $combatReport['debris'][0],
                902	=> $combatReport['debris'][1]
            );
		}
		
		if (!empty($combatReport['steal']['metal']))
		{
			$combatReport['steal'] = array(
				901	=> $combatReport['steal']['metal'],
				902	=> $combatReport['steal']['crystal'],
				903	=> $combatReport['steal']['deuterium']
			);
		}
		
		return $combatReport;
	}
	
	function battlehall() 
	{
		$db         = Database::get();
		$reportId   = HTTP::_GP('report', '');

		$sql = "SELECT 
			report, time,
			(
				SELECT
				GROUP_CONCAT(username SEPARATOR ' & ') as attacker
				FROM %%USERS%%
				WHERE id IN (SELECT uid FROM %%TOPKB_USERS%% WHERE %%TOPKB_USERS%%.rid = %%RW%%.rid AND role = 1)
			) as attacker,
			(
				SELECT
				GROUP_CONCAT(username SEPARATOR ' & ') as defender
				FROM %%USERS%%
				WHERE id IN (SELECT uid FROM %%TOPKB_USERS%% WHERE %%TOPKB_USERS%%.rid = %%RW%%.rid AND role = 2)
			) as defender
			FROM %%RW%%
			WHERE rid = :reportID;";

		$reportData = $db->selectSingle($sql, array(
			':reportID'	=> $reportId
		));

		$Info		= array($reportData["attacker"], $reportData["defender"]);
		
		if(!isset($reportData)) {
			$this->printMessage($this->lang['sys_report_not_found']);
		}
		
		$combatReport			= unserialize($reportData['report']);
		$combatReport['time']	= _date($this->lang['php_tdformat'], $combatReport['time'], $this->user->timezone);
		$combatReport			= $this->BCWrapperPreRev2321($combatReport);
		
		$this->assign(array(
			'Report'	=> $combatReport,
			'Info'		=> $Info,
			'pageTitle'	=> $this->lang['lm_topkb']
		));
		
		$this->display('shared.mission.report');
	}
	
	function show() 
	{
		$db         = Database::get();
        $reportId   = HTTP::_GP('report', '');

		$sql = "SELECT report,attacker,defender FROM %%RW%% WHERE rid = :reportID;";
		$reportData = $db->selectSingle($sql, array(
			':reportID'	=> $reportId
		));

		if(empty($reportData)) {
			$this->printMessage($this->lang['sys_report_not_found']);
		}
		
		// empty is BC for pre r2484
		$isAttacker = empty($reportData['attacker']) || in_array($this->user->id, explode(",", $reportData['attacker']));
		$isDefender = empty($reportData['defender']) || in_array($this->user->id, explode(",", $reportData['defender']));
		
		if(empty($reportData) || (!$isAttacker && !$isDefender))
        {
			$this->printMessage($this->lang['sys_report_not_found']);
		}

		$combatReport			= unserialize($reportData['report']);
		if($isAttacker && !$isDefender && $combatReport['result'] == 'r' && count($combatReport['rounds']) <= 2)
        {
			$this->printMessage($this->lang['sys_report_lost_contact']);
		}
		
		$combatReport['time']	= _date($this->lang['php_tdformat'], $combatReport['time'], $this->user->timezone);
		$combatReport			= $this->BCWrapperPreRev2321($combatReport);
		
		$this->assign(array(
			'Report'	=> $combatReport,
			'pageTitle'	=> $this->lang['sys_mess_attack_report']
		));
		
		$this->display('shared.mission.report');
	}
}