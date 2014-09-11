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
 * @info $Id: ShowAlliancePage.class.php 2776 2013-08-05 21:30:40Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowUpgradePage extends AbstractInstallPage
{
    public function show()
    {
        // Willkommen zum Update page. Anzeige, von und zu geupdatet wird. Informationen, dass ein backup erstellt wird.
        require_once('includes/config.php');

        try {
            $sqlRevision = Config::get()->sql_revision;
        }
        catch (Exception $e) {
            $template->message($LNG['upgrade_required_rev'], false, 0, true);
            exit;
        }

        $fileList = array();

        $directoryIterator = new DirectoryIterator(ROOT_PATH . 'install/updates/');
        /** @var $fileInfo DirectoryIterator */
        foreach ($directoryIterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $fileRevision = substr($fileInfo->getFilename(), 7, -4);
            if ($fileRevision > $sqlRevision) {
                $fileList[] = (int)$fileRevision;
            }
        }
        sort($fileList);
        $template->assign_vars(array(
            'revisionlist'  => $fileList,
            'file_revision' => empty($fileList) ? $sqlRevision : max($fileList),
            'sql_revision'  => $sqlRevision,
            'header'        => $LNG['menu_upgrade']
        ));

        $this->display('page.update.default');
    }

    public function execute()
    {
        // TODO:Need a rewrite!
        require 'includes/config.php';
        $startRevision       = HTTP::_GP('startrevision', 0);

        // Create a Backup
        $prefixCounts = strlen(DB_PREFIX);
        $dbTables     = array();
        $sqlTableRaw  = Database::get()->nativeQuery("SHOW TABLE STATUS FROM `" . DB_NAME . "`;");
        foreach($sqlTableRaw as $table)
        {
            if (DB_PREFIX == substr($table['Name'], 0, $prefixCounts)) {
                $dbTables[] = $table['Name'];
            }
        }

        if (empty($dbTables))
        {
            throw new Exception('No tables found for dump.');
        }

        $fileName = '2MoonsBackup_' . date('d_m_Y_H_i_s', TIMESTAMP) . '.sql';
        $filePath = 'includes/backups/' . $fileName;
        require 'includes/classes/SQLDumper.php';
        $dump = new SQLDumper;
        $dump->dumpTablesToFile($dbTables, $filePath);
        @set_time_limit(600);
        $httpRoot = PROTOCOL . HTTP_HOST . str_replace(array('\\', '//'), '/', dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/');
        $revision = $startRevision - 1;
        $fileList = array();
        $directoryIterator = new DirectoryIterator(ROOT_PATH . 'install/updates/');
        /** @var $fileInfo DirectoryIterator */
        foreach ($directoryIterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $fileRevision = substr($fileInfo->getFilename(), 7, -4);
            if ($fileRevision > $revision) {
                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
                $key           = $fileRevision . ((int)$fileExtension === 'php');
                $fileList[$key] = array(
                    'fileName'      => $fileInfo->getFilename(),
                    'fileRevision'  => $fileRevision,
                    'fileExtension' => $fileExtension
                );
            }
        }
        ksort($fileList);
        if (!empty($fileList) && !empty($revision)) {
            foreach ($fileList as $fileInfo) {
                switch ($fileInfo['fileExtension']) {
                    case 'php':
                        copy('install/updates/' . $fileInfo['fileName'], $fileInfo['fileName']);
                        $ch = curl_init($httpRoot . $fileInfo['fileName']);
                        curl_setopt($ch, CURLOPT_HEADER, false);
                        curl_setopt($ch, CURLOPT_NOBODY, true);
                        curl_setopt($ch, CURLOPT_MUTE, true);
                        curl_exec($ch);
                        if (curl_errno($ch)) {
                            $errorMessage = 'CURL-Error on update ' . basename($fileInfo['filePath']) . ':' . curl_error($ch);
                            try {
                                $dump->restoreDatabase($filePath);
                                $message = 'Update error.<br><br>' . $errorMessage . '<br><br><b><i>Backup restored.</i></b>';
                            }
                            catch (Exception $e) {
                                $message = 'Update error.<br><br>' . $errorMessage . '<br><br><b><i>Can not restore backup. Your game is maybe broken right now.</i></b><br><br>Restore error:<br>' . $e->getMessage();
                            }
                            throw new Exception($message);
                        }
                        curl_close($ch);
                        unlink($fileInfo['fileName']);
                        break;
                    case 'sql';
                        $data = file_get_contents(ROOT_PATH . 'install/updates/' . $fileInfo['fileName']);
                        try {
                            $queries	= explode(';', str_replace("prefix_", DB_PREFIX, $data));
                            $queries	= array_filter($queries);
                            foreach($queries as $query)
                            {
                                Database::get()->nativeQuery($query);
                            }
                        }
                        catch (Exception $e) {
                            $errorMessage = $e->getMessage();
                            try {
                                $dump->restoreDatabase($filePath);
                                $message = 'Update error.<br><br>' . $errorMessage . '<br><br><b><i>Backup restored.</i></b>';
                            }
                            catch (Exception $e) {
                                $message = 'Update error.<br><br>' . $errorMessage . '<br><br><b><i>Can not restore backup. Your game is maybe broken right now.</i></b><br><br>Restore error:<br>' . $e->getMessage();
                            }
                            throw new Exception($message);
                        }
                        break;
                }
            }
            $revision = end($fileList);
            $revision = $revision['fileRevision'];
        }
        $gameVersion    = explode('.', Config::get(ROOT_UNI)->VERSION);
        $gameVersion[2] = $revision;
        Database::get()->update("UPDATE %%CONFIG%% SET VERSION = '" . implode('.', $gameVersion) . "', sql_revision = " . $revision . ";");
        ClearCache();
        $template->assign_vars(array(
            'update'   => !empty($fileList),
            'revision' => $revision,
            'header'   => $LNG['menu_upgrade'],));
        $template->show('ins_doupdate');
    }
}