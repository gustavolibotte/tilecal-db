<?= $this->extend('layouts/entry_register') ?>

<?= $this->section('table') ?>
  <h4 class="text-center">Uploaded Files</h4>
  <table class="table table-bordered" id="file-table-side"></table>
<?= $this->endSection() ?>

<? $this->section('javascript')?>
  <script type="text/javascript" src="/assets/js/file_table_utils.js"></script>
  <script type="text/javascript" src="/assets/js/files/upload.js"></script>
<?= $this->endSection() ?>
