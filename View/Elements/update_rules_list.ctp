<?php foreach( $alias as $k => $v ){?>
    <tr id="action_<?php echo $k; ?>">
      <td>
        <?php
        if ( isset($v['id']) )
          echo $this->Form->hidden("Action.{$k}.id", array('label'=>false, 'value'=>$v['id']));

          echo $this->Form->input("Action.{$k}.alias", array('label'=>false, 'value'=>$v['alias'], 'class'=>'form-control'));
        ?>
      </td>
      <td>
        <?php  echo $this->Form->checkbox("Action.{$k}.alow", array('hiddenField' => true )  ); ?>
      </td>

      <td class="actions">
        <?php echo $this->Html->Link(__('Delete'), "javascript:removeTr({$k})", array('class' => "btn btn-danger"), __('Are you sure you want to delete this action: %s?', $k['alias'])); ?>
      </td>
    </tr>
<?php } ?>