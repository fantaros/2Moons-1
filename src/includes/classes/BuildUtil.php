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
 * @info $Id: BuildUtil.php 2803 2013-10-06 22:23:27Z slaver7 $
 * @link http://2moons.cc/
 */

class BuildUtil
{
	public static $bonusList = NULL;

	public static function getBonusList()
	{
		if(is_null(self::$bonusList))
		{
			self::$bonusList = array(
				'Attack',
				'Defensive',
				'Shield',
				'BuildTime',
				'ResearchTime',
				'ShipTime',
				'DefensiveTime',
				'Resource',
				'ResourceStorage',
				'ShipStorage',
				'FlyTime',
				'FleetSlots',
				'Planets',
				'SpyPower',
				'Expedition',
				'GateCoolTime',
				'MoreFound',
			);

			foreach(array_keys(Vars::getElements(Vars::CLASS_RESOURCE, array(Vars::FLAG_RESOURCE_PLANET, Vars::FLAG_ENERGY))) as $elementId)
			{
				self::$bonusList[]  = 'Resource'.$elementId;
			}

		}
		return self::$bonusList;
	}

	public static function getRestPrice(User $user, Planet $planet, Element $elementObj, $costResources = NULL)
	{
		if(!isset($costResources))
		{
			$costResources	= self::getElementPrice($user, $planet, $elementObj);
		}
		
		$overflow	= array();
		
		foreach($costResources as $resourceElementId => $value)
		{
			$resourceElementObj	= Vars::getElement($resourceElementId);
			if($resourceElementObj->hasFlag(Vars::FLAG_RESOURCE_USER))
			{
				$available  = $user->{$resourceElementObj->name};
			}
			else
			{
				$available  = $planet->{$resourceElementObj->name};
			}

			$overflow[$resourceElementId] = max($value - $available, 0);
		}

		return $overflow;
	}
	
	public static function getElementPrice(Element $elementObj, $elementLevel = 1, $forDestroy = false)
	{
		$price	= array();
		foreach(Vars::getElements(Vars::CLASS_RESOURCE) as $resourceElementId => $resourceElementObj)
		{
			$value  = $elementObj->cost[$resourceElementId];
			
			if($elementObj->factor != 0 && $elementObj->factor != 1)
			{
				// elementLevel - 1, because the basic values are level 1, not level 0
				$value	*= pow($elementObj->factor, $elementLevel - 1);
			}
			
			if($elementObj->class === Vars::CLASS_FLEET || $elementObj->class === Vars::CLASS_DEFENSE || $elementObj->class === Vars::CLASS_MISSILE)
			{
				$value	*= $elementLevel;
			}
			
			if($forDestroy === true)
			{
				$value	= round($value / 2);
			}

			$price[$resourceElementId]	= $value;
		}
		
		return $price; 
	}
	
	public static function requirementsAvailable(User $user, Planet $planet, Element $elementObj)
	{
		if(!count($elementObj->requirements)) return true;

		foreach($elementObj->requirements as $requireElementId => $requireElementLevel)
		{
			$requireElementObj = Vars::getElement($requireElementId);

			if($requireElementObj->isUserResource())
			{
				if ($user->{$requireElementObj->name} < $requireElementLevel) return false;
			}
			else
			{
				if ($planet->{$requireElementObj->name} < $requireElementLevel) return false;
			}
		}

		return true;
	}
	
	public static function getBuildingTime(User $user, Planet $planet, Element $elementObj, $costResources = NULL, $forLevel = NULL, $forDestroy = false)
	{
		$config	= Config::get($user->universe);

		$time   = 0;

		if(!isset($costResources))
		{
			$costResources	= self::getElementPrice($elementObj, $forDestroy, $forLevel);
		}
		
		$elementCost	= 0;

		foreach(array_keys(Vars::getElements(Vars::CLASS_RESOURCE, Vars::FLAG_CALCULATE_BUILD_TIME)) as $resourceElementId)
		{
			$elementCost	+= $costResources[$resourceElementId];
		}
		switch($elementObj->class)
		{
			case Vars::CLASS_BUILDING:
				$time = $elementCost / ($config->game_speed * (1 + $planet->{Vars::getElement(14)->name}));
				$time *= pow(0.5, $planet->{Vars::getElement(15)->name});
				$time += PlayerUtil::getBonusValue($time, 'BuildTime', $user);
			break;
			case Vars::CLASS_FLEET:
				$time = $elementCost / $config->game_speed;
				$time *= 1 + $planet->{Vars::getElement(21)->name};
				$time *= pow(0.5, $planet->{Vars::getElement(15)->name});
				$time += PlayerUtil::getBonusValue($time, 'ShipTime', $user);
			break;
			case Vars::CLASS_DEFENSE:
				$time = $elementCost / $config->game_speed;
				$time *= 1 + $planet->{Vars::getElement(21)->name};
				$time *= pow(0.5, $planet->{Vars::getElement(15)->name});
				$time += PlayerUtil::getBonusValue($time, 'DefensiveTime', $user);
			break;
			case Vars::CLASS_TECH:
				if(!isset($user->techNetwork))
				{
					$user->techNetwork  = PlayerUtil::getLabLevelByNetwork($user, $planet);
				}

				$techLabLevel = 0;

				foreach($user->techNetwork as $planetTechLevel)
				{
					if(!isset($elementObj->requirements[31]) || $planetTechLevel >= $elementObj->requirements[31])
					{
						$techLabLevel += $planetTechLevel;
					}
				}

				$time = $elementCost / (1000 * (1 + $techLabLevel)) / ($config->game_speed / 2500);
				$time += PlayerUtil::getBonusValue($time, 'ResearchTime', $user);
			break;
			case Vars::CLASS_PERM_BONUS:
				$time = $elementCost / $config->game_speed;
			break;
		}
		
		if($forDestroy) {
			$time	= floor($time * 1300);
		} else {
			$time	= floor($time * 3600);
		}
		
		return max($time, $config->min_build_time);
	}
	
	public static function isElementBuyable(User $user, Planet $planet, Element $elementObj, $costResources = NULL, $forDestroy = false, $forLevel = NULL)
	{
		$rest	= self::getRestPrice($user, $planet, $elementObj, $costResources, $forDestroy, $forLevel);
		return count(array_filter($rest)) === 0;
	}
	
	public static function maxBuildableElements(User $user, Planet $planet, Element $elementObj, $costResources = NULL)
	{
		if(!isset($costResources))
		{
			$costResources	= self::getElementPrice($elementObj, 1);
		}

		$maxElement	= array();

		$costResources  = array_filter($costResources);

		foreach($costResources as $resourceElementId => $value)
		{
			$resourceElementObj	= Vars::getElement($resourceElementId);
			if($resourceElementObj->hasFlag(Vars::FLAG_RESOURCE_USER))
			{
				$maxElement[$resourceElementId]	= floor($user->{$resourceElementObj->name} / $value);
			}
			else
			{
				$maxElement[$resourceElementId]	= floor($planet->{$resourceElementObj->name} / $value);
			}
		}
		
		return min($maxElement);
	}
	
	public static function maxBuildableMissiles(User $user, Planet $planet, QueueManager $queueObj)
	{
		$currentMissiles	= 0;
		$missileElements	= Vars::getElements(Vars::CLASS_MISSILE);
		foreach($missileElements as $missileElementObj)
		{
			if($missileElementObj->hasFlag(Vars::FLAG_ATTACK_MISSILE))
			{
				$currentMissiles	+= $planet->{$missileElementObj->name} * 2;
			}
			else
			{
				$currentMissiles	+= $planet->{$missileElementObj->name};
			}
		}

		$queueObj->getTasksByElementId(array_keys($missileElements));

		$queueData	  = $queueObj->getTasksByElementId(44);
		if(!empty($queueData))
		{
			$missileDepot = $queueData[count($queueData)-1]['amount'];
		}
		else
		{
			$missileDepot = $planet->{Vars::getElement(44)->name};
		}

		$maxMissiles		= $missileDepot * 10 * max(Config::get($user->universe)->silo_factor, 1);

		$buildableMissileCount  = max(0, $maxMissiles, $currentMissiles);
		$buildableMissiles	= array();

		foreach($missileElements as $missileElementId => $missileElementObj)
		{
			if($missileElementObj->hasFlag(Vars::FLAG_ATTACK_MISSILE))
			{
				$buildableMissiles[$missileElementId]   = $buildableMissileCount / 2;
			}
			else
			{
				$buildableMissiles[$missileElementId]	= $buildableMissileCount;
			}
		}

		return $buildableMissiles;
	}
}