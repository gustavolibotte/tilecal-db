<!doctype html>
<html lang="en">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><? echo $model['formatted_name']?> Register </title>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>

<body>
<div class="container mt-4 mb-5">
  <div class="row">
    <div class="jumbotron text-left">
      <h1><? echo $model['formatted_name']?> Register</h1>
    </div>
  </div>
  <div class="row">
    <div class="col-3 mt-4">
      <h4 class="text-center">Equipments</h4>
      <table class="table table-bordered" id="equipments-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Model</th>
          </tr>
        </thead>
        <tbody>
          <?php if($relatedEquipments): ?>
          <?php foreach($relatedEquipments as $equipment): ?>
            <tr>
              <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('equipments/view/%s', $equipment->id)); ?>"><?php echo $equipment->id; ?></a></td>
              <td><? echo $equipment->modelLabel; ?></td>
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
          <!-- <form id="register-equipment-form" action=<? echo base_url(sprintf('%s/register', $model['name']))?> method="POST"> -->
          <?= form_open_multipart(base_url(sprintf('equipments/%s/register', $model['name'])), 'id="register-equipment-form"'); ?>
          <?= csrf_field() ?>
            <div class="row">
              <div class="col mt-4" id="equipment-info-row">
                <h4>Equipment info</h4>
                <div id="parent-id-help" class="form-text">* required field</div>
                <? if($validationErrors): ?>
                  <ul>
                    <?php foreach($validationErrors as $errorMsg): ?>
                      <li><p class="text-danger"><? echo esc($errorMsg); ?></p></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
                <div class="mb-3">
                  <label for="equipment-id-input" class="col-form-label">ID*:</label>
                  <input name="id" type="text" class="form-control" id="equipment-id-input" placeholder="<?echo $formPlaceholders['id'];?>" value="<? echo $preFilledValues['id']?>">
                </div>
                <div class="mb-3">
                  <label for="location-input" class="col-form-label">Location*:</label>
                  <input name="location" type="text" class="form-control" id="location-input" placeholder="<?echo $formPlaceholders['location'];?>" value="<? echo $preFilledValues['location']?>">
                </div>
                <div class="mb-3">
                  <label for="status-input" class="col-form-label">Status*:</label>
                  <input name="status" type="text" class="form-control" id="status-input" placeholder="<?echo $formPlaceholders['status'];?>" value="<? echo $preFilledValues['status']?>">
                </div>
                <div class="mb-3">
                  <label for="beta-input" class="col-form-label">Beta*:</label>
                  <input name="beta" type="text" class="form-control" id="beta-input" placeholder="<?echo $formPlaceholders['beta'];?>" value="<? echo $preFilledValues['beta']?>">
                </div>
                <div class="mb-3">
                  <label for="nominal_hv-input" class="col-form-label">Nominal HV*:</label>
                  <input name="nominal_hv" type="text" class="form-control" id="nominal_hv-input" placeholder="<?echo $formPlaceholders['nominal_hv'];?>" value="<? echo $preFilledValues['nominal_hv']?>">
                </div>
                <div class="mb-3">
                  <label for="qe-input" class="col-form-label">QE*:</label>
                  <input name="qe" type="text" class="form-control" id="qe-input" placeholder="<?echo $formPlaceholders['qe'];?>" value="<? echo $preFilledValues['qe']?>">
                </div>
                <div class="mb-3">
                  <label for="type-input" class="col-form-label">Type*:</label>
                  <input name="type" type="text" class="form-control" id="type-input" placeholder="<?echo $formPlaceholders['type'];?>" value="<? echo $preFilledValues['type']?>">
                </div>
                <div class="mb-3">
                  <label for="comments-input" class="col-form-label">Comments:</label>
                  <textarea name="comments" class="form-control" type="text" id="comments-input" rows="3" placeholder="<?echo $formPlaceholders['comments'];?>" value="<? echo $preFilledValues['comments']?>"></textarea>
                </div>
              </div>
            </div>
            <?php if($enableFileLink): ?>
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
            <?php endif; ?>
            <div class="row">
              <div class="col mt-4 text-right">
              <a type="button" form="upload-file-form" href="<? echo base_url('equipments/pmt')?>" class="btn btn-danger">Cancel</a>
                <button type="submit" class="btn btn-success" id="register-equipment-form-submit-button" aria-pressed="true">Register</button>
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