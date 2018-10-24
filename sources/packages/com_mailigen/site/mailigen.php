<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// register classes to make sure we are using ours in case of naming conflicts
JLoader::register('mgModel', JPATH_COMPONENT_SITE . '/models/mgModel.php', true);
JLoader::register('mgController', JPATH_COMPONENT_SITE . '/controllers/mgController.php', true);
JLoader::register('mgView', JPATH_COMPONENT_SITE . '/views/mgView.php', true);

require_once JPATH_COMPONENT_ADMINISTRATOR . '/libraries/mailigen/MGAPI.class.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/MailigenApi.php';

// Require the base controller
require_once(JPATH_COMPONENT_SITE . '/controller.php');

$input = JFactory::getApplication()->input;

if ($input->getWord('format') != 'raw') {

  $document = JFactory::getDocument();

    $script = 'jQuery.noConflict();$j = jQuery.noConflict();
    !function($){
        $(document).ready(function(){

		(function(window) {
            var document = window.document;

            var mailigenJS = (function() {
                var $ = jQuery;

                return {
                    strings: {},
                    misc: {},
                    functions: {},
                    helpers: {}
                };

            })();

            window.mailigenJS = mailigenJS;
        })(window);


        });
    }(jQuery);';
    $document->addScriptDeclaration($script);


}

// Require specific controller if requested
$controller = $input->getCmd('controller');

if ($controller) {
    $path = JPATH_COMPONENT_SITE . '/controllers/' . $controller . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
}

// Create the controller
$classname  = 'MailigenController' . $controller;
$controller = new $classname();

// Perform the Request task
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
