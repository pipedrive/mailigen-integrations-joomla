<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class MailigenController extends mgController
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function display($cachable = false, $urlparams = array())
    {
        if ($this->input->getCmd('view') == '') {
            $this->input->set('view', 'main');
        }

        parent::display($cachable, $urlparams);
    }

}
