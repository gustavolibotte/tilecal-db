<table class="table table-bordered" id="equipment-table-related">
  <thead>
    <tr>
      <th>Relation</th>
      <th>Slot</th>
      <th>ID</th>
      <th>Type</th>
      <th>Location</th>
      <th>Status</th>
      <th>Owner</th>
      <th>Serial</th>
      <th>Comments</th>
      <th>Updated at</th>
    </tr>
  </thead>
  <tbody>
      <?php if($equipments): ?>
      <?php foreach($equipments as $equipment): ?>
      <tr>
        <td> <? echo ucfirst($equipment->relation_type); ?></td>
        <td> <? echo snake2Capitalized($equipment->slot_name); ?></td>
        <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('equipments/view/%s', $equipment->id)); ?>"><?php echo $equipment->id; ?></a></td>
        <td><?php echo $equipment->type_label; ?></td>
        <td><?php echo $equipment->location; ?></td>
        <td><?php echo $equipment->status; ?></td>
        <td><?php echo $equipment->owner_name; ?></td>
        <td><?php echo $equipment->serial; ?></td>
        <td><?php echo $equipment->comments; ?></td>
        <td><?php echo $equipment->updated_at; ?></td>
      </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>