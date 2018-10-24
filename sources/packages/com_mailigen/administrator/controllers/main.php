<?php

// no direct access
defined('_JEXEC') or die('Restricted Access');

class MailigenControllerMain extends MailigenController
{

    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function save()
    {
        $mailigenApiKey = trim($this->input->getString('apikey'));

        if (!$mailigenApiKey) {
            $this->app->enqueueMessage(JText::_('MAILIGEN_INVALID_API_CLIENT_ID'), 'error');
        } else {

            $db    = JFactory::getDBO();
            $query = $db->getQuery(true)
                ->select($db->qn('params'))
                ->from($db->qn('#__extensions'))
                ->where($db->qn('element') . ' = ' . $db->q('com_mailigen'));
            $db->setQuery($query);
            $parameters = $db->loadResult();

            $parameters                 = json_decode($parameters);
            $parameters->params->apikey = $mailigenApiKey;
            $parameters                 = json_encode($parameters);

            $query = $db->getQuery(true);
            $query->update('#__extensions')
                ->set($db->qn('params') . ' = ' . $db->q($parameters))
                ->where($db->qn('element') . ' = ' . $db->q('com_mailigen'));
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (Exception $e) {
                $this->app->enqueueMessage('Database error: ' . $e->getMessage(), 'error');
                $this->app->redirect('index.php?option=com_mailigen&view=main');
            }

        }

        $this->app->redirect('index.php?option=com_mailigen&view=main');
    }

}
