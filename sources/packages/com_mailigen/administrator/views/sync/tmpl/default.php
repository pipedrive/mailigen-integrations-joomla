<?php

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

<div id="ajax_response" style="display: none"></div>
<div id="message" style="display: none"></div>

<div id="form_container">
    <form action="index.php?option=com_mailigen&view=sync" method="post" name="adminForm" id="adminForm">

        <?php  // no lists created yet
        if (empty($this->lists)) {
            echo JText::_('MAILIGEN_CREATE_A_LIST');
            $i = $n = 1;
        } else { ?>
            <div class="note" style="display: none">
                <table>
                    <tr>
                        <td valign="top"><?php echo JText::_('MAILIGEN_NOTE'); ?>:</td>
                        <td valign="top">
                            <?php echo JText::_('MAILIGEN_ADDING_USERS_TAKES_SOME_TIME'); ?>
                            <br />
                            <?php echo JText::_('MAILIGEN_ADDING_USERS_AGAIN_MAY_CAUSE_TROUBLE'); ?>
                        </td>
                    </tr>
                </table>
            </div>


            <div class="clearfix">
              <div class="left">

                <select name="listId" id="listId" class="left" style="width: 250px;">
                        <?php if (count($this->lists) > 1) { ?>
                            <option value=""><?php echo JText::_('MAILIGEN_SELECT_A_LIST_TO_ASSIGN_THE_USERS_TO'); ?></option>
                        <?php }
                        foreach ($this->lists as $list) { ?>
                            <option value="<?php echo $list['id'];?>"><?php echo $list['name'];?></option><?php
                        } ?>
                    </select>


              </div>
            </div>

            <hr>
            
            <div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th width="5">
                                <?php echo JText::_('MAILIGEN_ID'); ?>
                            </th>
                            <th width="20">
                                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
                            </th>
                            <th style="text-align:left;">
                                <?php echo JText::_('MAILIGEN_NAME'); ?>
                            </th>
                            <th style="text-align:left;">
                                <?php echo JText::_('MAILIGEN_USERNAME'); ?>
                            </th>
                            <th style="text-align:left;">
                                <?php echo JText::_('MAILIGEN_EMAIL_ADDRESS'); ?>
                            </th>
                            <th width="50">
                                <?php echo JText::_('MAILIGEN_ENABLED'); ?>
                            </th>
                            <th width="150">
                                <?php echo JText::_('MAILIGEN_USERGROUP'); ?>
                            </th>
                            <th width="150">
                                <?php echo JText::_('MAILIGEN_LAST_VISIT'); ?>
                            </th>
                        </tr>
                    </thead>
                    <?php
                    $k = 0;
                    for ($i = 0, $n = count($this->items); $i < $n; $i++) {
                        $row = $this->items[$i];

                        $checked = JHTML::_('grid.id', $i, $row->id . '" class=""');

                        $blocked = JText::_(($row->block == 0 ? 'JNO' : 'JYES'));

                        $user_subscribed = '';

                        ?>

                        <tr class="<?php echo "row$k"; ?>" id="row_<?php echo $row->id;?>" <?php echo $user_subscribed; ?>>
                            <td>
                                <?php echo $row->id; ?>
                            </td>
                            <td>
                                <?php echo $checked;?>
                            </td>
                            <td style="text-align:left">
                                <a href=""  id="link_<?php echo $row->id;?>" <?php echo $user_subscribed; ?>><?php echo $row->name; ?></a>
                            </td>
                            <td style="text-align:left">
                                <?php echo $row->username; ?>
                            </td>
                            <td style="text-align:left">
                                <?php echo $row->email; ?>
                            </td>
                            <td>
                                <?php echo $blocked; ?>
                            </td>
                            <td>
                                <?php echo $row->groupname; ?>
                            </td>
                            <td>
                                <?php echo ($row->lastvisitDate == '0000-00-00 00:00:00') ? JText::_('MAILIGEN_NEVER') : $row->lastvisitDate; ?>
                            </td>
                        </tr>
                        <?php
                        $k = 1 - $k;
                    }
                    ?>
                </table>

            </div>

            <?php } // end - no list created ?>

        <input type="hidden" name="option" value="com_mailigen" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" id="boxchecked" value="0" />
        <input type="hidden" name="controller" id="controller" value="sync" />
        <input type="hidden" name="type" value="sync" />
    </form>

</div>

<?php echo $this->sidebar ? '</div>' : ''; ?>
