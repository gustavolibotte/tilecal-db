<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><? echo $pageTitle ?></title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/style.css'); ?>">
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <?= $this->renderSection('css') ?>
</head>

<? echo view_cell('LayoutCell::header'); ?>

<body>
  <div class="container-fluid mt-4 mb-4">
    <div class="row">
      <div class="jumbotron text-left">
        <? if (empty($headerClass)):?>
          <h1><? echo $pageHeader ?></h1>
        <? else: ?>
          <? echo sprintf('<h1 class="%s">%s</h1>', $headerClass, $pageHeader);?>
        <?endif; ?>
      </div>
      <? echo view_cell('MessagesCell::showMessages'); ?>
    </div>
    <?= $this->renderSection('content') ?>
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script type="text/javascript" src="/assets/js/toastr_utils.js"></script>
  <script type="text/javascript" src="/assets/js/routes.js"></script>
  <?= $this->renderSection('javascript') ?>

</body>

</html>