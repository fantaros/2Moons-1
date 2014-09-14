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
 * @info $Id: ShowOfficierPage.class.php 2786 2013-08-13 18:52:18Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowOfficierPage extends AbstractGamePage
{
    private function formatBonusList($elementObj)
    {
        $list   = array();

        foreach($elementObj->bonus as $bonusName => $bonusData)
        {
            if($bonusData['value'] == 0) continue;

            if($bonusData['unit'] === 'static')
            {
                $list[] = ($bonusData['value'] > 0 ? '+' : '').$bonusData['value'].' '.$this->lang['bonus'][$bonusName];
            }
            else
            {
                $list[] = ($bonusData['value'] > 0 ? '+' : '').($bonusData['value'] * 100).'% '.$this->lang['bonus'][$bonusName];
            }
        }

        return $list;
    }

    public function upgrade()
    {
        $elementId = HTTP::_GP('elementId', 0);

        $elementObj = Vars::getElement($elementId);

        if($elementObj->class == Vars::CLASS_PERM_BONUS && $this->user->can(MODULE_OFFICIER))
        {
            if($elementObj->maxLevel <= $this->user->{$elementObj->name})
            {
				$this->redirectTo('game.php?page=officier');
            }

            if(!BuildUtil::requirementsAvailable($this->user, $this->planet, $elementObj))
            {
				$this->redirectTo('game.php?page=officier');
            }

            $costResources		= BuildUtil::getElementPrice($elementObj, $this->user->{$elementObj->name} + 1);

            if (!BuildUtil::isElementBuyable($this->user, $this->planet, $elementObj, $costResources))
            {
				$this->redirectTo('game.php?page=officier');
            }

            foreach($costResources as $resourceElementId => $value)
            {
                $resourceElementObj    = Vars::getElement($resourceElementId);
                if($resourceElementObj->hasFlag(Vars::FLAG_RESOURCE_PLANET))
                {
                    $this->planet->{$resourceElementObj->name}	-= $costResources[$resourceElementId];
                }
                elseif($resourceElementObj->hasFlag(Vars::FLAG_RESOURCE_USER))
                {
                    $this->user->{$resourceElementObj->name}    -= $costResources[$resourceElementId];
                }
            }

            $this->user->{$elementObj->name}	+= 1;

        }
		elseif($elementObj->class == Vars::CLASS_TEMP_BONUS && $this->user->can(MODULE_DMEXTRAS))
        {
            if(!BuildUtil::requirementsAvailable($this->user, $this->planet, $elementObj))
            {
				$this->redirectTo('game.php?page=officier');
            }

            $costResources		= BuildUtil::getElementPrice($elementObj, 1);

            if (!BuildUtil::isElementBuyable($this->user, $this->planet, $elementObj, $costResources))
            {
				$this->redirectTo('game.php?page=officier');
            }

            foreach($costResources as $resourceElementId => $value)
            {
                $resourceElementObj    = Vars::getElement($resourceElementId);
                if($resourceElementObj->hasFlag(Vars::FLAG_RESOURCE_PLANET))
                {
                    $this->planet->{$resourceElementObj->name}	-= $costResources[$resourceElementId];
                }
                elseif($resourceElementObj->hasFlag(Vars::FLAG_RESOURCE_USER))
                {
                    $this->user->{$resourceElementObj->name}    -= $costResources[$resourceElementId];
                }
            }

            $this->user->{$elementObj->name}	= max($this->user->{$elementObj->name}, TIMESTAMP) + $elementObj->timeBonus;
        }

		$this->redirectTo('game.php?page=officier');
    }

	public function show()
	{
		$darkmatterList	= array();
		$officierList	= array();

		if($this->user->can(MODULE_DMEXTRAS))
		{
			foreach(Vars::getElements(Vars::CLASS_TEMP_BONUS) as $elementId => $elementObj)
			{
                if (!BuildUtil::requirementsAvailable($this->user, $this->planet, $elementObj)) continue;

				$costResources		= BuildUtil::getElementPrice($elementObj, 1);
				$buyable			= BuildUtil::isElementBuyable($this->user, $this->planet, $elementObj, $costResources);

                // zero cost resource do not need to display
                $costResources		= array_filter($costResources);

				$costOverflow		= BuildUtil::getRestPrice($this->user, $this->planet, $elementObj, $costResources);

				$darkmatterList[$elementId]	= array(
					'timeLeft'		=> max($this->user->{$elementObj->name} - TIMESTAMP, 0),
					'costResources'	=> $costResources,
					'buyable'		=> $buyable,
					'time'			=> $elementObj->timeBonus,
					'costOverflow'	=> $costOverflow,
					'elementBonus'	=> $this->formatBonusList($elementObj),
				);
			}
		}

		if($this->user->can(MODULE_OFFICIER))
		{
            foreach(Vars::getElements(Vars::CLASS_PERM_BONUS) as $elementId => $elementObj)
			{
				if (!BuildUtil::requirementsAvailable($this->user, $this->planet, $elementObj)) continue;

                $costResources		= BuildUtil::getElementPrice($elementObj, $this->user->{$elementObj->name} + 1);
				$buyable			= BuildUtil::isElementBuyable($this->user, $this->planet, $elementObj, $costResources);

                // zero cost resource do not need to display
                $costResources		= array_filter($costResources);

				$costOverflow		= BuildUtil::getRestPrice($this->user, $this->planet, $elementObj, $costResources);

				$officierList[$elementId]	= array(
					'level'			=> $this->user->{$elementObj->name},
					'maxLevel'		=> $elementObj->maxLevel,
					'costResources' => $costResources,
					'buyable'		=> $buyable,
					'costOverflow'	=> $costOverflow,
					'elementBonus'	=> $this->formatBonusList($elementObj),
				);
			}
		}

		$this->assign(array(
			'officierList'		=> $officierList,
			'darkmatterList'	=> $darkmatterList,
			'of_dm_trade'		=> sprintf($this->lang['of_dm_trade'], $this->lang['tech'][921]),
		));

		$this->display('page.officier.default');
	}
}