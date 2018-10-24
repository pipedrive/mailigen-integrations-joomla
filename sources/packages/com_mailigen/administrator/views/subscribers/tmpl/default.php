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

$listid = $this->input->getString('listid', 0);
foreach($this->lists as $list) {
    if ($list['id'] == $listid) {
        $listName = $list['name'];
        break;
    }
}

$type = $this->input->getString('type', 's');
switch ($type) {
    case 's':
        $state = JText::_('MAILIGEN_ACTIVE_SUBSCRIBERS');
        break;
    case 'u':
        $state = JText::_('MAILIGEN_SUBSCRIBERS') . ' ' . JText::_('MAILIGEN_STATE_UNSUBSCRIBED');
        break;
    case 'c':
        $state = JText::_('MAILIGEN_SUBSCRIBERS') . ' ' . JText::_('MAILIGEN_STATE_CLEANED');
        break;
} 

?>

<h3><?php echo $listName;?> - <?php echo count($this->members) . ' ' . $state;?></h3>

<form action="index.php" method="post" name="adminForm" id="adminForm">
        
<?php if (!count($this->members)) {
    echo '<p>' . JText::_('MAILIGEN_RECIPIENT_LIST_EMPTY') . '</p>';
    } else { ?> 

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
                                <?php echo JText::_('MAILIGEN_EMAIL_ADDRESS'); ?>
                            </th>
                            <th style="text-align:left;">
                                <?php echo JText::_('MAILIGEN_ADDED_AT'); ?>
                            </th>
                        </tr>
                    </thead>
                    <?php
                    $k = 0;
                    for ($i = 0, $n = count($this->members); $i < $n; $i++) {
                        $row = $this->members[$i];

                        $checked = JHTML::_('grid.id', $i, $row['id']);

                        ?>

                        <tr class="<?php echo "row$k"; ?>" id="row_<?php echo $row['id'];?>">
                            <td>
                                <?php echo $row['id']; ?>
                            </td>
                            <td>
                                <?php echo $checked;?>
                            </td>
                            <td style="text-align:left">
                                <?php echo $row['email']; ?>
                            </td>
                              <td>
                                <?php echo $row['timestamp'];?>
                            </td>
                        </tr>
                        <?php
                        $k = 1 - $k;
                    }
                    ?>
                </table>

            </div>

    <?php } ?>
    
        <input type="hidden" name="option" value="com_mailigen" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" id="boxchecked" value="0" />
        <input type="hidden" name="controller" id="controller" value="subscribers" />
        <input type="hidden" name="type" value="sync" />
    </form>

<?php echo $this->sidebar ? '</div>' : ''; ?>
