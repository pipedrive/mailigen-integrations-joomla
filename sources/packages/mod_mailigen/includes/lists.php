<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

class JFormFieldlists extends JFormField {

    public function getInput() {

        $mainframe = JFactory::getApplication();

        jimport('joomla.filesystem.file');
        jimport('joomla.application.component.helper');

        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_mailigen/mailigen.php')
            || !JComponentHelper::isEnabled('com_mailigen', true)) {

            $mainframe->enqueueMessage(JText::_('MOD_MAILIGEN_PLEASE_INSTALL_MAILIGEN'), 'error');
            $mainframe->redirect('index.php?option=com_modules');
        }

        require_once(JPATH_ADMINISTRATOR . '/components/com_mailigen/helpers/MailigenApi.php');

        $params = JComponentHelper::getParams('com_mailigen');
        $apikey = $params->get('params.apikey');

        $Mailigen = new MailigenApi();

        if (!$apikey || !$Mailigen->pingMailigen()) {
            $mainframe->enqueueMessage(JText::_('APIKEY ERROR'), 'error');
            $mainframe->redirect('index.php?option=com_mailigen&view=main');
        }

        require_once(JPATH_ADMINISTRATOR . '/components/com_mailigen/models/lists.php');
        $listsModel = new MailigenModelLists();
        $lists = $listsModel->getLists();

        $options = array();
        $options[] = array(
            'id'   => '',
            'name' => '-- ' . JText::_('MOD_MAILIGEN_PLEASE_SELECT_A_LIST') . ' --'
        );
        foreach ($lists as $list) {
            $options[] = array(
                'id'   => $list['id'],
                'name' => $list['name']
            );
        }

        $attribs = 'onchange="submitbutton(\'module.apply\')"';
        return JHtml::_('select.genericlist', $options, 'jform[params][listid]', $attribs, 'id', 'name', $this->value, $this->id);
    }
}
