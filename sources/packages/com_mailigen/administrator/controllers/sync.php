<?php

// no direct access
defined('_JEXEC') or die('Restricted Access');

class MailigenControllerSync extends MailigenController
{

    public function __construct($config = array())
    {
        parent::__construct($config);
        // Register Extra tasks
        $this->registerTask('add', 'sync');
        $this->registerTask('backup', 'sync');
    }

    public function syncSelected()
    {

        $listId = $this->input->getAlnum('listId', false);
        if (!$listId) {
            $this->app->enqueueMessage(JText::_('MAILIGEN_INVALID_LISTID'), 'error');
            $this->app->redirect('index.php?option=com_mailigen&view=sync');
        }

        // total number of elements to process
        $elements = $this->input->getUint('boxchecked', 0);
        if (!$elements) {
            $this->app->enqueueMessage(JText::_('MAILIGEN_NO_USERS_SELECTED'), 'error');
            $this->app->redirect('index.php?option=com_mailigen&view=sync');
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->qn('userid'))
            ->from($this->db->qn('#__mailigen'))
            ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId));
        $this->db->setQuery($query);
        $alreadySubscribed = $this->db->loadColumn();

        $cid = $this->input->get('cid', array());
        if (!count($cid)) {
            $cid = $this->input->get('cid[]', array());
        }

        $errors = array();

        $successCount = 0;
        $failedCount  = 0;

        $is_batch_subscribe = true;

        $com_params = JComponentHelper::getParams('com_mailigen');

        $email_type   = 'html';
        $double_optin = ($com_params->get('params.double_optin') == 0 ? false : true);

        $update_existing = true;
        $send_welcome    = false;

        $batch = array();

        foreach ($cid as $id) {
            try {

                $params = $this->getModel('sync')->getUserParams($id, $listId);

                $email_address = $params['email_address'];

                $merge_vars = array(
                    'EMAIL' => $params['email_address'],
                    'FNAME' => $params['merge_fields']['FNAME'],
                    'LNAME' => $params['merge_fields']['LNAME'],
                );

                array_push($batch, $merge_vars);

                if (!$is_batch_subscribe) {

                    $mailigenApi = $this->getModel('sync')->getMgObject();

                    $retval = $mailigenApi->listSubscribe($listId, $email_address, $merge_vars, $email_type, $double_optin, $update_existing, $send_welcome);
                }

                if (!in_array($id, $alreadySubscribed)) {

                    $query = $this->db->getQuery(true)
                        ->insert($this->db->qn('#__mailigen'))
                        ->set($this->db->qn('userid') . ' = ' . $this->db->q($id))
                        ->set($this->db->qn('email') . ' = ' . $this->db->q($params['email_address']))
                        ->set($this->db->qn('listid') . ' = ' . $this->db->q($listId));
                    $this->db->setQuery($query)->execute();

                }

                if (!$is_batch_subscribe) {
                    $successCount++;
                }

            } catch (Exception $e) {
                $user = $this->getModel('sync')->getUser($id);

                $errors[] = $e->getMessage() . ' => ' . $user->email;

                $query = $this->db->getQuery(true)
                    ->delete($this->db->qn('#__mailigen'))
                    ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId))
                    ->where($this->db->qn('email') . ' = ' . $this->db->q($user->email));

                $this->db->setQuery($query)->execute();
            }
        }

        if ($is_batch_subscribe && count($batch) > 0) {

            $mailigenApi = $this->getModel('sync')->getMgObject();

            $retval = $mailigenApi->listBatchSubscribe($listId, $batch, $double_optin, $update_existing);

            $successCount = $retval['success_count'];
            $failedCount  = $retval['error_count'];

        }

        if ($successCount) {

            $this->app->enqueueMessage($successCount . ' ' . JText::_('MAILIGEN_RECIPIENTS_SAVED'));

        }

        if (count($errors)) {

            $this->app->enqueueMessage(count($errors) . ' ' . JText::_('Errors') . ': ' . implode('; ', $errors) . ')', 'error');
        }

        $this->app->redirect('index.php?option=com_mailigen&view=sync');
    }

}
