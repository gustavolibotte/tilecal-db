<?= $this->extend('layouts/entry_register') ?>

<?= $this->section('table') ?>
  <h4 class="text-center">Last Registered Equipments</h4>
  <? echo view_cell('EquipmentTableCell::side');?>
<?= $this->endSection() ?>

<? $this->section('javascript')?>
<? echo view_cell('EquipmentTableCell::sidejs');?>
<?= $this->endSection() ?>
