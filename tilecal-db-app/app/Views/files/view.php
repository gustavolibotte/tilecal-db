<!doctype html>
<html lang="en">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title> File <? echo $fileInfo['id']?> </title>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>

<body>
<div class="container mt-4 mb-5">
  <div class="row">
    <div class="jumbotron text-left">
      <h1> File ID: <? echo $fileInfo['id']?></h1>
    </div>
  </div>
  <div class="row">
    <div class="col-3 mt-4">
      <h4 class="text-center">Related Equipments</h4>
      <table class="table table-bordered" id="equipments-side-table">
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
          <button class="nav-link active" id="equip-info-tab" data-bs-toggle="tab" data-bs-target="#equip-info-pane" type="button" role="tab" aria-controls="equip-info-pane" aria-selected="true">Info</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="history-info-tab" data-bs-toggle="tab" data-bs-target="#history-info-pane" type="button" role="tab" aria-controls="history-info-pane" aria-selected="true">History</button>
        </li>
      </ul>
      <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="equip-info-pane" role="tabpanel" tabindex="0">
          <div class="row">
            <div class="col mt-4" id="equipment-info-col">
              <div class="row">
                <div class="col">
                  <h4>File info</h4>
                </div>
                <div class="col-1 text-right">
                  <a type="button"class="btn btn-primary"href="#">Edit</a>
                </div>
              </div>
              <ul class="list-group mt-2">
                <li class="list-group-item">ID: <? echo $fileInfo['id']?> </li>
                <li class="list-group-item">Name: <? echo $fileInfo['name']?> </li>
                <li class="list-group-item">Description: <? echo $fileInfo['description']?> </li>
                <li class="list-group-item">Download: <a type="button" class="btn btn-secondary" href="<? echo base_url(sprintf('files/%s', $file['id'])); ?>"><i class="bi bi-download"></i></a> </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="history-info-pane" role="tabpanel" tabindex="0">
          <div class="row">
            <div class="col mt-4" id="history-info-col">
              <h4>File history</h4>
              <ul class="list-group">
                <li class="list-group-item">
                  <div class="row">
                    <div class="col align-self-center">
                      <p><b>2023-06-27 13:00</b> Lucas updated the uploaded file. Download the older version on the side button.</p>
                    </div>
                    <div class="col-1">
                      <a type="button" class="btn btn-secondary" href="#"><i class="bi bi-download"></i></a>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
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
<script type="text/javascript" src="<?=base_url('assets/js/files/view.js')?>" ></script>
</body>
</html>