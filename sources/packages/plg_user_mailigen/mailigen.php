<?php

defined('_JEXEC') or die('Restricted Access');

class PlgUserMailigen extends JPlugin
{

    private static $MG       = null;
    private static $oldEmail = null;
    protected $app;
    protected $db;
    protected $api;
    protected $debug;
    protected $listId;
    protected $autoloadLanguage = true;

    public function __construct(&$subject, $config)
    {
        // Determine if the mailigen component is installed and enabled
        jimport('joomla.filesystem.file');
        jimport('joomla.application.component.helper');

        // if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_mailigen/mailigen.php')
        //     || !JComponentHelper::isEnabled('com_mailigen', true)) {
        //     return;
        // }

        parent::__construct($subject, $config);
        JFormHelper::addFieldPath(__DIR__ . '/fields');

    }

    private function getApi()
    {
        if (!PlgUserMailigen::$MG) {

            $params = JComponentHelper::getParams('com_mailigen');
            $apikey = $params->get('params.apikey');

            require_once JPATH_ADMINISTRATOR . '/components/com_mailigen/libraries/mailigen/MGAPI.class.php';

            PlgUserMailigen::$MG = new MGAPI($apikey);
        }

        return PlgUserMailigen::$MG;
    }

    public function onContentPrepareData($context, $data)
    {
        // Check we are manipulating a valid form.
        if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile'))) {
            return true;
        }

        if (is_object($data)) {

            $userId = isset($data->id) ? $data->id : 0;

            if (!isset($data->mailigen) && $userId > 0) {

                $this->db = JFactory::getDBO();
                $user     = JFactory::getUser($userId);

                // check if user is subscribed
                $query = $this->db->getQuery(true)
                    ->select(1)
                    ->from($this->db->qn('#__mailigen'))
                    ->where($this->db->qn('userid') . ' = ' . $this->db->q($userId))
                    ->where($this->db->qn('listid') . ' = ' . $this->db->q($this->params->get('listid')));
                try {
                    $isSubscribed = ($this->db->setQuery($query)->loadResult() ? 1 : 0);
                } catch (Exception $e) {
                    $isSubscribed = false;
                }

                $data->mailigen['subscribe'] = $isSubscribed;
                if (!JHtml::isRegistered('users.subscribe')) {
                    JHtml::register('users.subscribe', array(__CLASS__, 'subscribe'));
                }

                if (!$isSubscribed) {
                    return;
                }

            }
        }

        return true;
    }

    /**
     * adds additional fields to the user editing form
     *
     * @param   JForm  $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function onContentPrepareForm($form, $data)
    {

        if (!($form instanceof JForm)) {
            $this->_subject->setError('JERROR_NOT_A_FORM');
            return false;
        }

        if (!$this->params->get('listid')) {
            if (JFactory::getConfig()->get('debug')) {
                $this->_subject->setError('No list selected in Mailigen user plugin config!');
                return false;
            }

            return;
        }

        $params = JComponentHelper::getParams('com_mailigen');
        $apikey = $params->get('params.apikey');

        if (!$apikey) {
            return;
        }

        // Check we are manipulating a valid form.
        $name = $form->getName();
        if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration'))) {
            return true;
        }

        // Add the registration fields to the form.
        JForm::addFormPath(__DIR__ . '/profiles');
        $form->loadFile('profile', false);

        return true;
    }

    public static function subscribe($value)
    {
        return JText::_(($value ? 'JYES' : 'JNO'));
    }

    public function onUserBeforeSave($oldUser, $isNew, $newUser)
    {
        self::$oldEmail = $oldUser['email'];
    }

    public function onUserAfterSave($data, $isNew, $success, $error)
    {
        if (!$this->params->get('listid') || !$success) {
            return;
        }

        $this->app = JFactory::getApplication();
        $this->db  = JFactory::getDBO();

        $option = $this->app->input->getCmd('option');
        $task   = $this->app->input->getCmd('task');

        $listId = $this->params->get('listid');
        $userId = Joomla\Utilities\ArrayHelper::getValue($data, 'id', 0, 'int');

        $email = $data['email'];

        if (($option == 'com_users' && $task == 'activate')) {

            $query = $this->db->getQuery(true)
                ->select($this->db->qn(array('fname', 'lname', 'email')))
                ->from($this->db->qn('#__mailigen_signup'))
                ->where($this->db->qn('email') . ' = ' . $this->db->q($email));
            try {
                $res = $this->db->setQuery($query)->loadObject();
            } catch (Exception $e) {}
            if (!$res) {
                return;
            }

            // subscribe the user
            try {

                $merge_vars = array(
                    'EMAIL' => $res->email,
                    'FNAME' => $res->fname,
                    'LNAME' => $res->lname,
                );

                $email_type      = 'html';
                $double_optin    = ((int) $this->params->get('double_optin') === 0 ? false : true);
                $update_existing = ((int) $this->params->get('update_existing') === 0 ? false : true);
                $send_welcome    = ((int) $this->params->get('send_welcome') === 0 ? false : true);

                $retval = $this->getApi()->listSubscribe($listId, $email, $merge_vars, $email_type, $double_optin, $update_existing, $send_welcome);

                $query = $this->db->getQuery(true)
                    ->delete($this->db->qn('#__mailigen_signup'))
                    ->where($this->db->qn('email') . ' = ' . $this->db->q($email));
                $this->db->setQuery($query)->execute();

                $query = $this->db->getQuery(true)
                    ->insert($this->db->qn('#__mailigen'))
                    ->set($this->db->qn('userid') . ' = ' . $this->db->q($userId))
                    ->set($this->db->qn('email') . ' = ' . $this->db->q($email))
                    ->set($this->db->qn('listid') . ' = ' . $this->db->q($listId));
                $this->db->setQuery($query)->execute();

            } catch (Exception $e) {
                $this->_subject->setError("Unable to subscribe to the newsletter list!\n\tCode=" . $e->getCode()
                    . "\n\tMsg=" . $e->getMessage() . "\n");

                return;
            }

            return;
        }

        // process registration / profile form
        $jform = $this->app->input->get('jform', array(), 'RAW');

        if (!isset($jform['mailigen'])) {
            return;
        }

        $subscribe = $jform['mailigen']['subscribe'];
        $name      = $jform['name'];

        // Check if the user is already activated and is subscribed
        $isSubscribed = false;

        if (!$data['activation'] && $data['email'] && !empty(self::$oldEmail)) {

            require_once JPATH_ADMINISTRATOR . '/components/com_mailigen/models/subscriber.php';
            require_once JPATH_ADMINISTRATOR . '/components/com_mailigen/models/lists.php';

            $subscriberModel = new MailigenModelSubscriber();
            $userLists       = $subscriberModel->getListsForEmail(self::$oldEmail);

            if (count($userLists) > 0) {
                foreach ($userLists as $list) {
                    if ($list == $listId) {
                        $isSubscribed = true;
                        break;
                    }
                }
            }
        }

        // User wishes to subscribe/update interests
        if ($subscribe == 1) {

            // split name into first and last name
            $nameParts = explode(' ', $name);
            $firstName = $nameParts[0];
            unset($nameParts[0]);
            $lastName = implode(' ', $nameParts);

            // If this is a new user then just store details now and subscribe the user later at activation
            if ($data['activation']) {

                $query = $this->db->getQuery(true)
                    ->insert($this->db->qn('#__mailigen_signup'))
                    ->set(array(
                        $this->db->qn('fname') . ' = ' . $this->db->q($firstName),
                        $this->db->qn('lname') . ' = ' . $this->db->q($lastName),
                        $this->db->qn('email') . ' = ' . $this->db->q($email),
                    ));
                try {
                    $this->db->setQuery($query)->execute();

                } catch (Exception $e) {}

            } else if ($task != 'saveregisters') {

                $merge_vars = array(
                    'EMAIL' => $email,
                    'FNAME' => $firstName,
                    'LNAME' => $lastName,
                );

                $email_type      = 'html';
                $double_optin    = ((int) $this->params->get('double_optin') === 0 ? false : true);
                $update_existing = ((int) $this->params->get('update_existing') === 0 ? false : true);
                $send_welcome    = ((int) $this->params->get('send_welcome') === 0 ? false : true);

                if ($isSubscribed === false) {

                    // subscribe the user
                    $retval = $this->getApi()->listSubscribe($listId, $email, $merge_vars, $email_type, $double_optin, $update_existing, $send_welcome);

                    $query = $this->db->getQuery(true)
                        ->insert($this->db->qn('#__mailigen'))
                        ->set(array(
                            $this->db->qn('userid') . ' = ' . $this->db->q($userId),
                            $this->db->qn('email') . ' = ' . $this->db->q($email),
                            $this->db->qn('listid') . ' = ' . $this->db->q($listId),
                        ));
                    try {
                        $this->db->setQuery($query)->execute();
                    } catch (Exception $e) {}

                } else {

                    // update the users subscription
                    if ($email != self::$oldEmail) {

                        // update local database entry
                        $query = $this->db->getQuery(true)
                            ->update($this->db->qn('#__mailigen'))
                            ->set($this->db->qn('email') . ' = ' . $this->db->q($email))
                            ->where($this->db->qn('email') . ' = ' . $this->db->q(self::$oldEmail))
                            ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId));
                        try {
                            $this->db->setQuery($query)->execute();
                        } catch (Exception $e) {}

                        // $params['email_address_old'] = self::$oldEmail;
                    }

                    // subscribe the user
                    $retval = $this->getApi()->listSubscribe($listId, $email, $merge_vars, $email_type, $double_optin, $update_existing, $send_welcome);
                }
            }

            // user wishes to unsubscribe
        } else if (!$subscribe && $isSubscribed) {

            $delete_member = true;
            $send_goodbye  = true;
            $send_notify   = true;

            // unsubscribe the user
            $retval = $this->getApi()->listUnsubscribe($listId, $email, $delete_member, $send_goodbye, $send_notify);

            // remove local database entry
            $query = $this->db->getQuery(true)
                ->delete($this->db->qn('#__mailigen'))
                ->where($this->db->qn('email') . ' = ' . $this->db->q($email))
                ->where($this->db->qn('listid') . ' = ' . $this->db->q($listId));

            try {
                $this->db->setQuery($query)->execute();
            } catch (Exception $e) {}
        }

        return true;
    }

}
