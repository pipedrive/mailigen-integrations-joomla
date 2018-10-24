<?php

// no direct access
defined('_JEXEC') or die('Restricted Access');

class MailigenModelSubscriptions extends mgModel {

    public function getLists() {
        require_once(JPATH_ADMINISTRATOR . '/components/com_mailigen/models/lists.php');
        $listsModel = new MailigenModelLists();

        return $listsModel->getLists();
    }

    public function isSubscribed($listId, $email) {
        try {

            $res = $this->getMgObject()->listMemberInfo($listId, $email);

            return ($res['status'] == 'subscribed');

        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}
