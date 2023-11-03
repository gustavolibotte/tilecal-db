<table class="row-border" id="history-table-full">
  <thead>
    <tr>
      <th>Created At</th>
      <th>User</th>
      <th>Description</th>
    </tr>
  </thead>
  <tbody>
      <?php if($histories): ?>
      <?php foreach($histories as $history): ?>
      <tr>
        <td><b><?php echo $history->created_at->format('Y-m-d H:i:s.u');; ?></b></td>
        <td><b><?php echo $history->user; ?></b></td>
        <td><?php echo $history->message; ?></td>
      </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>