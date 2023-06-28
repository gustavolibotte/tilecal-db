<!doctype html>
<html lang="en">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><? echo $model['name']?> Register </title>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>

<body>
<div class="container mt-4 mb-5">
  <div class="row">
    <div class="jumbotron text-left">
      <h1><? echo $model['name']?> Register</h1>
    </div>
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
              <td><? echo $equipment['model']; ?></td>
            </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="col mt-4">
      <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">
            Register
          </button>
        </li>
      </ul>
      <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
          <form id="register-equipment-form" action=<?
            echo base_url(sprintf('%s/register', $model['snakeCase']))
            ?> method="POST">
            <?= csrf_field() ?>
              <div class="row">
                <div class="col mt-4" id="equipment-info-row">
                  <h4>Equipment info</h4>
                  <div id="parent-id-help" class="form-text">* required field</div>
                  <div class="mb-3">
                    <label for="equipment-id-input" class="col-form-label">ID*:</label>
                    <input name="id" type="text" class="form-control" id="equipment-id-input" placeholder="Equipment barcode number">
                  </div>
                  <div class="mb-3">
                    <label for="model-name-input" class="col-form-label">Model*:</label>
                    <input name="model_name" type="text" class="form-control" id="model-name-input" value="<? echo $model['name']?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label for="location-input" class="col-form-label">Location*:</label>
                    <input name="location" type="text" class="form-control" id="location-input" placeholder="Equipment location (175 for building 175)">
                  </div>
                  <div class="mb-3">
                    <label for="status-input" class="col-form-label">Status*:</label>
                    <input name="status" type="text" class="form-control" id="status-input" placeholder="Installed, tested, assembled ...">
                  </div>
                  <div class="mb-3">
                    <label for="comments-input" class="col-form-label">Comments:</label>
                    <textarea class="form-control" type="text" id="comments-input" rows="3" placeholder="Useful info about the equipment"></textarea>
                  </div>
                </div>
                <div class="row" id="add-files-row">
                  <div class="col mt-4">
                    <div class="row">
                      <div class="col-11"><h4>Link files</h4></div>
                      <div class="col-1 text-right">
                        <button class="btn btn-secondary mt-1" type="button" id="add-file-btn"><i class="bi bi-plus-lg"></i></button>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col"  id="add-files-col">
                        <input type="text" class="form-control mt-1" name="files[]" placeholder="File name">
                      </div>
                    </div>
                  </div>
                </div>
                  <div class="row">
                    <div class="col mt-4 text-right">
                      <button type="button" form="register-equipment-form" class="btn btn-danger">Cancel</button>
                      <button type="submit" class="btn btn-success" id="register-equipment-form-submit-button" aria-pressed="true">Register</button>
                    </div>
                  </div>
                </div>
              </div>
          </form>
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
<script type="text/javascript" src="<?=base_url('assets/js/equipment/no_child_register.js')?>" ></script>
</body>
</html>