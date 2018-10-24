<?php

// no direct access
defined('_JEXEC') or die('Restricted Access');

class MailigenControllerLists extends MailigenController
{

    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function addUsers()
    {
        $this->app->redirect('index.php?option=com_mailigen&view=sync');
    }

    public function cancel()
    {
        $this->app->redirect('index.php?option=com_mailigen&view=lists');
    }
}
