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
        parent::display($cachable, $urlparams);
    }

    public function signup()
    {
        header('Content-Type: application/json');

        $response = array();

        if (!JSession::checkToken()) {
            $response['html']  = 'Invalid Token';
            $response['error'] = true;
            echo json_encode($response);
            exit;
        }

        require_once JPATH_ADMINISTRATOR . '/components/com_mailigen/helpers/MailigenApi.php';

        $params   = JComponentHelper::getParams('com_mailigen');
        $apikey   = $params->get('params.apikey');
        $Mailigen = new MailigenApi();

        if (!$apikey || !$Mailigen->pingMailigen()) {

            $response['html']  = 'No Mailigen API key';
            $response['error'] = true;
            echo json_encode($response);
            exit;
        }

        // set Itemid so we can retrieve the correct module parameters below
        $this->app->input->set('Itemid', $this->app->input->getUint('itemId', ''));

        jimport('joomla.application.module.helper');

        $module       = JModuleHelper::getModule('mod_mailigensignup', $this->app->input->getString('title', ''));
        $moduleParams = new JRegistry();
        $moduleParams->loadString($module->params);

        $listId = $moduleParams->get('listid');

        $user = JFactory::getUser();

        $email = ($user->id ? $user->email : $this->app->input->get('email', '', 'String'));
        $name  = ($user->id ? $user->name : $this->app->input->get('name', '', 'String'));

        $merge_vars          = array();
        $merge_vars['EMAIL'] = $email;

        if (strlen(trim($name)) > 0) {

            // split name into first and last name
            $nameParts = explode(' ', $name);

            if (count($nameParts) > 1) {

                $firstName = $nameParts[0];
                unset($nameParts[0]);
                $lastName = implode(' ', $nameParts);

                $merge_vars['FNAME'] = $firstName;
                $merge_vars['LNAME'] = $lastName;
            } else {
                $firstName           = $nameParts[0];
                $merge_vars['FNAME'] = $firstName;
            }
        }

        $email_type      = 'html';
        $double_optin    = ((int) $moduleParams->get('double_optin') === 0 ? false : true);
        $update_existing = ((int) $moduleParams->get('update_existing') === 0 ? false : true);
        $send_welcome    = ((int) $moduleParams->get('send_welcome') === 0 ? false : true);

        if ($this->getModel('subscriptions')->isSubscribed($listId, $email)) {
            $update = true;
        } else {
            $update = false;
        }

        $response['email']           = $email;
        $response['email_type']      = $email_type;
        $response['merge_vars']      = $merge_vars;
        $response['double_optin']    = $double_optin;
        $response['update_existing'] = $update_existing;
        $response['send_welcome']    = $send_welcome;

        // subscribe user
        try {
            $this->db->transactionStart();

            $mailigenApi = $this->getModel('subscriptions')->getMgObject();

            $retval = $mailigenApi->listSubscribe($listId, $email, $merge_vars, $email_type, $double_optin, $update_existing, $send_welcome);

            $query = $this->db->getQuery(true)
                ->select($this->db->qn('userid'))
                ->from($this->db->qn('#__mailigen'))
                ->where($this->db->qn('email') . ' = ' . $this->db->q($email))
                ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId));

            $userIdSubscribed = $this->db->setQuery($query)->loadResult();

            if ($userIdSubscribed === null) {

                $query = $this->db->getQuery(true)
                    ->insert($this->db->qn('#__mailigen'))
                    ->set($this->db->qn('email') . ' = ' . $this->db->q($email))
                    ->set($this->db->qn('listid') . ' = ' . $this->db->q($listId));
                if ($user->id) {
                    $query->set($this->db->qn('userid') . ' = ' . $this->db->q($user->id));
                }

                $this->db->setQuery($query)->execute();

            }else if( (int) $userIdSubscribed === 0){

                $query = $this->db->getQuery(true)
                    ->update($this->db->qn('#__mailigen'))
                    ->set($this->db->qn('email') . ' = ' . $this->db->q($email))
                    ->set($this->db->qn('listid') . ' = ' . $this->db->q($listId))
                    ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId))
                    ->where($this->db->qn('email') . ' = ' . $this->db->q($email));
                if ($user->id) {
                    $query->set($this->db->qn('userid') . ' = ' . $this->db->q($user->id));
                }

                $this->db->setQuery($query)->execute();

            }

            $this->db->transactionCommit();

            $response['html']  = ($update) ? $moduleParams->get('updated_text') : $moduleParams->get('thankyou_text');
            $response['error'] = false;

        } catch (Exception $e) {
            $this->db->transactionRollback();

            $response['html']  = $e->getMessage();
            $response['error'] = true;
        }

        echo json_encode($response);

    }

}
