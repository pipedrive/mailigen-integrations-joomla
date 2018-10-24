<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

// Determine if the mailigen component is installed and enabled
jimport('joomla.filesystem.file');
jimport('joomla.application.component.helper');

if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_mailigen/mailigen.php')
    || !JComponentHelper::isEnabled('com_mailigen', true)) {
    echo JText::_('MOD_MAILIGEN_PLEASE_INSTALL_MAILIGEN');
    return;
} else if (!$params->get('listid')) {
    // not list selected in the module configuration
    echo '<p style="color: red; font-weight: 600;">'.JText::_('MOD_MAILIGEN_PLEASE_SELECT_A_LIST_IN_THE_CONFIG').'</p>';
    return;
}

$mailigenParams = JComponentHelper::getParams('com_mailigen');
$mailigenApikey = $mailigenParams->get('params.apikey');

if (!$mailigenApikey) {

    echo JText::_('MOD_MAILIGEN_INVALID_OR_EMPTY_KEY');
    return;
}

// Include the Mailigen helper
JLoader::register('ModMailigenHelper', __DIR__ . '/helper.php');

$document = JFactory::getDocument();

$mailigenSignupBaseUrl           = JUri::root();
$mailigenSignupErrorInvalidEmail = JText::_('MOD_MAILIGEN_EMAIL_ERROR', 'Please enter a valid email address.');

$style='

    .mailigenSignupModule .mailigenIntro,
    .mailigenSignupModule .mailigenTextAboveSubmit,
    .mcSignupModule .mailigenTextBelowSubmit {
        margin-bottom: 0.5em;
    }

    .mailigenSignupModule input[type="text"] {
        margin-bottom: 5px;
        width: 95%;
    }

    .mailigenSignupModule .mailigenAjaxLoader {
        text-align: -webkit-center;
    }
';

$document->addStyleDeclaration($style);


$script = 'jQuery.noConflict();$j = jQuery.noConflict();
    !function($){
        $(document).ready(function(){

        var mailigenSignupBaseUrl="' . $mailigenSignupBaseUrl . '";
        var mailigenSignupErrorInvalidEmail="' . $mailigenSignupErrorInvalidEmail . '";

        $(".mailigenSignupSubmit").click(function() {
            var moduleId = $(this).data("id");
            var module = $("#mailigenSignupModule_" + moduleId);
            // Validate email address with regex
            if (!mailigenSignupCheckEmail(module.find("input[name=email]").val())) {
                alert(mailigenSignupErrorInvalidEmail);
                return;
            }
            // Submit the form
            mailigenSignupSubmit(module);
        });

        function mailigenSignupCheckEmail(email) {
            var pattern = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return pattern.test(email);
        }

        function mailigenSignupSubmit(module) {
            var moduleId = module.data("id");
            $.ajax({
                url: mailigenSignupBaseUrl + "index.php?option=com_mailigen&task=signup&format=raw",
                type: "post",
                dataType: "json",
                data: module.find("form").serialize(),
                beforeSend: function() {
                    module.find(".mailigenIntro").css("display", "none");
                    module.find(".mailigenSignupFormWrapper").css("display", "none");
                    module.find(".mailigenAjaxLoader").css("display", "block");
                },
                success: function(response) {
                    module.find(".mailigenAjaxLoader").css("display", "none");
                    module.find(".mailigenSignupResult").html(response.html).css("display", "block");
                }
            });
        }
        });
    }(jQuery);';

$document->addScriptDeclaration($script);

// load language files. include en-GB as fallback
$jlang = JFactory::getLanguage();
$jlang->load('mod_mailigen', JPATH_SITE, 'en-GB', true);
$jlang->load('mod_mailigen', JPATH_SITE, $jlang->getDefault(), true);
$jlang->load('mod_mailigen', JPATH_SITE, null, true);

require JModuleHelper::getLayoutPath('mod_mailigen', $params->get('layout', 'default'));
