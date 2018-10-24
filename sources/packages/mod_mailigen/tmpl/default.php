<?php

defined('_JEXEC') or die('Restricted access');

$app  = JFactory::getApplication();
$user = JFactory::getUser();
$uri  = JURI::getInstance();
$rand = rand(1000, 9999);

$email     = ($user->id ? $user->email : '');
$name      = ($user->id ? $user->name : '');
$firstName = '';
$lastName  = '';

if (strlen(trim($name)) > 0) {

    // split name into first and last name
    $nameParts = explode(' ', $name);

    if (count($nameParts) > 1) {
        $firstName = $nameParts[0];
        unset($nameParts[0]);
        $lastName = implode(' ', $nameParts);
    } else {
        $firstName = $nameParts[0];
    }
}

$fields     = array();
$mod_fields = $params->get('fields');

if (is_string($mod_fields)) {
    $mod_fields = json_decode($mod_fields);
    if (count($mod_fields)) {
        foreach ($mod_fields as $index => $value) {
            $fields[$index] = json_encode($value);
        }
    }
} else {
    if (count($mod_fields)) {
        foreach ($mod_fields as $index => $value) {
            $fields[$index] = $value;
        }
    }
}

?>

<div id="mailigenSignupModule_<?php echo $rand; ?>" data-id="<?php echo $rand; ?>" class="mailigenSignupModule <?php echo $params->get('moduleclass_sfx', ''); ?>">

    <?php if ($params->get('intro_text', 0)) {?>
        <div class="mailigenIntro"><?php echo JText::_($params->get('intro_text')); ?> </div>
    <?php }?>

    <div class="mailigenSignupFormWrapper">

        <form action="<?php echo $uri->toString(array('scheme', 'host', 'port', 'path', 'query')); ?>" method="post" id="mailigenSignupForm_<?php echo $rand; ?>" class="mailigenSignupForm" name="mailigenSignupForm<?php echo $rand; ?>" onsubmit="return false;">

        <input type="text" name="email" class="" value="<?php echo $email; ?>" title="<?php echo JText::_('MOD_MAILIGEN_FIELD_EMAIL', 'Email Address') . ' *'; ?>" placeholder="<?php echo JText::_('MOD_MAILIGEN_FIELD_EMAIL', 'Email Address') . ' *'; ?>" required="required"
        <?php echo (strlen($email) > 0 ? ' readonly="readonly" ' : ''); ?> >

        <?php if (in_array('name', $fields)) {?>
            <input type="text" name="name" class="" value="<?php echo (strlen($name) > 0 ? $name : ''); ?>" title="<?php echo JText::_('MOD_MAILIGEN_FIELD_NAME', 'Name'); ?>" placeholder="<?php echo JText::_('MOD_MAILIGEN_FIELD_NAME', 'Name'); ?>">
        <?php }?>

        <?php if (in_array('first_name', $fields)) {?>
            <input type="text" name="first_name" class="" value="<?php echo (strlen($firstName) > 0 ? $firstName : ''); ?>" title="<?php echo JText::_('MOD_MAILIGEN_FIELD_FIRST_NAME', 'First Name'); ?>" placeholder="<?php echo JText::_('MOD_MAILIGEN_FIELD_FIRST_NAME', 'First Name'); ?>">
        <?php }?>

        <?php if (in_array('last_name', $fields)) {?>
            <input type="text" name="last_name" class="" value="<?php echo (strlen($lastName) > 0 ? $lastName : ''); ?>" title="<?php echo JText::_('MOD_MAILIGEN_FIELD_LAST_NAME', 'Last Name'); ?>" placeholder="<?php echo JText::_('MOD_MAILIGEN_FIELD_LAST_NAME', 'Last Name'); ?>">
        <?php }?>

        <?php if ($params->get('text_above_submit')): ?>
            <div id="text_above_submit_<?php echo $rand; ?>" class="mailigenTextAboveSubmitContainer">
                <div class="mailigenTextAboveSubmit"><?php echo JText::_($params->get('text_above_submit')); ?></div>
            </div>
        <?php endif;?>
        <div>
            <input type="button" class="btn btn-primary mailigenSignupSubmit" value="<?php echo JText::_('MOD_MAILIGEN_SUBSCRIBE'); ?>" data-id="<?php echo $rand; ?>">
        </div>

        <?php if ($params->get('text_below_submit')): ?>
            <div id="text_below_submit_<?php echo $rand; ?>" class="mailigenTextBelowSubmitContainer">
                <div class="mailigenTextBelowSubmit"><?php echo JText::_($params->get('text_below_submit')); ?></div>
            </div>
        <?php endif;?>

        <input type="hidden" name="uid" value="<?php echo $user->id; ?>">
        <input type="hidden" name="ip" value="<?php echo @$_SERVER['REMOTE_ADDR']; ?>">
        <input type="hidden" name="itemId" value="<?php echo $app->input->getInt('Itemid'); ?>">
        <input type="hidden" name="title" value="<?php echo htmlspecialchars($module->title); ?>">
        <?php echo JHtml::_('form.token'); ?>

        </form>
    </div>

    <div class="mailigenAjaxLoader" style="display: none;">
        <img src="<?php echo JURI::root(); ?>media/mod_mailigen/images/ajax-loader.gif" alt="Please wait">
    </div>
    <div class="mailigenSignupResult" style="display:none;"></div>

</div>
