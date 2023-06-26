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

<!-- <div class="d-flex justify-content-end">
        <button  class="btn btn-primary" id="reg-eq-button" data-bs-toggle="modal" data-bs-target="#reg-eq-modal">
          Register Equipment
        </button>
	</div>
  <div class="modal fade" id="reg-eq-modal" tabindex="-1" aria-labelledby="reg-eq-modal-label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="reg-eq-modal-label">Upload file to the database</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="register-equipment-form" action=<? echo base_url('/register-equipment') ?> method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
              <label for="equipment-id" class="col-form-label">ID:</label>
              <input name="id" type="text" class="form-control" id="equipment-id-input" placeholder="Equipment barcode number">
            </div>
            <div class="mb-3">
              <label for="model-name" class="col-form-label">Model:</label>
              <input name="model_name" type="text" class="form-control" id="model-name-input" placeholder="Equipment model name">
            </div>
            <div class="mb-3">
            <label for="location-field" class="col-form-label">Location:</label>
              <input name="location" type="text" class="form-control" id="location-input" placeholder="Equipment location (175 for building 175)">
            </div>
            <div class="mb-3">
            <label for="status-field" class="col-form-label">Status:</label>
              <input name="status" type="text" class="form-control" id="status-input" placeholder="Installed, tested, assembled ...">
            </div>
            <div class="mb-3">
            <label for="parent-id" class="col-form-label">Parent ID:</label>
              <input name="parent_id" type="text" class="form-control" id="parent-id-input" placeholder="Parent equipment barcode number" value="">
            </div>
            <div class="modal-footer">
              <button type="button" form="register-equipment-form" class="btn btn-danger"  data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success" id="register-equipment-form-submit-button" data-bs-dismiss="modal" aria-pressed="true">Register</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div> -->
  <?php
     if(isset($_SESSION['msg'])){
        echo $_SESSION['msg'];
      }
     ?>
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
            <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('equipments/view/%s', $equipment['id'])); ?>"><?php echo $equipment['id']; ?></a></td>
            <td><?php echo $equipment['model']; ?></td>
            <td><?php echo $equipment['location']; ?></td>
            <td><?php echo $equipment['status']; ?></td>
            <?php if (is_null($equipment['parent_id'])): ?>
              <td><?php echo $equipment['parent_id']; ?></td>
            <?php else: ?>
              <td><a type="button" class="btn btn-secondary" href="<?php echo base_url(sprintf('equipments/view/%s', $equipment['parent_id'])); ?>"><?php echo $equipment['parent_id']; ?></a></td>
            <?php endif; ?>
            <td><?php echo $equipment['comments']; ?></td>
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
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- App JS modules -->
<script type="text/javascript" src="<?=base_url('assets/js/equipment/index.js')?>" ></script>
</body>
</html>