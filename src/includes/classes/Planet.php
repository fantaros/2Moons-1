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
 * @info $Id: ShowBanListPage.class.php 2776 2013-08-05 21:30:40Z slaver7 $
 * @link http://2moons.cc/
 */

class Planet extends Model
{
    private $data = false;

    private $planetId;

    public function __construct($planetId, $selectData = '*')
    {
        $this->db       = Database::get();

        if(!is_numeric($planetId))
        {
            throw new InvalidArgumentException("Invalid Planet ID:", $planetId);
        }

        $this->planetId = $planetId;

        if(!empty($selectData))
        {
            if(is_array($selectData))
            {
                $selectData = implode(',', $selectData);
            }
            $this->data = $this->db->selectSingle("SELECT ".$selectData." FROM %PLANETS% WHERE id = :userId;", array(
                ':planetId'   => $planetId
            ));
        }

        if(empty($this->data))
        {
            $this->data = false;
        }
    }

    public function save()
    {
        if(empty($this->changed)) return;

        $sql = "UPDATE TABLE %PLANETS% SET %s WHERE id = :planetId;";

        $sqlWhere   = array();
        $sqlData    = array(
            ':planetId' => $this->planetId
        );

        foreach($this->changed as $key => $value)
        {
            $sqlData[':'.$key]  = $value;
            $sqlWhere[]         = ':'.$key.' = '.$key;
        }

        $this->db->update(sprintf($sql, implode(',', $sqlWhere)), $sqlData);
    }
} 