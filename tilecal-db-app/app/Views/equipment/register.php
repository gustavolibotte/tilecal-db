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
  <div class="jumbotron text-left">
    <h1>Equipment Register</h1>
  </div>
  <div class="row">
    <div class="col-3 mt-4">
      <table class="table table-bordered" id="equipments-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Model</th>
          </tr>
        </thead>
        <tbody>
          <?php if($equipments): ?>
          <?php foreach($equipments as $equipment): ?>
            <tr>
              <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('equipments/view/%s', $equipment['id'])); ?>"><?php echo $equipment['id']; ?></a></td>
              <td><?php echo $equipment['model']; ?></td>
            </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="col mt-4">
      <div class="container">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">
              Register
            </button>
          </li>
        </ul>
        <div class="tab-content" id="myTabContent">
          <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
            <form id="register-equipment-form" action=<? echo base_url('/register-equipment') ?> method="POST">
              <?= csrf_field() ?>
              <div id="parent-id-help" class="form-text">* required field</div>
              <div class="mb-3">
                <label for="equipment-id" class="col-form-label">ID*:</label>
                <input name="id" type="text" class="form-control" id="equipment-id-input" placeholder="Equipment barcode number">
              </div>
              <div class="mb-3">
                <label for="model-name" class="col-form-label">Model Name*:</label>
                <select class="form-select" aria-label="Equipment model">
                  <option selected>Choose an equipment model</option>
                  <?php foreach($models as $model): ?>
                    <option value=<? echo $model['name'] ?>> <? echo $model['formattedName']; ?> </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="model-name" class="col-form-label">Model*:</label>
                <input name="model_name" type="text" class="form-control" id="model-name-input" placeholder="Equipment model name">
              </div>
              <div class="mb-3">
              <label for="location-field" class="col-form-label">Location*:</label>
                <input name="location" type="text" class="form-control" id="location-input" placeholder="Equipment location (175 for building 175)">
              </div>
              <div class="mb-3">
              <label for="status-field" class="col-form-label">Status*:</label>
                <input name="status" type="text" class="form-control" id="status-input" placeholder="Installed, tested, assembled ...">
              </div>
              <div class="mb-3">
              <label for="parent-id" class="col-form-label">Parent ID:</label>
                <input name="parent_id" type="text" class="form-control" id="parent-id-input" placeholder="Parent equipment barcode number">
                <div id="parent-id-help" class="form-text">This id must be already registered into the database</div>
              </div>
              <label for="comments" class="col-form-label">Comments:</label>
                <textarea class="form-control" type="text" id="comments-input" rows="3" placeholder="Useful info about the equipment"></textarea>
              </div>
              <div class="modal-footer">
                <button type="button" form="register-equipment-form" class="btn btn-danger"  data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success" id="register-equipment-form-submit-button" data-bs-dismiss="modal" aria-pressed="true">Register</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.13.4/features/scrollResize/dataTables.scrollResize.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- App JS modules -->
<script type="text/javascript" src="<?=base_url('assets/js/equipment/register.js')?>" ></script>
</body>
</html>