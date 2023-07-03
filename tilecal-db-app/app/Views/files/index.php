<!doctype html>
<html lang="en">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>TileCal Electronics Database File Index </title>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
</head>

<body>
<div class="container mt-4">
<h1>File Index</h1>
  <div class="mt-3">
    <table class="table table-bordered" id="files-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Filename</th>
          <th>Description</th>
          <th>Download</th>
        </tr>
      </thead>
      <tbody>
          <?php if($files): ?>
          <?php foreach($files as $file): ?>
          <tr>
            <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('files/view/%s', $file['id'])); ?>"><?php echo $file['id']; ?></a></td>
            <td><?php echo $file['name']; ?></td>
            <td><?php echo $file['description']; ?></td>
            <td><a type="button" class="btn btn-secondary" href="<? echo base_url(sprintf('files/%s', $file['id'])); ?>"><i class="bi bi-download"></i></a></td>
          </tr>
         <?php endforeach; ?>
         <?php endif; ?>
       </tbody>
    </table>
  </div>
</div>
 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.13.4/features/scrollResize/dataTables.scrollResize.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- App JS modules -->
<script type="text/javascript" src="<?=base_url('assets/js/files/index.js')?>" ></script>
</body>
</html>