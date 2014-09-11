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
 * @info $Id: ShowNotesPage.class.php 2800 2013-10-04 22:07:04Z slaver7 $
 * @link http://2moons.cc/
 */

 
class ShowNotesPage extends AbstractGamePage
{
	public static $requireModule = MODULE_NOTICE;

	function __construct() 
	{
		parent::__construct();
		$this->setWindow('popup');
		$this->initTemplate();
	}
	
	function show()
	{

        $db = Database::get();

        $sql = "SELECT * FROM %%NOTES%% WHERE owner = :userID ORDER BY priority DESC, time DESC;";
        $notesResult = $db->select($sql, array(
            ':userID'   => $this->user->id
        ));

        $notesList		= array();
		
		foreach($notesResult as $notesRow)
		{
			$notesList[$notesRow['id']]	= array(
				'time'		=> _date($this->lang['php_tdformat'], $notesRow['time'], $this->user->timezone),
				'title'		=> $notesRow['title'],
				'size'		=> strlen($notesRow['text']),
				'priority'	=> $notesRow['priority'],
			);
		}
		
		$this->assign(array(
			'notesList'	=> $notesList,
		));
		
		$this->display('page.notes.default');
	}
	
	function detail()
	{

		$noteID		= HTTP::_GP('id', 0);
		
		if(!empty($noteID)) {
            $db = Database::get();

            $sql = "SELECT * FROM %%NOTES%% WHERE id = :noteID AND owner = :userID;";
            $noteDetail = $db->selectSingle($sql, array(
                ':userID'   => $this->user->id,
                ':noteID'   => $noteID
            ));
		} else {
			$noteDetail	= array(
				'id'		=> 0,
				'priority'	=> 1,
				'text'		=> '',
				'title'		=> ''
			);
		}

		$this->assign(array(
			'PriorityList'	=> array(2 => $this->lang['nt_important'], 1 => $this->lang['nt_normal'], 0 => $this->lang['nt_unimportant']),
			'noteDetail'	=> $noteDetail,
		));
		
		$this->display('page.notes.detail');
	}
	
	public function insert()
	{
		$priority 	= HTTP::_GP('priority', 1);
		$title 		= HTTP::_GP('title', '', true);
		$text 		= HTTP::_GP('text', '', true);
		$id			= HTTP::_GP('id', 0);	
		$title 		= !empty($title) ? $title : $this->lang['nt_no_title'];
		$text 		= !empty($text) ? $text : $this->lang['nt_no_text'];

        $db = Database::get();

		if($id == 0) {
			$sql = "INSERT INTO %%NOTES%% SET owner = :userID, time = :time, priority = :priority, title = :title, text = :text, universe = :universe;";
            $db->insert($sql, array(
                ':userID'   => $this->user->id,
                ':time'     => TIMESTAMP,
                ':priority' => $priority,
                ':title'    => $title,
                ':text'     => $text,
                ':universe' => Universe::current()
            ));
        } else {
			$sql	= "UPDATE %%NOTES%% SET time = :time, priority = :priority, title = :title, text = :text WHERE id = :noteID;";
            $db->update($sql, array(
                ':noteID'   => $id,
                ':time'     => TIMESTAMP,
                ':priority' => $priority,
                ':title'    => $title,
                ':text'     => $text,
            ));
        }
		
		$this->redirectTo('game.php?page=notes');
	}
	
	function delete()
	{

		$deleteIds	= HTTP::_GP('delmes', array());
		$deleteIds	= array_keys($deleteIds);
		$deleteIds	= array_filter($deleteIds, 'is_numeric');

		if(empty($deleteIds))
		{
            $sql = 'DELETE FROM %%NOTES%% WHERE id IN ('.implode(', ', $deleteIds).') AND owner = :userID;';
			Database::get()->delete($sql, array(
                ':userID'   => $this->user->id,
            ));
		}
		$this->redirectTo('game.php?page=notes');
	}

}