<?php
/**
 * @copyright   Copyright (c) 2018 Mailigen. All rights reserved.
 * @license     GNU General Public License version 3 or later.
 */

// no direct access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.filepsystem.file');

class pkg_mailigenInstallerScript
{

    public function preflight($type, $parent)
    {

        $db = JFactory::getDbo();
        // get the table list
        $tables = $db->getTableList();
        // get prefix
        $prefix = $db->getPrefix();

    }

    private function _executeQuery($query)
    {
        $db = JFactory::getDbo();
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            // do nothing. we dont want to fail the install process.
            echo $e;
        }
    }

}
