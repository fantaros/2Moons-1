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
 * @info $Id: ShowAlliancePage.class.php 2776 2013-08-05 21:30:40Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowRequirementsPage extends AbstractInstallPage
{
    public static $requiredDirectories    = array('cache/', 'cache/templates/', 'cache/sessions/', 'includes/');
    public static $requiredFiles          = array('includes/config.php');

    public function show()
    {
        global $LNG;

        $isError        = false;
        $writeError     = false;

        $requirements   = array();

        $valueHTML      = '<span class="%s">%s</span>';

        clearstatcache();

        // Check PHP Version
        if(version_compare(PHP_VERSION, "5.2.5", ">="))
        {
            $value      = 'yes';
        }
        else
        {
            $value      = 'no';
            $isError    = true;
        }

        $requirements[] = array(
            'name'          => $LNG['req_php_need'],
            'description'   => $LNG['req_php_need_desc'],
            'value'         => sprintf($valueHTML, $value, $LNG['reg_'.$value].', '.PHP_VERSION),
        );

        // Check register globals is disabled
        if(!ini_get('register_globals'))
        {
            $value      = 'yes';
        }
        else
        {
            $value      = 'no';
            $isError    = true;
        }

        $requirements[] = array(
            'name'          => $LNG['reg_global_need'],
            'description'   => $LNG['reg_global_desc'],
            'value'         => sprintf($valueHTML, $value, $LNG['reg_'.$value]),
        );

        // Check PDO is available
        if(class_exists('PDO'))
        {
            $value      = 'yes';
        }
        else
        {
            $value      = 'no';
            $isError    = true;
        }

        $requirements[] = array(
            'name'          => $LNG['reg_pdo_active'],
            'description'   => $LNG['reg_pdo_desc'],
            'value'         => sprintf($valueHTML, $value, $LNG['reg_'.$value]),
        );

        // Check if gdlib is available
        if(extension_loaded('gd'))
        {
            $value      = 'yes';
        }
        else
        {
            $value      = 'no';
            $isError    = true;
        }

        $requirements[] = array(
            'name'          => $LNG['reg_gd_need'],
            'description'   => $LNG['reg_gd_desc'],
            'value'         => sprintf($valueHTML, $value, $LNG['reg_'.$value]),
        );

        // Check if json is available
        if(function_exists('json_encode'))
        {
            $value      = 'yes';
        }
        else
        {
            $value      = 'no';
            $isError    = true;
        }

        $requirements[] = array(
            'name'          => $LNG['reg_json_need'],
            'value'         => sprintf($valueHTML, $value, $LNG['reg_'.$value]),
        );

        // Check if ini_set is available
        if(function_exists('ini_set'))
        {
            $value      = 'yes';
        }
        else
        {
            $value      = 'no';
            $isError    = true;
        }

        $requirements[] = array(
            'name'          => $LNG['reg_iniset_need'],
            'value'         => sprintf($valueHTML, $value, $LNG['reg_'.$value]),
        );

        foreach(self::$requiredFiles as $file)
        {
            if (file_exists($file) || @touch($file))
            {
                $value = sprintf($valueHTML, 'yes', $LNG['reg_found']);

                if (is_writable($file) || @chmod($file, 0777))
                {
                    $value .= ' - '.sprintf($valueHTML, 'yes', $LNG['reg_writable']);
                }
                else
                {
                    $value .= ' - '.sprintf($valueHTML, 'no', $LNG['reg_writable']);

                    $isError    = true;
                    $writeError = true;
                }
            }
            else
            {
                $value      = sprintf($valueHTML, 'no', $LNG['reg_not_found']);
                $isError    = true;
                $writeError = true;
            }

            $requirements[] = array(
                'name'          => sprintf($LNG['reg_file'], $file),
                'value'         => $value,
            );
        }

        foreach (self::$requiredDirectories as $dir)
        {
            if (file_exists($dir) || @mkdir($dir, 0777, true))
            {
                $value = sprintf($valueHTML, 'yes', $LNG['reg_found']);

                if (is_writable($dir) || @chmod($dir, 0777))
                {
                    $value .= ' - '.sprintf($valueHTML, 'yes', $LNG['reg_writable']);
                }
                else
                {
                    $value .= ' - '.sprintf($valueHTML, 'no', $LNG['reg_writable']);

                    $isError = true;
                    $writeError   = true;
                }
            }
            else
            {
                $value  = sprintf($valueHTML, 'no', $LNG['reg_not_found']);
                $isError  = true;
                $writeError    = true;
            }

            $requirements[] = array(
                'name'          => sprintf($LNG['reg_dir'], $dir),
                'value'         => $value,
            );
        }


        if (function_exists('ftp_connect') === false)
        {
            $writeError = false;
        }

        $this->assign(array(
            'requirements'  => $requirements,
            'isError'       => $isError,
            'writeError'    => $writeError,
        ));

        $this->display('page.requirements.default');
    }
}