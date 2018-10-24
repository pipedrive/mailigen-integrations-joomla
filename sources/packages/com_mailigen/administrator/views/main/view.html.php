<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Main View
 *
 * @since  0.0.1
 */
class MailigenViewMain extends mgView
{
    /**
     * Display the Hello World view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {

        $this->params = JComponentHelper::getParams('com_mailigen');
        $apikey       = $this->params->get('params.apikey');
        $Mailigen     = new MailigenApi();

        $accountDetails = $Mailigen->pingMailigen();

        // Set the toolbar Title
        JToolbarHelper::title(JText::_('COM_MAILIGEN'));

        $user = JFactory::getUser();
        if ($user->authorise('core.admin', 'com_mailigen')) {
            JToolBarHelper::preferences('com_mailigen', '450');
            JToolBarHelper::spacer();
        }

        $this->accountDetails = $accountDetails;

        // Display the template
        parent::display($tpl);
    }
}
