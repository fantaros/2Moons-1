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
 * @info $Id: ShowFleetAjaxPage.class.php 2796 2013-09-29 23:10:14Z slaver7 $
 * @link http://2moons.cc/
 */


class ShowFleetAjaxPage extends AbstractGamePage
{
	public $returnData	= array();

    public static $requireModule = 0;

	function __construct() 
	{
		parent::__construct();
		$this->setWindow('ajax');
	}
	
	private function sendData($Code, $Message) {
		$this->returnData['code']	= $Code;
		$this->returnData['mess']	= $Message;
		$this->sendJSON($this->returnData);
	}
	
	public function show()
	{
		$planetID 		= HTTP::_GP('planetID', 0);
		$targetMission	= HTTP::_GP('mission', 0);
		
		$activeSlots	= FleetUtil::getUsedSlots($this->user->id);
		$maxSlots		= FleetUtil::GetMaxFleetSlots($this->user);
		
		$this->returnData['slots']		= $activeSlots;
		
		if ($this->user->onVacation())
        {
			$this->sendData(620, $this->lang['fa_vacation_mode_current']);
		}
		
		if (empty($planetID))
        {
			$this->sendData(601, $this->lang['fa_planet_not_exist']);
		}
		
		if ($maxSlots <= $activeSlots)
        {
			$this->sendData(612, $this->lang['fa_no_more_slots']);
		}

		$fleetArray = array();

        $db = Database::get();

        switch($targetMission)
		{
			case 6:
				if(!$this->user->can(MODULE_MISSION_SPY))
                {
					$this->sendData(699, $this->lang['sys_module_inactive']);
				}

                $count  = $this->user->spio_anz;
				
				if(empty($ships))
                {
					$this->sendData(611, $this->lang['fa_no_spios']);
				}

                $spyElements	= array_reverse(Vars::getElements(Vars::CLASS_FLEET, Vars::FLAG_SPY), true);

                $fleetArray		= array();
                foreach($spyElements as $elementId => $elementObj)
                {
                    $fleetArray[$elementId] = min($count, $PLANET[$elementObj->name]);

                    $this->returnData['ships'][$elementId]	= $PLANET[$elementObj->name] - $fleetArray[$elementId];

                    $count  -= $fleetArray[$elementId];

                    if($count == 0) break;

                }
			break;
			case 8:
				if(!$this->user->can(MODULE_MISSION_RECYCLE))
                {
					$this->sendData(699, $this->lang['sys_module_inactive']);
				}

                $sql = "SELECT (der_metal + der_crystal) as sum FROM %%PLANETS%% WHERE id = :planetID;";
                $totalDebris = $db->selectSingle($sql, array(
                    ':planetID' => $planetID
                ), 'sum');

                $collectElements	= array_reverse(Vars::getElements(Vars::CLASS_FLEET, Vars::FLAG_COLLECT), true);
				$fleetArray		    = array();
				
				foreach($collectElements as $elementId => $elementObj)
				{
					$shipsNeed 		= min(ceil($totalDebris / $elementObj->capacity), $PLANET[$elementObj->name]);
					$totalDebris	-= ($shipsNeed * $elementObj->capacity);
					
					$fleetArray[$elementId]	= $shipsNeed;
					$this->returnData['ships'][$elementId]	= $PLANET[$elementObj->name] - $shipsNeed;
					
					if($totalDebris <= 0)
					{
						break;
					}
				}
				
				if(empty($fleetArray))
				{
					$this->sendData(611, $this->lang['fa_no_recyclers']);
				}
				break;
			default:
				$this->sendData(610, $this->lang['fa_not_enough_probes']);
			break;
		}
		
		$fleetArray	= array_filter($fleetArray);
		
		if(empty($fleetArray))
        {
			$this->sendData(610, $this->lang['fa_not_enough_probes']);
		}


        if(!in_array($planetID, array_keys($this->user->getPlanetList())))
        {
            $targetPlanet   = new Planet($planetID);
            if (!$targetPlanet)
            {
                $this->sendData(601, $this->lang['fa_planet_not_exist']);
            }

            $targetUser     = new User($this->planet->id_owner);
        }
        else
        {
            $targetPlanet   = $this->planet;
            $targetUser     = $this->user;
        }
		
		if($targetMission == 6)
		{
            if ($this->user->id == $targetUser->id)
            {
                $this->sendData(618, $this->lang['fa_not_spy_yourself']);
            }

			if(Config::get()->adm_attack == 1 && $targetUser->authattack > $this->user->authlevel)
            {
				$this->sendData(619, $this->lang['fa_action_not_allowed']);
			}
			
			if ($targetUser->onVacation())
            {
				$this->sendData(605, $this->lang['fa_vacation_mode']);
			}

			$sql	= 'SELECT total_points
			FROM %%STATPOINTS%%
			WHERE id_owner = :userId AND stat_type = :statType';

			$userStats = Database::get()->selectSingle($sql, array(
				':userId'	=> $this->user->id,
				':statType'	=> 1
			));

			$IsNoobProtec	= CheckNoobProtec($USER, $targetData, $targetData);
			
			if ($IsNoobProtec['NoobPlayer']) {
				$this->sendData(603, $this->lang['fa_week_player']);
			}
			
			if ($IsNoobProtec['StrongPlayer']) {
				$this->sendData(604, $this->lang['fa_strong_player']);
			}
		}
		
		$SpeedFactor    	= FleetUtil::GetGameSpeedFactor();
		$Distance    		= FleetUtil::GetTargetDistance($this->planet, $targetPlanet);
		$SpeedAllMin		= FleetUtil::GetFleetMaxSpeed($fleetArray, $this->user);
		$Duration			= FleetUtil::GetMissionDuration(10, $SpeedAllMin, $Distance, $SpeedFactor, $this->user);
		$consumption		= FleetUtil::GetFleetConsumption($fleetArray, $Duration, $Distance, $this->user, $SpeedFactor);

		$$this->planet->deuterium   	-= $consumption;

		if($$this->planet->deuterium < 0) {
			$this->sendData(613, $this->lang['fa_not_enough_fuel']);
		}
		
		if($consumption > FleetUtil::GetFleetRoom($fleetArray)) {
			$this->sendData(613, $this->lang['fa_no_fleetroom']);
		}
		
		if(connection_aborted())
			exit;
			
		$this->returnData['slots']++;

        $fleetResource	= ArrayUtil::combineArrayWithSingleElement(
            array_keys(Vars::getElements(Vars::CLASS_RESOURCE, Vars::FLAG_TRANSPORT))
        , 0);

		$fleetStartTime		= $Duration + TIMESTAMP;
		$fleetStayTime		= $fleetStartTime;
		$fleetEndTime		= $fleetStayTime + $Duration;
		
		$shipID				= array_keys($fleetArray);
		
		FleetUtil::sendFleet($fleetArray, $targetMission, $this->user->id, $this->planet->id, $this->planet->galaxy,
			$this->planet->system, $this->planet->planet, $this->planet->planet_type, $targetData['id_owner'], $planetID,
			$targetData['galaxy'], $targetData['system'], $targetData['planet'], $targetData['planet_type'],
			$fleetResource, $fleetStartTime, $fleetStayTime, $fleetEndTime);

		$this->sendData(600, sprintf('%s %s %s %s [%s:%s:%s]',
            $this->lang['fa_sending'],
            array_sum($fleetArray),
            $this->lang['tech.'.$shipID[0]],
            $this->lang['gl_to'],
            $targetData['galaxy'],
            $targetData['system'],
            $targetData['planet']
        ));
	}
}