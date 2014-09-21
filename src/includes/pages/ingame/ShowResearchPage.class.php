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
 * @info $Id: ShowResearchPage.class.php 2786 2013-08-13 18:52:18Z slaver7 $
 * @link http://2moons.cc/
 */

require_once('AbstractGamePage.php');

class ShowResearchPage extends AbstractGamePage
{
	public static $requireModule = MODULE_RESEARCH;

    public function build()
    {
        global $USER;
        $elementId  = HTTP::_GP('elementId', 0);
        if($_SERVER['REQUEST_METHOD'] === 'POST' && $this->user->urlaubs_modus == 0 && !empty($elementId))
        {
            $elementObj = Vars::getElement($elementId);
            if($elementObj->class == Vars::CLASS_TECH)
            {
                $this->ecoObj->addToQueue($elementObj, QueueManager::USER);
            }
        }
        $this->redirectTo('game.php?page=research');
    }

    public function cancel()
    {
        $taskId = HTTP::_GP('taskId', 0);
        if($_SERVER['REQUEST_METHOD'] === 'POST' && $this->user->urlaubs_modus == 0 && !empty($taskId))
        {
            $this->ecoObj->removeFromQueue($taskId, Vars::CLASS_TECH);
        }
        $this->redirectTo('game.php?page=research');
    }

    private function getQueueData()
    {
        $queueData  = $this->ecoObj->getQueueObj()->getTasksByElementId(array_keys(Vars::getElements(Vars::CLASS_TECH)));

        $queue          = array();
        $elementLevel   = array();
        $count          = array();


        foreach($queueData as $task)
        {
            if ($task['endBuildTime'] < TIMESTAMP)
                continue;

            $queue[$task['taskId']] = array(
                'element'	=> $task['elementId'],
                'level' 	=> $task['amount'],
                'time' 		=> $task['buildTime'],
                'resttime' 	=> $task['endBuildTime'] - TIMESTAMP,
                'endtime' 	=> _date('U', $task['endBuildTime'], $this->user->timezone),
                'display' 	=> _date($this->lang['php_tdformat'], $task['endBuildTime'], $this->user->timezone),
                'planet'	=> $task['planetId'] != $this->planet->id ? $this->user->PLANETS[$task['planetId']]['name'] : false,
            );

            $elementLevel[$task['elementId']]   = $task['amount'];
            if(!isset($count[$task['queueId']]))
            {
                $count[$task['queueId']] = 0;
            }

            $count[$task['queueId']]++;
        }

        return array('queue' => $queue, 'elementLevel' => $elementLevel, 'count' => $count);
    }

	public function show()
	{
		if ($this->planet->getElement(31) == 0)
		{
			$this->printMessage($this->lang['bd_lab_required']);
		}

        if(!isset($this->user->techNetwork))
        {
            $this->user->techNetwork  = PlayerUtil::getLabLevelByNetwork($USER, $PLANET);
        }

        $busyQueues     = array();
		
		$queueData		= $this->getQueueData();
		$ResearchList	= array();

        $isLabInBuild   = false;

		foreach(Vars::getElements(Vars::CLASS_TECH) as $elementId => $elementObj)
		{
			if (!BuildUtil::requirementsAvailable($USER, $PLANET, $elementObj))
				continue;
				
			if(isset($queueData['elementLevel'][$elementId]))
			{
				$levelToBuild	= $queueData['elementLevel'][$elementId];
			}
			else
			{
				$levelToBuild	= $USER[$elementObj->name];
			}
			
			$costResources		= BuildUtil::getElementPrice($elementObj, $levelToBuild + 1);
            $elementTime    	= BuildUtil::getBuildingTime($USER, $PLANET, $elementObj, $costResources);

            // zero cost resource do not need to display
            $costResources		= array_filter($costResources);

			$costOverflow		= BuildUtil::getRestPrice($USER, $PLANET, $elementObj, $costResources);

            if(!isset($busyQueues[$elementObj->queueId]))
            {
                $tasks  = $this->ecoObj->getQueueObj()->getTasksByElementId(Vars::getElement($elementObj->queueId)->blocker);
                $busyQueues[$elementObj->queueId] = count($tasks) != 0;
            }

            $isBusy             = $busyQueues[$elementObj->queueId];

            if($isBusy)
            {
                $buyable    = false;
                $isLabInBuild   = true;
            }
            elseif(isset($queueData['count'][$elementObj->queueId]) && $queueData['count'][$elementObj->queueId] >= Vars::getElement($elementObj->queueId)->maxCount)
            {
                $buyable    = false;
            }
            elseif(isset($queueData['count'][$elementObj->queueId]) && $queueData['count'][$elementObj->queueId] > 0)
            {
                $buyable    = true;
            }
            else
            {
                $buyable    = BuildUtil::isElementBuyable($USER, $PLANET, $elementObj, $costResources);
            }

			$ResearchList[$elementId]	= array(
                'level'				=> $USER[$elementObj->name],
                'maxLevel'			=> $elementObj->maxLevel,
				'costResources'		=> $costResources,
				'costOverflow'		=> $costOverflow,
				'elementTime'    	=> $elementTime,
				'buyable'			=> $buyable,
                'isBusy'			=> $isBusy,
				'levelToBuild'		=> $levelToBuild,
			);
		}
		
		$this->assign(array(
			'Queue'			=> $queueData['queue'],
            'isLabInBuild'  => $isLabInBuild,
            'ResearchList'  => $ResearchList,
		));
		
		$this->display('page.research.default');
	}
}