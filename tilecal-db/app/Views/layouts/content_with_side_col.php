<?= $this->extend('layouts/base') ?>

<?= $this->section('css') ?>
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/content-side-col.css'); ?>">
  <?= $this->renderSection('css') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <div class="row">
    <div class="col-3 mt-4" id="side-table-col">
      <?= $this->renderSection('sideContent') ?>
    </div>
    <div class="col mt-4" id="content-col">
    <?= $this->renderSection('content') ?>
    </div>
  </div>
<?= $this->endSection() ?>


