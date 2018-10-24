<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class MailigenViewLists extends mgView
{

    public function display($tpl = null)
    {

        $this->params = JComponentHelper::getParams('com_mailigen');
        $apikey       = $this->params->get('params.apikey');        
        $Mailigen     = new MailigenApi();

        // Set the toolbar Title
        JToolBarHelper::title(JText::_('COM_MAILIGEN_TITLE_LISTS'));

        // get lists
        $this->lists = $this->get('Lists');

        // "add users to list" button
        if ($apikey && $Mailigen->pingMailigen() && !empty($this->lists)) {

            JToolBarHelper::custom('addUsers', 'plus', 'plus', 'MAILIGEN_ADD_USERS', false);
            JToolBarHelper::spacer();
            
        }


        // Display the template
        parent::display($tpl);

    }
}
