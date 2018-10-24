<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

JHTML::_('behavior.modal');

$apikey = $this->params->get('params.apikey');

$Mailigen = new MailigenApi();

if (!$apikey) {
    echo '<table>' . $Mailigen->apiKeyMissing();
    return;
} else if (!$Mailigen->pingMailigen()) {
    echo '<table>' . $Mailigen->apiKeyMissing(1);
    return;
}

echo $this->sidebar;

?>

<div class="container">

  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#mailigen_account_details">Account Details</a></li>
  </ul>

  <div class="tab-content">

    <div id="mailigen_account_details" class="tab-pane fade in active">

<table class="table">
    <tbody>
      <tr>
        <td><?php echo JText::_('MAILIGEN_USERNAME'); ?>:</td>
        <td><?php echo $this->accountDetails['username']; ?></td>
      </tr>
      <tr>
        <td><?php echo JText::_('MAILIGEN_PLAN_TYPE'); ?>:</td>
        <td><?php echo $this->accountDetails['plan_type'];?></td>
      </tr>
      <tr>
        <td><?php echo JText::_('MAILIGEN_LAST_LOGIN'); ?>:</td>
        <td><?php echo JHTML::_('date', date('Y-m-d H:i:s', strtotime($this->accountDetails['last_login'])), JText::_('DATE_FORMAT_LC2')); ?></td>
      </tr>
</tbody>
</table>
    </div>

  </div>
</div>

<?php echo $this->sidebar ? '</div>' : ''; ?>


