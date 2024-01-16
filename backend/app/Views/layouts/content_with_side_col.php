<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
  <div class="row">
    <div class="col-3 mt-4" id="side-table-col" style="overflow-y: scroll;">
      <?= $this->renderSection('sideContent') ?>
    </div>
    <div class="col mt-4" id="content-col">
    <?= $this->renderSection('content') ?>
    </div>
  </div>
<?= $this->endSection() ?>


