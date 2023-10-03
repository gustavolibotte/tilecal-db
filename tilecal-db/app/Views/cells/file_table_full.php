<table class="table table-bordered" id="file-table-full">
  <thead>
    <tr>
      <th>Filename</th>
      <th>Description</th>
      <th>Created</th>
      <th>Last update</th>
      <th>Download</th>
    </tr>
  </thead>
  <tbody>
      <?php if($files): ?>
      <?php foreach($files as $file): ?>
      <tr>
        <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('files/view/%s', $file->id)); ?>"><?php echo $file->name; ?></a></td>
        <td><?php echo $file->description; ?></td>
        <td><?php echo $file->created_at; ?></td>
        <td><?php echo $file->updated_at; ?></td>
        <td><a type="button" class="btn btn-secondary" href="<? echo base_url(sprintf('files/%s', $file->id)); ?>"><i class="bi bi-download"></i></a></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
</table>
