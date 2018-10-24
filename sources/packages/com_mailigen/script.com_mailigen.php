<?php

defined('_JEXEC') or die('Restricted access');
jimport('joomla.filepsystem.file');

class com_mailigenInstallerScript
{

    /**
     * The list of extra modules and plugins to install on component installation / update and remove on component
     * uninstallation.
     *
     * @var   array
     */
    public function preflight($type, $parent)
    {

        $db = JFactory::getDbo();
        // get the table list
        $tables = $db->getTableList();
        // get prefix
        $prefix = $db->getPrefix();

        if (!in_array($prefix . 'mailigen', $tables)) {

            $query = "CREATE TABLE IF NOT EXISTS `#__mailigen` (
                        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                        `userid` int(11) unsigned NOT NULL ,
                        `email` varchar(50) NOT NULL,
                        `listid` varchar(25) NOT NULL,
                      PRIMARY KEY (`id`),                      
                      UNIQUE KEY `mgUseridListidEmail` (`userid`,`email`,`listid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;";
            $this->_executeQuery($query);
        }

        if (!in_array($prefix . 'mailigen_signup', $tables)) {

            $query = "CREATE TABLE IF NOT EXISTS `#__mailigen_signup` (
                        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                        `fname` varchar(100),
                        `lname` varchar(100),
                        `email` varchar(100) NOT NULL,
                      PRIMARY KEY (`id`)                      
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;";
            $this->_executeQuery($query);
        }


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
