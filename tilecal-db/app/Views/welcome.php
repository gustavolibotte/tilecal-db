<!DOCTYPE html>
<html lang="en">
<head>
  <title>Welcome to the Tilecal Database</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
</head>

<?= $this->include('layouts/header') ?>

<body>
<div class="container mt-4">

  <div class="row align-items-center mt-4">
    <div class="col mt-4">
      <div class="jumbotron text-center">
        <h1>Welcome to the Tilecal Electronics Database</h1>
        <p>Here we can see the current status of each Tilecal component.
          Use the navbar on top to naviagte through the pages or choose one of the options bellow.</p>
        </p> 
      </div>
    </div>
  </div>

  <div class="row mt-4 align-items-center">
    <div class="col">
      <h3>Equipments</h3>
      <ul>
        <li><a href="<? echo base_url(('equipments')); ?>" class="text-decoration-underline">Index</a></li>
      </ul>
    </div>
    <div class="col">
      <h3>Files</a></h3>
      <ul>
        <li><a href="<? echo base_url(('files')); ?>" class="text-decoration-underline">Index</a></li>
        <li><a href="#" class="text-decoration-underline">Upload</a></li>
      </ul>
    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>

<?= $this->include('layouts/footer') ?>

</html>
