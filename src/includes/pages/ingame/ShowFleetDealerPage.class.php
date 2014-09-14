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
 * @info $Id: ShowFleetDealerPage.class.php 2789 2013-09-20 21:13:40Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowFleetDealerPage extends AbstractGamePage
{
	public static $requireModule = MODULE_FLEET_TRADER;

    public function send()
	{
		$elementId		= HTTP::_GP('shipId', 0);
		$amount			= max(0, round(HTTP::_GP('count', 0.0)));
		$allowedShipIDs	= array_merge(Vars::getElements(Vars::CLASS_FLEET, Vars::FLAG_TRADE), Vars::getElements(Vars::CLASS_DEFENSE, Vars::FLAG_TRADE));

        $elementObj     = Vars::getElement($elementId);
		if(!empty($amount) && in_array($elementId, $allowedShipIDs) && $this->planet->{$elementObj->name} >= $amount)
		{
			$tradeCharge					= 1 - (Config::get()->trade_charge / 100);
            $costResource                   = BuildUtil::getElementPrice($elementObj, $amount * $tradeCharge);
            foreach($costResource as $resourceElementId => $resourceAmount)
            {
                $resourceElementObj = Vars::getElement($resourceElementId);

                if($resourceElementObj->isUserResource())
                {
                    $this->user->{$resourceElementObj->name}    += $resourceAmount;
                }
                else
                {
                    $this->planet->{$resourceElementObj->name}  += $resourceAmount;
                }
            }
			
			$this->planet->{$elementObj->name}  -= $amount;
            $this->ecoObj->saveToDatabase('PLANET', $elementObj->name);

            $this->printMessage($this->lang['tr_exchange_done'], array(array(
				'label'	=> $this->lang['sys_forward'],
				'url'	=> 'game.php?page=fleetDealer'
			)));
		}
		else
		{
			$this->printMessage($this->lang['tr_exchange_error'], array(array(
				'label'	=> $this->lang['sys_back'],
				'url'	=> 'game.php?page=fleetDealer'
			)));
		}
	}
	
	function show()
	{
		$elementsData   = array();

        $allowedShipIDs = Vars::getElements(Vars::CLASS_FLEET, Vars::FLAG_TRADE) + Vars::getElements(Vars::CLASS_DEFENSE, Vars::FLAG_TRADE);

		foreach($allowedShipIDs as $elementId => $elementObj)
		{
            $elementsData[$elementId]	= array(
                'available' => $this->planet->{$elementObj->name},
                'price'     => BuildUtil::getElementPrice($elementObj)
            );
		}

		if(empty($elementsData))
		{
			$this->printMessage($this->lang['ft_empty']);
		}

		$this->assign(array(
			'elementsData'	=> $elementsData,
			'Charge'	    => Config::get()->trade_charge,
		));
		
		$this->display('page.fleetDealer.default');
	}
}