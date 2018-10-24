<?php

// no direct access
defined('_JEXEC') or die('Restricted Access');

if (!class_exists('mgModel')) {
    require_once('mgModel.php');
}

class MailigenModelLists extends mgModel {

    public function getLists() {

        $cacheGroup = 'mailigenMisc';
        $cacheID = 'Lists';

        // if (!$this->caching || !$this->cache($cacheGroup)->get($cacheID, $cacheGroup)) {

            $data = $this->getMgObject()->lists();

        //     if ($this->caching) {
        //         $this->cache($cacheGroup)->store(json_encode($data), $cacheID, $cacheGroup);
        //     } else {
                return $data;
        //     }
        // }

        // return json_decode($this->cache($cacheGroup)->get($cacheID, $cacheGroup), true);
    }

    public function getListsForEmail($email) {
        return $this->getMgObject()->listsForEmail($email);
    }


}
