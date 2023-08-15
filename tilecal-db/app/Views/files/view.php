<?= $this->extend('layouts/entry_view') ?>

<?= $this->section('entryProperties') ?>
  <li class="list-group-item"><b>Name</b>: <? echo $fileInfo->name?> </li>
  <li class="list-group-item"><b>Description</b>: <? echo $fileInfo->description?> </li>
  <li class="list-group-item"><b>First Upload</b>: <? echo $fileInfo->created_at?> </li>
  <li class="list-group-item"><b>Last Update</b>: <? echo $fileInfo->updated_at?> </li>
  <li class="list-group-item"><b>Download</b>: <a type="button" class="btn btn-secondary" href="<? echo base_url(sprintf('files/%s', $fileInfo->id)); ?>"><i class="bi bi-download"></i></a> </li>
<?= $this->endSection() ?>

<?= $this->section('relatedContent') ?>
  <hr>
  <h4>Related Equipments</h4>
  <? echo view_cell('EquipmentTableCell::fullFileRelated', ['fileId' => $fileInfo->id]);?>
<?= $this->endSection() ?>

<?= $this->section('javascript')?>
  <? echo view_cell('EquipmentTableCell::fullFileRelatedjs'); ?> 
<?= $this->endSection() ?>
