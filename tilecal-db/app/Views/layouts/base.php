<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><? echo $pageTitle ?></title>
  <?= $this->renderSection('css') ?>
</head>

<?= $this->include('layouts/header') ?>

<body>
  <div class="container mt-4">
    <?= $this->renderSection('content') ?>
  </div>

  <?= $this->renderSection('javascript') ?>

</body>

</html>