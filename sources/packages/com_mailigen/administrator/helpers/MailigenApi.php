<?php

require_once __DIR__ . '/../libraries/mailigen/MGAPI.class.php';

// no direct access
defined('_JEXEC') or die('Restricted Access');

class MailigenApi
{

    protected $session;

    public function __construct()
    {
        $this->session = JFactory::getSession();
    }

    public function pingMailigen()
    {

        if (!$this->session->has('mailigenAccountData') || ($this->session->get('mailigenAccountData')) === false) {

            jimport('joomla.html.parameter');
            jimport('joomla.application.component.helper');

            $params = JComponentHelper::getParams('com_mailigen');

            $apikey = $params->get('params.apikey');

            if (strlen(trim($apikey))) {

                $Mailigen = new MGAPI($apikey);
                try {
                    $mailigenAccountData = $Mailigen->getAccountDetails();

                    $this->session->set('mailigenAccountData', $mailigenAccountData);

                } catch (Exception $e) {
                    return false;
                }
            }
        }

        return $this->session->get('mailigenAccountData');
    }

    public function apiKeyMissing($incorrectKey = 0)
    {

        jimport('joomla.html.pane');

        $params = JComponentHelper::getParams('com_mailigen');

        $mailigenApiKey = $params->get('params.apikey');

        $html = '';

        $html .= '<h2>' . JText::_('COM_MAILIGEN_ENTER_API_KEY') . '</h2>';
        $html .= '<p>' . JText::_('COM_MAILIGEN_API_KEY_INSTRUCTIONS') . '</p>';

        $html .= '<style>
            input#apikey {
                margin-right: 10px !important;
            }
            button#btnSaveApikey {
                margin-top: -11px !important;
            }
            </style>';

        $html .= '<form class="form-inline" action="index.php?option=com_mailigen&view=main" method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-search">';

        $html .= '<input type="text" name="apikey" id="apikey" value="' . $mailigenApiKey . '" class="input-xlarge" size="45" />';

        $html .= '<button class="btn" type="button" id="btnSaveApikey" onclick="submitbutton(\'save\');">' . JText::_('COM_MAILIGEN_SAVE_API_KEY') . '</button>';

        $html .= '<input type="hidden" name="controller" value="main" />';
        $html .= '<input type="hidden" name="option" value="com_mailigen" />';
        $html .= '<input type="hidden" name="task" value="" />';
        $html .= '</form>';

        if ($incorrectKey) {
            $html .= $this->loginIncorrect();
        }

        return $html;
    }

    private function loginIncorrect()
    {
        $html = '<div style="clear:both;"></div>';
        $html .= '<div>' .
        '<h2 style="color:#ff0000;font-size:12px;"> ' . JText::_('COM_MAILIGEN_INCORRECT_API_KEY_ENTERED') . '</h2></div>';

        return $html;
    }

}
