<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.error');
jimport('joomla.html.parameter');
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');

if (version_compare(JVERSION, '3.0', 'ge')) {
    class mgControllerWrapper extends JControllerLegacy
    {

        public function __construct()
        {
            parent::__construct($config = array());
        }

        public function display($cachable = false, $urlparams = array())
        {
            parent::display($cachable, $urlparams);
        }
    }
} else {
    class mgControllerWrapper extends JController
    {
        public function display($cachable = false, $urlparams = false)
        {
            parent::display($cachable, $urlparams);
        }
    }
}

class mgController extends mgControllerWrapper
{
    protected $app;
    protected $db;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->app = JFactory::getApplication();
        $this->db  = JFactory::getDBO();
    }

    public function display($cachable = false, $urlparams = false)
    {
        try {
            parent::display($cachable, $urlparams);
        } catch (Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');
            $this->app->redirect('index.php?option=com_mailigen');
        }
    }

}
