<?php

// no direct access
defined('_JEXEC') or die('Restricted Access');


if (!class_exists('mgModel')) {
    require_once('mgModel.php');
}

class MailigenModelSync extends mgModel {

    private $data;
    private $total = null;
    private $pagination = null;

    private static $listMergeFields = null;
    private static $customFields = null;
    private static $interestCategories = null;

    public function __construct() {
        parent::__construct();

    }

    private function buildQuery() {

        $query =  'SELECT a.*, ug.title AS groupname'
            . ' FROM #__users AS a'
            . ' INNER JOIN #__user_usergroup_map AS um ON um.user_id = a.id'
            . ' INNER JOIN #__usergroups AS ug ON ug.id = um.group_id'
            // . $where
            . ' ORDER BY a.id';

        return $query;
    }

    public function getData() {
        if (empty($this->data)) {
            $query = $this->buildQuery();
            $this->data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->data;
    }

    public function getUser($id) {
        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('u.id', 'u.name', 'u.username', 'u.email', 'u.block', 'g.title'),
                array('id', 'name', 'username', 'email', 'block', 'usergroup')))
            ->from($this->db->qn('#__users', 'u'))
            ->join('LEFT', $this->db->qn('#__user_usergroup_map') . ' AS m ON (u.id = m.user_id)')
            ->join('LEFT', $this->db->qn('#__usergroups') . ' AS g ON (g.id = m.group_id)')
            ->where($this->db->qn('u.id') . ' = ' . $this->db->q($id));

        return $this->db->setQuery($query)->loadObject();
    }

    public function getUserParams($userId, $listId) {
        if (!(int)$userId) {
            throw new Exception('INVALID REQUEST');
        }

        $user = $this->getUser($userId);

        // bail out if user not found or user id blocked
        if (!$user) {
            throw new Exception('User not found');
        } elseif($user->block == 1) {
            throw new Exception('User is blocked');
        }

        $params = array(
            'email_address' => $user->email,
            'email_type'    => 'html',
            'status'        => 'subscribed'
        );

        // split first and last name
        // name
        $names = explode(' ', $user->name);
        if (count($names) > 1) {
            $params['merge_fields']['FNAME'] = $names[0];
            unset($names[0]);
            $params['merge_fields']['LNAME'] = implode(' ', $names);
        } else {
            $params['merge_fields']['FNAME'] = $user->name;
        }

        return $params;
    }

    public function getTotalUsers() {
        $query = $this->db->getQuery(true)
            ->select('COUNT(' . $this->db->qn('id') . ')')
            ->from($this->db->qn('#__users'))
            ->where($this->db->qn('block') . ' = ' . $this->db->q(0));

        return $this->db->setQuery($query)->loadResult();
    }

    public function getTotal() {
        if (empty($this->total)) {
            $query = $this->buildQuery();
            $this->total = $this->_getListCount($query);
        }

        return $this->total;
    }

    public function getPagination() {
        if (empty($this->pagination)) {
            jimport('joomla.html.pagination');
            $this->pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->pagination;
    }

    public function getGroups() {

        require_once(JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php');
        return UsersHelper::getGroups();
    }

}
