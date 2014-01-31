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
 * @info $Id: AbstractGamePage.class.php 2746 2013-05-18 11:38:36Z slaver7 $
 * @link http://2moons.cc/
 */

require 'includes/classes/AbstractPage.class.php';

abstract class AbstractIndexPage extends AbstractPage
{
	protected function getUniverseSelector()
	{
		$universeSelect	= array();
		foreach(Universe::availableUniverses() as $uniId)
		{
			$universeSelect[$uniId]	= Config::get($uniId)->uni_name;
		}

		return $universeSelect;
	}

    protected function assignBasicData()
    {
        global $LNG;

        $config	= Config::get();
        $this->assign(array(
            'lang'    			    => $LNG->getLanguage(),
            'basePath'			    => PROTOCOL.HTTP_HOST.HTTP_BASE,
            'isMultiUniverse'	    => count(Universe::availableUniverses()) > 1,
            'recaptchaEnable'		=> $config->capaktiv,
            'recaptchaPublicKey'	=> $config->cappublic,
            'gameName' 				=> $config->game_name,
            'facebookEnable'		=> $config->fb_on,
            'fb_key' 				=> $config->fb_apikey,
            'mailEnable'			=> $config->mail_active,
            'reg_close'				=> $config->reg_closed,
            'referralEnable'		=> $config->ref_active,
            'analyticsEnable'		=> $config->ga_active,
            'analyticsUID'			=> $config->ga_key,
            'UNI'					=> Universe::current(),
            'VERSION'				=> $config->VERSION,
            'REV'					=> substr($config->VERSION, -4),
            'languages'				=> Language::getAvailableLanguages(false),
        ));
    }
}