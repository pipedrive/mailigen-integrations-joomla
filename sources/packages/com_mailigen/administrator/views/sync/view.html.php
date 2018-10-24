<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class MailigenViewSync extends mgView
{

    public function display($tpl = null)
    {
        $document = JFactory::getDocument();

        $script = "!function($){
            $(document).ready(function(){

                Joomla.submitbutton = function(pressbutton) {
                    if (pressbutton == 'mailigen') {

                        Joomla.submitform('syncSelected');
                    } else {
                        Joomla.submitform(pressbutton);
                    }
                };

            });
            }(jQuery);";

        $document->addScriptDeclaration($script);


        $this->params = JComponentHelper::getParams('com_mailigen');
        $apikey       = $this->params->get('params.apikey');
        $Mailigen     = new MailigenApi();

        // Set the toolbar Title
        JToolBarHelper::title(JText::_('MAILIGEN_ADD_USERS'));

        // "add users to list" button
        if ($apikey && $Mailigen->pingMailigen()) {

            // JToolBarHelper::custom('mailigen', 'loop', 'loop', 'MAILIGEN_ADD_TO_MAILIGEN', false, false);
            JToolBarHelper::custom('syncSelected', 'loop', 'loop', 'MAILIGEN_ADD_TO_MAILIGEN', false, false);
            JToolBarHelper::spacer();

        }

        $option = $this->input->getCmd('option');

        $this->filters = array();

        require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

        // Get data from the model
        $this->items = $this->get('Data');

        $this->setModel($this->getModelInstance('lists'));
        $this->lists    = $this->getModel('lists')->getLists();
        // $this->groups   = $this->get('Groups');

        // Display the template
        parent::display($tpl);

    }
}
