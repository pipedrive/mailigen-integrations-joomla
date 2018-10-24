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
<form action="index.php" method="post" id="adminForm">
  <?php
    if (empty($this->lists)) {
        echo '<h2>' . JText::_('MAILIGEN_CREATE_A_LIST') . '</h2></form>';
        return;
    } else { ?>      
    <div id="">

        <table class="table table-striped">
          <thead>
              <tr>
            <th>#</th>

            <th nowrap="nowrap">
                <?php echo JText::_('MAILIGEN_LIST_NAME'); ?>
            </th>
            <th>
                <?php echo JText::_('MAILIGEN_LIST_MEMBER_COUNT'); ?>
            </th>
             <th>
                <?php echo JText::_('MAILIGEN_LIST_UNSUBSCRIBE_COUNT'); ?>
            </th>
             <th>
                <?php echo JText::_('MAILIGEN_LIST_DEFAULT_FROM_NAME'); ?>
            </th>
             <th>
                <?php echo JText::_('MAILIGEN_LIST_DEFAULT_FROM_EMAIL'); ?>
            </th>
             <th>
                <?php echo JText::_('MAILIGEN_LIST_DEFAULT_SUBJECT'); ?>
            </th>
             <th>
                <?php echo JText::_('MAILIGEN_LIST_DATE_CREATED'); ?>
            </th>
              </tr>
          </thead>

            <tbody>

          <?php

          $k = 0;
            foreach ($this->lists as $index => $list) {

              $checked = JHTML::_('grid.id', $index, $list['id']); ?>

              <tr class="<?php echo "row$k"; ?>">
                <td align="center">
                    <?php echo $index+1; ?>
                </td>
                <td nowrap="nowrap">
                    <a href="index.php?option=com_mailigen&view=subscribers&listid=<?php echo $list['id'];?>&type=s">
                      <?php echo $list['name']; ?>
                    </a>
                </td>

                <td align="center">
                    <?php echo $list['member_count']; ?>
                </td>

                <td align="center">
                    <?php echo $list['unsubscribe_count']; ?>
                </td> 

                <td align="center">
                    <?php echo $list['default_from_name']; ?>
                </td> 

                <td align="center">
                    <?php echo $list['default_from_email']; ?>
                </td> 

                <td align="center">
                    <?php echo $list['default_subject']; ?>
                </td>

                <td align="center">
                    <?php echo date('d/m/Y H:i A', strtotime($list['date_created'])); ?>
                </td>

              </tr><?php
              $k = 1 - $k;
          } ?>
            </tbody>
      </table>
    </div>

    <?php } ?>
    
    <input type="hidden" name="option" value="com_mailigen" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="lists" />
</form>

<?php echo $this->sidebar ? '</div>' : ''; ?>
