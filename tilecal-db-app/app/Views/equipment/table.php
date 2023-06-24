<!doctype html>
<html lang="en">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>TileCal Electronics Database</title>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
</head>

<body>
<div class="container mt-4">
<h1>TileCal Electronics Database</h1>

    <div class="d-flex justify-content-end">
        <a href="<?php echo site_url('/addname') ?>" class="btn btn-primary">Add a Name & email</a>
	</div>
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
          <th>Created at</th>
          <th>Updated at</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- App JS modules -->
<script type="text/javascript" src="<?=base_url('assets/js/equipment/table.js')?>" ></script>
</body>
</html>