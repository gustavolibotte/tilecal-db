<?= $this->extend('layouts/entry_view') ?>

<?= $this->section('entryProperties') ?>
  <li class="list-group-item"><b>ID</b>: <? echo $equipmentInfo->id?> </li>
  <li class="list-group-item"><b>Model</b>: <? echo $equipmentInfo->model_label?> </li>
  <? if ($equipmentInfo->location_description): ?>
    <li class="list-group-item"><b>Location</b>: <? echo $equipmentInfo->location . ' - ' . $equipmentInfo->location_description?> </li>
  <? else: ?>
    <li class="list-group-item"><b>Location</b>: <? echo $equipmentInfo->location?> </li>
  <? endif; ?>
  <li class="list-group-item"><b>Status</b>: <? echo $equipmentInfo->status?> </li>
  <li class="list-group-item"><b>Owner</b>: <? echo $equipmentInfo->owner_name?> </li>
  <li class="list-group-item"><b>Parent ID</b>: <? echo $equipmentInfo->parent_id?></li>
  <li class="list-group-item"><b>Serial</b>: <? echo $equipmentInfo->serial?> </li>
  <?php if($equipmentInfo->model_params): ?>
    <?php foreach($equipmentInfo->model_params as $name=>$value): ?>
      <li class="list-group-item"><b><? echo ucwords(str_replace('_', ' ', $name)); ?></b>: <? echo $value; ?> </li>
    <?php endforeach; ?>
  <?php endif; ?>
  <li class="list-group-item"><b>Deleted</b>: <?php if($equipmentInfo->deletedAt): ?> <? echo $equipmentInfo->deletedAt?> <?php else: ?> No <?php endif; ?></li> 
  <li class="list-group-item"><b>Comments</b>: <? echo $equipmentInfo->comments?></li>
<?= $this->endSection() ?>

<?= $this->section('relatedContent') ?>
  <hr>
  <h4> Related Equipments </h4>
  <? echo view_cell('EquipmentTableCell::fullEquipmentRelated', ['equipmentId' => $equipmentInfo->id]);?>
  <hr>
  <h4> Related Files </h4>
  <hr>
  <? echo view_cell('FileTableCell::full', ['equipmentId' => $equipmentInfo->id]);?>
<?= $this->endSection() ?>

<?= $this->section('javascript')?>
  <? echo view_cell('EquipmentTableCell::fullEquipmentRelatedjs'); ?> 
  <? echo view_cell('FileTableCell::fulljs'); ?> 
<?= $this->endSection() ?>
