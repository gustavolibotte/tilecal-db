<?= $this->extend('layouts/entry_register') ?>

<?= $this->section('table') ?>
  <h4 class="text-center">Registered Files</h4>
  <? echo view_cell('FileTableCell::side');?>
<?= $this->endSection() ?>