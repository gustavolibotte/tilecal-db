<?= $this->extend('layouts/entry_register') ?>

<?= $this->section('table') ?>
  <h4 class="text-center">Last Edited Equipments</h4>
  <table class="table table-bordered" id="equipment-side-table"></table>
<?= $this->endSection() ?>

<? $this->section('javascript')?>
  <script type="text/javascript" src="/assets/js/equipment_table_utils.js"></script>
  <script type="text/javascript" src="/assets/js/equipments/register.js"></script>
<?= $this->endSection() ?>
