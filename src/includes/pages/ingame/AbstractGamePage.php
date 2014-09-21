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
 * @info $Id: AbstractGamePage.php 2803 2013-10-06 22:23:27Z slaver7 $
 * @link http://2moons.cc/
 */

require 'includes/classes/AbstractPage.php';

abstract class AbstractGamePage extends AbstractPage
{
	/**
	 * @var User
	 */
	protected $user;

    /**
     * @var Planet
     */
	protected $planet;

    /**
     * @var Language
     */
    protected $lang;

    protected $disableEcoSystem = false;

    public static $requireModule = 0;
	
	public function __construct()
    {
	    parent::__construct();

        $this->user     = Session::get()->getUser();
        $this->lang     = $this->user->getLangObj();
        if(isset($this->requireModule) && $this->requireModule !== 0 && !$this->user->can($this->requireModule))
        {
            $this->printMessage($this->lang['sys_module_inactive']);
        }

        $this->planet   = $this->user->getCurrentPlanet();

        if(!AJAX_REQUEST && !$this->user->can(MODULE_FLEET_EVENTS))
        {
            FleetHandler::run();
        }
	}
	
	protected function getCronjobsTodo()
	{
		$this->assign(array(
			'cronjobs'  => Cron::getNeedTodoExecutedJobs()
		));
	}
	
	protected function getNavigationData() 
    {
		$config			= Config::get();

        $PlanetSelect	= array();

        foreach($this->user->getPlanetList() as $PlanetQuery)
		{
			$PlanetSelect[$PlanetQuery['id']]	= $PlanetQuery['name'].(($PlanetQuery['planet_type'] == 3) ? " (" . $this->lang['fcm_moon'] . ")":"")." [".$PlanetQuery['galaxy'].":".$PlanetQuery['system'].":".$PlanetQuery['planet']."]";
		}
		
		$resourceTable	= array();
		$resourceSpeed	= $config->resource_multiplier;

        foreach(Vars::getElements(VARS::CLASS_RESOURCE, Vars::FLAG_TOPNAV) as $elementId => $elementObj)
		{
            $elementName                                = $elementObj->name;
			$resourceTable[$elementId]['name']			= $elementName;
            if($elementObj->hasFlag(Vars::FLAG_RESOURCE_USER))
            {
                $resourceTable[$elementId]['current']		= $this->user->$elementName;
            }
            else
            {
                if($elementObj->hasFlag(Vars::FLAG_ENERGY))
                {
                    $resourceTable[$elementId]['used']		= $this->planet->{$elementName.'_used'};
                    $resourceTable[$elementId]['max']		= $this->planet->$elementName;
                }
                else
                {
                    $resourceTable[$elementId]['current']		= $this->planet->$elementName;
                    $resourceTable[$elementId]['max']			= $this->planet->{$elementName.'_max'};
                    if($this->user->onVacation() || $this->planet->planet_type != PLANET)
                    {
                        $resourceTable[$elementId]['production']	= 0;
                    }
                    else
                    {
                        $resourceTable[$elementId]['production']	= $this->planet->{$elementName.'_perhour'} + $config->{$elementName.'_basic_income'} * $resourceSpeed;
                    }
                }
            }
		}

        $vacation        = false;
        $deleteAccount   = false;

        if($this->user->onVacation())
        {
            $vacation       = _date($this->lang['php_tdformat'], $this->user->urlaubs_until, $this->user->timezone);
        }

        if($this->user->db_deaktjava)
        {
            $deleteAccount  = sprintf($this->lang['tn_delete_mode'],
                _date($this->lang['php_tdformat'], $this->user->db_deaktjava + ($config->del_user_manually * 86400)), $this->user->timezone
            );
        }
		
		$this->assign(array(
			'PlanetSelect'		=> $PlanetSelect,
			'new_message' 		=> $this->user->messages,
			'vacation'			=> $vacation,
			'delete'			=> $deleteAccount,
			'darkmatter'		=> $this->user->darkmatter,
			'current_pid'		=> $this->planet->id,
			'image'				=> $this->planet->image,
			'resourceTable'		=> $resourceTable,
			'shortlyNumber'		=> $themeSettings['TOPNAV_SHORTLY_NUMBER'],
			'closed'			=> !$config->game_disable,
			'hasBoard'			=> filter_var($config->forum_url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED),
			'hasAdminAccess'	=> !empty(Session::get()->adminAccess),
			'hasGate'			=> $this->planet->{Vars::getElement(43)->name} > 0
		));
	}


    protected function assignFullPageData()
    {
        $this->getNavigationData();
        $this->getCronjobsTodo();
    }

    protected function assignBasicData()
    {
		$config	= Config::get();

        $this->assign(array(
            'vmode'			=> $this->user->urlaubs_modus,
			'authlevel'		=> $this->user->authlevel,
			'userID'		=> $this->user->id,
            'gameName'		=> $config->game_name,
            'uniName'		=> $config->uni_name,
			'ga_active'		=> $config->ga_active,
			'ga_key'		=> $config->ga_key,
			'debug'			=> $config->debug,
			'VERSION'		=> $config->VERSION,
			'date'			=> explode("|", date('Y\|n\|j\|G\|i\|s\|Z', TIMESTAMP)),
			'REV'			=> substr($config->VERSION, -4),
            'Offset'		=> $this->user->getServerTimeDifference(),
			'queryString'	=> $this->getQueryString(),
			'themeSettings'	=> $THEME->getStyleSettings(),
            'lang'    		=> Session::get()->getUser()->getLangObj(),
            'themePath'		=> $THEME->getTheme(),
            'basePath'		=> PROTOCOL.HTTP_HOST.HTTP_BASE,
		));
	}
	
	protected function save()
    {

	}
}