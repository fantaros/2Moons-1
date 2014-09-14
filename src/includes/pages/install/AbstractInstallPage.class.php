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
 * @info $Id: AbstractGamePage.class.php 2803 2013-10-06 22:23:27Z slaver7 $
 * @link http://2moons.cc/
 */

require 'includes/classes/AbstractPage.php';

abstract class AbstractInstallPage extends AbstractPage
{
    public static $enableInstallToolFile = 'includes/ENABLE_INSTALL_TOOL';
    public static $quickStartFile        = 'includes/FIRST_INSTALL';

    public function __construct()
    {
        parent::__construct();
        $this->isInstallToolEnabled();
    }

    protected function assignFullPageData()
    {
        global $LNG;
        $gameVersion    = file_get_contents('install/VERSION');
        $gameRevision   = explode('.', $gameVersion);
        $this->assign(array(
            'lang'    		=> $LNG->getLanguage(),
            'themePath'		=> 'styles/theme/'.DEFAULT_THEME.'/',
            'basePath'		=> str_replace('install/', '', PROTOCOL.HTTP_HOST.HTTP_BASE),
            'VERSION'		=> $gameVersion,
            'REV'		    => $gameRevision[2],
            'gameName'      => '2Moons'
        ));
    }

    protected function isInstallToolEnabled()
    {
        global $LNG;
        // If include/FIRST_INSTALL is present and can be deleted, automatically create include/ENABLE_INSTALL_TOOL
        if (is_file(self::$quickStartFile) && is_writeable(self::$quickStartFile) && unlink(self::$quickStartFile)) {
            @touch(self::$enableInstallToolFile);
        }
        // Only allow Install Tool access if the file "include/ENABLE_INSTALL_TOOL" is found
        if (is_file(self::$enableInstallToolFile) && (time() - filemtime(self::$enableInstallToolFile) > 3600)) {
            $content      = file_get_contents(self::$enableInstallToolFile);
            $verifyString = 'KEEP_FILE';
            if (trim($content) !== $verifyString) {
                // Delete the file if it is older than 3600s (1 hour)
                unlink(self::$enableInstallToolFile);
            }
        }
        if (!is_file(self::$enableInstallToolFile)) {
            $this->printMessage($LNG->getTemplate('locked_install'));
            exit;
        }
    }
}