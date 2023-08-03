<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><? echo $pageTitle ?></title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/style.css'); ?>">
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <?= $this->renderSection('css') ?>
</head>

<?= $this->include('layouts/header') ?>

<body>
  <div class="container mt-4 mb-4">
    <div class="row">
      <div class="jumbotron text-left">
        <h1><? echo $pageHeader ?></h1>
      </div>
    </div>
    <?= $this->renderSection('content') ?>
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
  <?= $this->renderSection('javascript') ?>

</body>

</html>