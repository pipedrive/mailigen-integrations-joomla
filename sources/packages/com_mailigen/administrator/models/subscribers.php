<?php

// no direct access
defined('_JEXEC') or die('Restricted Access');

class MailigenModelSubscribers extends mgModel {

    private static $data;
    private $pagination = null;
    protected $mainframe, $db;

    public function __construct() {
        parent::__construct();

        $this->mainframe = JFactory::getApplication();
        $this->db = JFactory::getDBO();

    }

    private function buildQuery() {

        $query = $this->db->getQuery(true);
        $query->select($this->db->qn(array('id', 'name', 'username', 'email', 'block', 'usertype')))
            ->from($this->db->qn('#__users'))
            ->order($this->db->qn('id'));

        return $query;
    }

    public function getData() {

        if (empty(MailigenModelSubscribers::$data)) {
            $query = $this->buildQuery();
            MailigenModelSubscribers::$data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return MailigenModelSubscribers::$data;
    }

    public function getUser($id) {
        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('id', 'name', 'username', 'email', 'block', 'usertype')))
            ->from($this->db->qn('#__users'))
            ->where($this->db->qn('id') . ' = ' . $this->db->q($id));

        return $this->_getList($query);
    }

    public function addJoomlaUserData(&$users) {

        // if (!count($users)) {
        //     return;
        // }
        // $emails = array();
        // foreach ($users as $user) {
        //     $emails[] = $user['email_address'];
        // }

        // $query = $this->db->getQuery(true);
        // $query->select($this->db->qn(array('id', 'email')))
        //     ->from($this->db->qn('#__users'))
        //     ->where($this->db->qn('email') . ' IN ("' . implode('","', $emails) . '")');
        // $this->db->setQuery($query);
        // $res = $this->db->loadObjectList();

        // $jUsers = array();
        // foreach ($res as $r) {
        //     $jUsers[$r->email] = $r->id;
        // }

        // foreach ($users as $index => $user) {
        //     if (isset($jUsers[$user['email_address']])) {
        //         $users[$index] = JFactory::getUser($jUsers[$user['email_address']]);
        //         $users[$index]->timestamp_opt = $user['timestamp_opt'];
        //         $users[$index]->member_rating = $user['member_rating'];
        //     } else {
        //         $tmp = new stdClass();
        //         $tmp->id = '';
        //         $tmp->name = '';
        //         $tmp->email = $user['email_address'];
        //         $tmp->timestamp_opt = $user['timestamp_opt'];
        //         $tmp->member_rating = $user['member_rating'];
        //         $users[$index] = $tmp;
        //     }
        // }
    }

    public function getSubscribed() {
        $query = $this->db->getQuery(true);
        $query->select($this->db->qn('*'))
            ->from($this->db->qn('#__mailigen'));

        return $this->_getList($query);
    }

    public function getMembers() {

        $listid = $this->input->getString('listid',  0, '', 'string');
        $type = $this->input->getString('type',  's', '', 'string');
        $option = $this->input->getCmd('option');

        $count = 500;
        $offset = 0;

        switch ($type) {
            case 's':
                $result = $this->getMgObject()->listMembers($listid, 'subscribed', $offset, $count);
                break;
            case 'u':
                $result = $this->getMgObject()->listMembers($listid, 'unsubscribed', $offset, $count);
                break;
            case 'c':
                $result = $this->getMgObject()->listMembers($listid, 'cleaned', $offset, $count);
                break;
        }

        return $result;
    }

    public function getLists() {
        return $this->getModel('lists')->getLists();
    }


    public function getPagination() {
        // Load the content if it doesn't already exist
        if (empty($this->pagination)) {
            $option = $this->input->getCmd('option');
            $limit = $this->mainframe->getUserStateFromRequest('global.list.limit', 'limit', $this->mainframe->getCfg('list_limit'), 'int');
            $limitstart = $this->mainframe->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');
            if ($limit == 0){
                $limit = 15000;
            }
            jimport('joomla.html.pagination');
            $this->pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->pagination;
    }
}
