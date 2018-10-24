<?php

// no direct access
defined('_JEXEC') or die('Restricted Access');

if (!class_exists('mgModel')) {
    require_once 'mgModel.php';
}
class MailigenModelSubscriber extends mgModel
{

    private static $data;
    protected $_total     = null;
    protected $pagination = null;

    public function __construct()
    {
        parent::__construct();

    }

    private function buildQuery()
    {

        $query = $this->db->getQuery(true);
        $query->select($this->db->qn(array('id', 'name', 'username', 'email', 'block', 'usertype')))
            ->from($this->db->qn('#__users'))
            ->order($this->db->qn('id'));

        return $query;

    }

    public function getData()
    {

        // Lets load the data if it doesn't already exist
        if (empty(MailigenModelSubscriber::$data)) {
            $query                         = $this->buildQuery();
            MailigenModelSubscriber::$data =
            $this->getList($query);
        }
        return MailigenModelSubscriber::$data;
    }

    public function getUser($id)
    {

        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('id', 'name', 'username', 'email', 'block', 'usertype')))
            ->from($this->db->qn('#__users'))
            ->where($this->db->qn('id') . ' = ' . $this->db->q($id));

        return $this->getList($query);
    }

    public function getListsForEmail($email)
    {
        // $email = str_replace(' ', '+', $email);

        return $this->getModel('lists')->getListsForEmail($email);
    }

    public function getListMemberInfo($listId, $email)
    {
        return $this->getMgObject()->listMember($listId, $email);
    }

    public function getSubscribed()
    {
        $query = $this->db->getQuery(true)
            ->select('*')
            ->from($this->db->qn('#__mailigen'));
        $res = $this->getList($query);

        return $res;
    }

    public function getUsers()
    {
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from($this->db->qn('#__users'));
        $res = $this->getList($query);

        return $res;
    }

    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     *
     * @param boole $img True to return a complete IMG tag False for just the URL
     * @param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
     * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @param array $atts Optional, additional key/value attributes to include in the IMG tag
     * @return String containing either just a URL or a complete image tag
     * @source http://gravatar.com/site/implement/images/php/
     */
    public function getGravatar($default = '', $img = false, $s = 155, $d = 'mm', $r = 'g', $atts = array())
    {
        $email = str_replace(' ', '+', $this->input->getString('email'));
        $url   = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($default) {
            $url .= '&amp;default=' . urlencode($default);
        }
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val) {
                $url .= ' ' . $key . '="' . $val . '"';
            }
            $url .= ' />';
        }

        return $url;
    }

}
