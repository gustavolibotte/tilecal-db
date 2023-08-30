<table class="table table-bordered" id="equipment-table-full">
  <thead>
    <tr>
      <th>ID</th>
      <th>Type</th>
      <th>Location</th>
      <th>Status</th>
      <th>Owner</th>
      <th>Serial</th>
      <th>Comments</th>
      <th>Created at</th>
      <th>Updated at</th>
    </tr>
  </thead>
  <tbody>
      <?php if($equipments): ?>
      <?php foreach($equipments as $equipment): ?>
      <tr>
        <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('equipments/view/%s', $equipment->id)); ?>"><?php echo $equipment->id; ?></a></td>
        <td><?php echo $equipment->type_label; ?></td>
        <td><?php echo $equipment->location; ?></td>
        <td><?php echo $equipment->status; ?></td>
        <td><?php echo $equipment->owner_name; ?></td>
        <td><?php echo $equipment->serial; ?></td>
        <td><?php echo $equipment->comments; ?></td>
        <td><?php echo $equipment->created_at; ?></td>
        <td><?php echo $equipment->updated_at; ?></td>
      </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>