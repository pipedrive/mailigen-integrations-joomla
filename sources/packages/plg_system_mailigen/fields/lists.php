<?php

defined('JPATH_PLATFORM') or die;

class JFormFieldLists extends JFormField
{

    public function getInput()
    {

        $this->app = JFactory::getApplication();

        jimport('joomla.filesystem.file');
        jimport('joomla.application.component.helper');

        // if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/
        //     com_mailigen/mailigen.php')
        //     || !JComponentHelper::isEnabled('com_mailigen', true)) {

        //     $this->app->enqueueMessage(JText::_('PLG_SYSTEM_MAILIGEN_PLEASE_INSTALL_MAILIGEN'), 'error');
        //     $this->app->redirect('index.php?option=com_modules');
        // }

        require_once JPATH_ADMINISTRATOR . '/components/com_mailigen/helpers/MailigenApi.php';

        $params = JComponentHelper::getParams('com_mailigen');

        $apikey = $params->get('params.apikey');

        $Mailigen = new MailigenApi();

        if (!$apikey || !$Mailigen->pingMailigen()) {

            $this->app->enqueueMessage(JText::_('APIKEY ERROR'), 'error');
            $this->app->redirect('index.php?option=com_mailigen&view=main');
        }

        require_once JPATH_ADMINISTRATOR . '/components/com_mailigen/models/lists.php';

        $listsModel = new MailigenModelLists();

        $lists = $listsModel->getLists();

        $options   = array();
        $options[] = array(
            'id'   => '',
            'name' => '-- ' . JText::_('PLG_SYSTEM_MAILIGEN_PLEASE_SELECT_A_LIST') . ' --',
        );
        foreach ($lists as $list) {
            $options[] = array(
                'id'   => $list['id'],
                'id'   => $list['id'],
                'name' => $list['name'],
            );
        }

        $attribs = 'onchange="submitbutton(\'plugin.apply\')"';
        // $attribs = '';
        return JHtml::_('select.genericlist', $options, 'jform[params][listid]', $attribs, 'id', 'name', $this->value, $this->id);
    }
}
