<?php

defined('_JEXEC') or die('Restricted Access');

class PlgSystemMailigen extends JPlugin
{
    private static $MG = null;
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

        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_mailigen/mailigen.php')
            || !JComponentHelper::isEnabled('com_mailigen', true)) {
            return;
        }

        parent::__construct($subject, $config);
        JFormHelper::addFieldPath(__DIR__ . '/fields');

    }

    private function getApi()
    {
        if (!PlgSystemMailigen::$MG) {

            $params = JComponentHelper::getParams('com_mailigen');
            $apikey = $params->get('params.apikey');

            require_once JPATH_ADMINISTRATOR . '/components/com_mailigen/libraries/mailigen/MGAPI.class.php';

            PlgSystemMailigen::$MG = new MGAPI($apikey);
        }

        return PlgSystemMailigen::$MG;
    }

    /**
     * adds additional fields to the contact form
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

        // Check we are manipulating a valid form.
        $name = $form->getName();

        if (!in_array($name, array('com_contact.contact'))) {
            return true;
        }

        if (!$this->params->get('listid')) {
            if (JFactory::getConfig()->get('debug')) {
                $this->_subject->setError('No list selected in "' . JText::_('PLG_SYSTEM_MAILIGEN') . '" plugin config!');
                return false;
            }

            return;
        }

        $params = JComponentHelper::getParams('com_mailigen');
        $apikey = $params->get('params.apikey');

        if (!$apikey) {
            return;
        }

        // Add the newsletter fields to the form.
        JForm::addFormPath(__DIR__ . '/contacts');
        $form->loadFile('contact', false);

        return true;
    }

    public static function subscribe($value)
    {
        return JText::_(($value ? 'JYES' : 'JNO'));
    }

    public function onSubmitContact($contact, $data)
    {
        if (!$this->params->get('listid')) {
            return;
        }

        $this->app = JFactory::getApplication();
        $this->db  = JFactory::getDBO();

        $option = $this->app->input->getCmd('option');
        $task   = $this->app->input->getCmd('task');

        $listId = $this->params->get('listid');
        $userId = Joomla\Utilities\ArrayHelper::getValue($data, 'id', 0, 'int');

        if (($option == 'com_contact' && $task == 'submit')) {

            // process form
            $jform = $this->app->input->get('jform', array(), 'RAW');

            if (!isset($jform['mailigen'])) {
                return;
            }

            $subscribe = $jform['mailigen']['subscribe'];
            $name      = $jform['contact_name'];
            $email     = $jform['contact_email'];

            // split name into first and last name
            $nameParts = explode(' ', $name);
            $firstName = $nameParts[0];
            unset($nameParts[0]);
            $lastName = implode(' ', $nameParts);

            // Check if the user is already subscribed
            $isSubscribed = false;

            if ($jform['contact_email'] && !empty($jform['contact_email'])) {

                require_once JPATH_ADMINISTRATOR . '/components/com_mailigen/models/subscriber.php';
                require_once JPATH_ADMINISTRATOR . '/components/com_mailigen/models/lists.php';

                $subscriberModel = new MailigenModelSubscriber();
                $userLists       = $subscriberModel->getListsForEmail($jform['contact_email']);

                if (count($userLists) > 0) {
                    foreach ($userLists as $list) {
                        if ($list == $listId) {
                            $isSubscribed = true;
                            break;
                        }
                    }
                }
            }

            if (!$isSubscribed && $subscribe == 1) {

                // subscribe the user
                try {

                    $merge_vars = array(
                        'EMAIL' => $email,
                        'FNAME' => $firstName,
                        'LNAME' => $lastName,
                    );

                    $email_type      = 'html';
                    $double_optin    = ((int) $this->params->get('double_optin') === 0 ? false : true);
                    $update_existing = ((int) $this->params->get('update_existing') === 0 ? false : true);
                    $send_welcome    = ((int) $this->params->get('send_welcome') === 0 ? false : true);

                    $retval = $this->getApi()->listSubscribe($listId, $email, $merge_vars, $email_type, $double_optin, $update_existing, $send_welcome);

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
            }

            return;
        }
    }

}
