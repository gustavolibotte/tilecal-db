<!doctype html>
<html lang="en">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>TileCal Electronics Database Equipment Index </title>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
</head>

<body>
<div class="container mt-4">
<h1>Equipment Index</h1>
  <div class="mt-3">
    <table class="table table-bordered" id="equipments-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Model</th>
          <th>Location</th>
          <th>Status</th>
          <th>Parent ID</th>
          <th>Comments</th>
        </tr>
      </thead>
      <tbody>
          <?php if($equipments): ?>
          <?php foreach($equipments as $equipment): ?>
          <tr>
            <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('equipments/view/%s', $equipment->id)); ?>"><?php echo $equipment->id; ?></a></td>
            <td><?php echo $equipment->modelLabel; ?></td>
            <td><?php echo $equipment->location; ?></td>
            <td><?php echo $equipment->status; ?></td>
            <?php if (is_null($equipment->parentID)): ?>
              <td><?php echo $equipment->parentID; ?></td>
            <?php else: ?>
              <td><a type="button" class="btn btn-secondary" href="<?php echo base_url(sprintf('equipments/view/%s', $equipment->parentID)); ?>"><?php echo $equipment->parentID; ?></a></td>
            <?php endif; ?>
            <td><?php echo $equipment->comments; ?></td>
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
<script type="text/javascript" src="<?=base_url('assets/js/equipment/index.js')?>" ></script>
</body>
</html>