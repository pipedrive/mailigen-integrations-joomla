<?php

// no direct access
defined('_JEXEC') or die('Restricted Access');

class MailigenControllerSubscribers extends MailigenController {

	public function __construct($config = array()) {
		parent::__construct($config);        
	}

    public function goToLists() {
        $this->setRedirect('index.php?option=com_mailigen&view=lists');
    }

}
