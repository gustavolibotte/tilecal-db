<!doctype html>
<html lang="en">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>File Register </title>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>

<body>
<div class="container mt-4 mb-5">
  <div class="row">
    <div class="jumbotron text-left">
      <h1>File Register</h1>
    </div>
  </div>
  <div class="row">
    <div class="col-3 mt-4">
      <table class="table table-bordered" id="files-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Filename</th>
            <th>Download</th>
          </tr>
        </thead>
        <tbody>
          <?php if($files): ?>
          <?php foreach($files as $file): ?>
            <tr>
              <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('files/view/%s', $file['id'])); ?>"><?php echo $file['id']; ?></a></td>
              <td><? echo $file['name']; ?></td>
              <td><a type="button" class="btn btn-secondary" href="<? echo base_url(sprintf('files/%s', $file['id'])); ?>"><i class="bi bi-download"></i></a></td>
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
            Upload
          </button>
        </li>
      </ul>
      <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
          <form id="register-equipment-form">
            <?= csrf_field() ?>
            <div class="row">
              <div class="col mt-4" id="equipment-info-row">
                <h4>File info</h4>
                <div id="parent-id-help" class="form-text">* required field</div>
                <div class="mb-3">
                  <label for="name-input" class="col-form-label">Name*:</label>
                  <input name="name" type="text" class="form-control" id="name-input" placeholder="2023_07_30_pmt_block_5628_test.pdf">
                </div>
                <div class="mb-3">
                  <label for="file-input" class="form-label">Choose file to upload*:</label>
                  <input class="form-control" type="file" id="file-input">
                </div>
                <div class="mb-3">
                  <label for="description-input" class="col-form-label">Description*:</label>
                  <textarea class="form-control" type="text" id="description-input" rows="3" placeholder="Useful info about the file"></textarea>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col mt-4 text-right">
                <a type="button" form="register-equipment-form" href="<? echo base_url('files')?>" class="btn btn-danger">Cancel</a>
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
<script type="text/javascript" src="<?=base_url('assets/js/files/register.js')?>" ></script>
</body>
</html> 