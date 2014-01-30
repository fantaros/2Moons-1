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
 * @version 1.8.0 (2013-03-18)
 * @info $Id: AbstractGamePage.class.php 2793 2013-09-29 12:33:56Z slaver7 $
 * @link http://2moons.cc/
 */

require 'includes/classes/AbstractPage.class.php';

abstract class AbstractAdminPage extends AbstractPage
{
	protected function getPageData() 
    {
		global $USER, $THEME;
		
		$dateTimeServer		= new DateTime("now");
		if(isset($USER['timezone'])) {
			try {
				$dateTimeUser	= new DateTime("now", new DateTimeZone($USER['timezone']));
			} catch (Exception $e) {
				$dateTimeUser	= $dateTimeServer;
			}
		} else {
			$dateTimeUser	= $dateTimeServer;
		}

		$config	= Config::get();

        $this->assign(array(
            'vmode'				=> $USER['urlaubs_modus'],
			'authlevel'			=> $USER['authlevel'],
			'userID'			=> $USER['id'],
			'bodyclass'			=> $this->getWindow(),
            'game_name'			=> $config->game_name,
            'uni_name'			=> $config->uni_name,
			'ga_active'			=> $config->ga_active,
			'ga_key'			=> $config->ga_key,
			'debug'				=> $config->debug,
			'VERSION'			=> $config->VERSION,
			'date'				=> explode("|", date('Y\|n\|j\|G\|i\|s\|Z', TIMESTAMP)),
			'REV'				=> substr($config->VERSION, -4),
			'Offset'			=> $dateTimeUser->getOffset() - $dateTimeServer->getOffset(),
			'queryString'		=> $this->getQueryString(),
			'themeSettings'		=> $THEME->getStyleSettings(),
		));
	}

	protected function assignBasicData()
    {
        global $THEME, $LNG;

        $this->assign(array(
            'lang'    		=> $LNG->getLanguage(),
            'themeName'		=> $THEME->getTheme(),
            'scripts'		=> $this->tplObj->jsscript,
            'execscript'	=> implode("\n", $this->tplObj->script),
            'basePath'		=> PROTOCOL.HTTP_HOST.HTTP_BASE,
            'sessionId'		=> session_id()
        ));
	}
}