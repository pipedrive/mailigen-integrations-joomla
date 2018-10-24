<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

if (version_compare(JVERSION, '3.0', 'ge')) {
    class mgModelHelper extends JModelLegacy {
        public function __construct($config = array()) {
            parent::__construct($config);
        }
        public static function addIncludePath($path = '', $prefix = 'MailigenModel') {
            return parent::addIncludePath($path, $prefix);
        }
    }
} else {
    class mgModelHelper extends JModel {
        public function __construct($config = array()) {
            parent::__construct($config);
        }
        public static function addIncludePath($path = '', $prefix = 'MailigenModel') {
            return parent::addIncludePath($path, $prefix);
        }
    }
}

class mgModel extends mgModelHelper {

    public static $MG = null;
    public static $cache = array();
    protected $caching = false;
    protected $app;
    protected $input;
    protected $db;

    public function __construct($config = array()) {
        parent::__construct($config);

        $this->app = JFactory::getApplication();
        $this->input = $this->app->input;
        $this->db = JFactory::getDBO();

        $this->caching = !(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'mailigen.loc');
    }

    public function getMgObject() {

        if (mgModel::$MG === null) {

            $params = JComponentHelper::getParams('com_mailigen');
            
            $apikey = $params->get('params.apikey');

            require_once(JPATH_ADMINISTRATOR . '/components/com_mailigen/libraries/mailigen/MGAPI.class.php');
            mgModel::$MG = new MGAPI($apikey);
        }

        return mgModel::$MG;
    }

    public function getModel($model) {
        if (version_compare(JVERSION, '3.0', 'ge')) {
            return JModelLegacy::getInstance($model, 'MailigenModel');
        } else {
            return JModel::getInstance($model, 'MailigenModel');
        }
    }

    public function cache($cacheGroup) {
        if (!isset(mgModel::$cache[$cacheGroup])) {
            jimport('joomla.cache.cache');
            $cacheOptions = array();
            $cacheOptions['caching'] = true;
            $cacheOptions['cachebase'] = JPATH_ADMINISTRATOR . '/cache';
            $cacheOptions['defaultgroup'] = $cacheGroup;
            $cacheOptions['storage'] = 'file';
            $cacheOptions['lifetime'] = 60;
            $cacheOptions['locking'] = false;

            mgModel::$cache[$cacheGroup] = new JCache($cacheOptions);
        }

        return mgModel::$cache[$cacheGroup];
    }

    /**
     * Caching enabled?
     * @return bool
     */
    public function isCaching() {
        return $this->caching;
    }
}
