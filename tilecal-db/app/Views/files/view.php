<?= $this->extend('layouts/entry_view') ?>

<?= $this->section('entryProperties') ?>
  <li class="list-group-item">Name: <? echo $fileInfo->name?> </li>
  <li class="list-group-item">Description: <? echo $fileInfo->description?> </li>
  <li class="list-group-item">Download: <a type="button" class="btn btn-secondary" href="<? echo base_url(sprintf('files/%s', $fileInfo->id)); ?>"><i class="bi bi-download"></i></a> </li>
<?= $this->endSection() ?>

<?= $this->section('relatedContent') ?>
  <hr>
  <h4>Related Equipments</h4>
  <? echo view_cell('EquipmentTableCell::fullFileRelated', ['fileId' => $fileInfo->id]);?>
<?= $this->endSection() ?>

<?= $this->section('javascript')?>
  <? echo view_cell('EquipmentTableCell::fullFileRelatedjs'); ?> 
<?= $this->endSection() ?>
