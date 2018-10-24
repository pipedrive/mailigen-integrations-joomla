<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class MailigenViewSubscribers extends mgView
{

    public function display($tpl = null)
    {
        $this->params = JComponentHelper::getParams('com_mailigen');
        $apikey       = $this->params->get('params.apikey');        
        $Mailigen     = new MailigenApi();

        // Set the toolbar Title
        JToolBarHelper::title(JText::_('COM_MAILIGEN_TITLE_SUBSCRIBERS'));

        if ($apikey && $Mailigen->pingMailigen()) {
          
            JToolBarHelper::custom('goToLists', 'back', 'back', 'COM_MAILIGEN_BACK_TO_LISTS', false, false);
            JToolBarHelper::spacer();
            
        }

        $this->lists = $this->get('Lists');        
        $this->members = $this->get('Members');

        // Display the template
        parent::display($tpl);

    }
}
