<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.error');
jimport('joomla.html.parameter');
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');

if (version_compare(JVERSION, '3.0', 'ge')) {
    class mgControllerWrapper extends JControllerLegacy {

        public function __construct() {
            parent::__construct($config = array());
        }

        public function display($cachable = false, $urlparams = array()) {
            parent::display($cachable, $urlparams);
        }
    }
} else {
    class mgControllerWrapper extends JController {
        public function display($cachable = false, $urlparams = false) {
            parent::display($cachable, $urlparams);
        }
    }
}

class mgController extends mgControllerWrapper {

    protected $app;
    protected $input;
    protected $db;
    protected $session;

    public function __construct() {
        parent::__construct($config = array());

        $this->app = JFactory::getApplication();
        $this->input = $this->app->input;
        $this->db = JFactory::getDBO();
        $this->session = JFactory::getSession();
    }

    public function display($cachable = false, $urlparams = array()) {
        try {
            parent::display($cachable, $urlparams);
        } catch (Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');
            $this->app->redirect('index.php?option=com_mailigen');
        }
    }
}
