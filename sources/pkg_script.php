<?php

defined('_JEXEC') or die('Restricted access');
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

     //    if (!in_array($prefix . 'smartupsell_rules', $tables)) {
     //        $query = "CREATE TABLE IF NOT EXISTS `#__smartupsell_rules` (
					//     `smartupsell_rule_id` int(11) NOT NULL AUTO_INCREMENT,
					// 	`product_id` int(11) NOT NULL ,
					// 	`products_to_show` longtext,
					// 	`discount` DECIMAL(5,2),
					// 	`applicable` int(11) NOT NULL DEFAULT '0',                        
     //                    `short_description` text,     
					// 	`status` varchar(100),
					//   PRIMARY KEY (`smartupsell_rule_id`)
					// ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;";
     //        $this->_executeQuery($query);
     //    }

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
