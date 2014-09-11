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
 * @info $Id: class.theme.php 2790 2013-09-20 21:18:08Z slaver7 $
 * @link http://2moons.cc/
 */
 
class Theme
{
	static public $themeList;
	private $THEMESETTINGS;
	
	function __construct()
	{	
		$this->skininfo = array();
		$this->skin		= isset($_SESSION['themeName']) ? $_SESSION['themeName'] : DEFAULT_THEME;
		$this->setUserTheme($this->skin);
	}
	
	function isHome()
    {
		$this->template		= ROOT_PATH.'styles/home/';
		$this->customtpls	= array();
	}
	
	function setUserTheme($theme)
    {
		if(!file_exists(THEME_PATH.$theme.'/style.cfg'))
			return false;
			
		$this->skin		= $theme;
		$this->parseStyleCFG();
		$this->setStyleSettings();
        return true;
	}
		
	function getTheme()
    {
		return str_replace(ROOT_PATH, "", THEME_PATH).$this->skin.'/';
	}
	
	function getThemeName()
    {
		return $this->skin;
	}
	
	function getTemplatePath()
    {
		return ROOT_PATH.$this->getTheme().'templates/';
	}
		
	function isCustomTPL($tpl)
    {
		if(!isset($this->customtpls))
			return false;
			
		return in_array($tpl, $this->customtpls);
	}
	
	function parseStyleCFG()
    {
        $cfgPath = THEME_PATH.$this->skin.'/style.cfg';
        if(!is_readable($cfgPath))
        {
            throw new Exception("Missing style.cfg on current theme!");
        }

		require $cfgPath;
        if(!isset($Skin))
        {
            throw new Exception("Incorrect style.cfg on current theme!");
        }

		$this->skininfo		= $Skin;
		$this->customtpls	= (array) $Skin['templates'];	
	}
	
	function setStyleSettings()
    {
        $THEMESETTINGS = array();

        $settingsCfg = THEME_PATH.$this->skin.'/settings.cfg';

		if(file_exists($settingsCfg)) {
			require $settingsCfg;
		}
		
		$this->THEMESETTINGS	= array_merge(array(
			'PLANET_ROWS_ON_OVERVIEW' => 2,
			'SHORTCUT_ROWS_ON_FLEET1' => 2,
			'COLONY_ROWS_ON_FLEET1' => 2,
			'ACS_ROWS_ON_FLEET1' => 1,
			'TOPNAV_SHORTLY_NUMBER' => 0,
		), $THEMESETTINGS);
	}
	
	function getStyleSettings()
    {
		return $this->THEMESETTINGS;
	}
	
	static function getAvailableSkins()
    {
		if(!isset(self::$themeList))
		{
			if(file_exists(ROOT_PATH.'cache/cache.themes.php'))
			{
				self::$themeList	= unserialize(file_get_contents(ROOT_PATH.'cache/cache.themes.php'));
			}
            else
            {
				$Skins	= array_diff(scandir(THEME_PATH), array('..', '.', '.svn', '.htaccess', 'index.htm'));
				$themeList	= array();
				foreach($Skins as $theme) {
                    $cfgPath = THEME_PATH.$theme.'/style.cfg';
					if(!file_exists($cfgPath))
						continue;
						
					require $cfgPath;

                    if(isset($Skin['name']))
                    {
                        $themeList[$theme]	= $Skin['name'];
                    }
				}

				file_put_contents(ROOT_PATH.'cache/cache.themes.php', serialize($themeList));
				self::$themeList	= $themeList;
			}
		}
		return self::$themeList;
	}
}
