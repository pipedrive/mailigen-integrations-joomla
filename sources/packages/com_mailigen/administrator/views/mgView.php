<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

if (version_compare(JVERSION, '3.0', 'ge')) {
    class mgViewHelper extends JViewLegacy
    {
        public function __construct($config = array())
        {
            parent::__construct($config);
        }
    }
} else {
    class mgViewHelper extends JView
    {
        public function __construct($config = array())
        {
            parent::__construct($config);
        }
    }
}

class mgView extends mgViewHelper
{

    protected $app;
    protected $input;
    protected $db;
    protected $session;
    public $sidebar = '';

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->app     = JFactory::getApplication();
        $this->input   = $this->app->input;
        $this->db      = JFactory::getDBO();
        $this->session = JFactory::getSession();
    }

    public function display($tpl = null)
    {

        $this->createSidebar();

        $this->sidebar = '<div id="j-sidebar-container" class="span2">' . JHtmlSidebar::render() . '</div>'
            . '<div id="j-main-container" class="span10">';

        parent::display($tpl);
    }

    public function getPageTitleClass()
    {
        return (version_compare(JVERSION, '3.0', 'ge')) ? 'mc_title_logo' : 'mc_title_logo_25';
    }

    public function getModelInstance($model)
    {
        if (version_compare(JVERSION, '3.0', 'ge')) {
            return JModelLegacy::getInstance($model, 'MailigenModel');
        } else {
            return JModel::getInstance($model, 'MailigenModel');
        }
    }

    private function createSidebar()
    {
        // create meta menu
        $ext = JFactory::getApplication()->input->getWord('view', 'main');

        // if (in_array($ext, array('subscribers', 'joomailermailchimpintegration'))) {
        //     $ext = 'lists';
        // }

        $subMenu = array();
        $subMenu['MAILIGEN_DASHBOARD'] = 'main';
        $subMenu['MAILIGEN_LISTS'] = 'lists';

        foreach ($subMenu as $name => $extension) {

            JHtmlSidebar::addEntry(JText::_($name), 'index.php?option=com_mailigen&view=' . $extension, $extension == $ext);
        }
    }
}
