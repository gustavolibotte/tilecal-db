<table class="table table-bordered" id="file-table-side">
  <thead>
    <tr>
      <th>Filename</th>
      <th>Updated</th>
    </tr>
  </thead>
  <tbody>
      <?php if($files): ?>
      <?php foreach($files as $file): ?>
      <tr>
        <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('files/view/%s', $file->id)); ?>"><?php echo $file->name; ?></a></td>
        <td><?php echo $file->updatedAt; ?></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
</table>
