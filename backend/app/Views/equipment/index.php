<?= $this->extend('layouts/base') ?>

<?= $this->section('css') ?>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <div class="mt-3">
    <table class="table table-bordered" id="equipment-table-index"></table>
  </div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
  <script src="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.js"></script>
  <script src="https://cdn.datatables.net/plug-ins/1.13.4/features/scrollResize/dataTables.scrollResize.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="/assets/js/equipment_table_utils.js"></script>
  <script type="text/javascript" src="/assets/js/equipments/index.js"></script> 
<?= $this->endSection() ?>
