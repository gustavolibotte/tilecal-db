<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
  <div class="row">
    <div class="jumbotron text-left">
      <h1><? echo $viewHeader ?></h1>
    </div>
  </div>
  <div class="row">
    <div class="col-3 mt-4" id="side-table-col">
      <?= $this->renderSection('sideContent') ?>
    </div>
    <div class="col mt-4">
    <?= $this->renderSection('content') ?>
    </div>
  </div>
<?= $this->endSection() ?>


