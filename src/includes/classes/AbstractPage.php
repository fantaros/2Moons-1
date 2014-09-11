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
 * @info $Id: AbstractGamePage.class.php 2803 2013-10-06 22:23:27Z slaver7 $
 * @link http://2moons.cc/
 */

abstract class AbstractPage
{
    /**
     * reference of the template object
     * @var Template
     */
    protected $tplObj = null;
    protected $window = 'full';

    protected function __construct()
    {
        if(!AJAX_REQUEST)
        {
            $this->initTemplate();
        }
        else
        {
            $this->setWindow('ajax');
        }
    }

    protected function initTemplate()
    {
        if(isset($this->tplObj))
            return true;

        $this->tplObj	= new Template;
        return true;
    }

    protected function setWindow($window)
    {
        $this->window	= $window;
    }

    protected function getWindow()
    {
        return $this->window;
    }

    protected function getQueryString()
    {
        $queryString	= array();
        $page			= HTTP::_GP('page', '');

        if(!empty($page)) {
            $queryString['page']	= $page;
        }

        $mode			= HTTP::_GP('mode', '');
        if(!empty($mode)) {
            $queryString['mode']	= $mode;
        }

        return http_build_query($queryString);
    }

    protected function assign($array)
    {
        $this->tplObj->getSmartyObj()->assign($array, NULL, true);
    }

    protected function display($file)
    {
        global $LNG;

        $this->save();

        if($this->getWindow() !== 'ajax')
        {
            $this->assignFullPageData();
        }

        $this->assignBasicData();

        $this->assign(array(
            'metaRefresh'   => array(),
            'bodyclass'     => $this->getWindow(),
        ));

        $this->tplObj->getSmartyObj()->assign(array(
            'LNG'			=> $LNG,
        ), NULL, false);

        header('Content-Type: text/html; charset=UTF-8');
        $this->tplObj->display('extends:layout.'.$this->getWindow().'.tpl|'.$file.'.tpl');
        exit;
    }

    protected function assignFullPageData()
    {

    }

    protected function assignBasicData()
    {

    }

    protected function save()
    {

    }

    protected function metaRefresh($url, $seconds = 3)
    {
        $this->assign(array(
            'metaRefresh' => array(
                'url'       => $url,
                'seconds'   => (int) $seconds,
            )
        ));
    }

    protected function printMessage($message, $redirectButtons = null, $redirect = null, $fullSide = true)
    {
        if(isset($redirect))
        {
            $this->metaRefresh($redirect[0], $redirect[1]);
        }

        if(!$fullSide)
        {
            $this->setWindow('popup');
        }

        $this->assign(array(
            'message'			=> $message,
            'redirectButtons'	=> $redirectButtons,
        ));

        $this->display('error.default');
    }

    protected function sendJSON($data)
    {
        $this->save();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
        exit;
    }

    protected function redirectTo($url)
    {
        $this->save();
        HTTP::redirectTo($url);
    }

    protected function redirectPost($url, $postFields)
    {
        $this->save();
        $this->assign(array(
            'url'    		=> $url,
            'postFields'	=> $postFields,
        ));

        $this->display('info.redirectPost');
    }
}