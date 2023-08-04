<?= $this->extend('layouts/entry_view') ?>

<?= $this->section('table') ?>
  <h4 class="text-center">Related Equipments</h4>
  <? echo view_cell('EquipmentTableCell::side', ['equipmentId' => $equipmentInfo->id]);?>
<?= $this->endSection() ?>

<?= $this->section('entryProperties') ?>
  <li class="list-group-item"><b>ID</b>: <? echo $equipmentInfo->id?> </li>
  <li class="list-group-item"><b>Model</b>: <? echo $equipmentInfo->modelLabel?> </li>
  <li class="list-group-item"><b>Location</b>: <? echo $equipmentInfo->location?> </li>
  <li class="list-group-item"><b>Status</b>: <? echo $equipmentInfo->status?> </li>
  <li class="list-group-item"><b>Parent ID</b>: <? echo $equipmentInfo->parentID?></li>
  <li class="list-group-item"><b>Serial</b>: <? echo $equipmentInfo->serial?> </li>
  <?php if($equipmentInfo->modelParams): ?>
    <?php foreach($equipmentInfo->modelParams as $name=>$value): ?>
      <li class="list-group-item"><b><? echo ucwords(str_replace('_', ' ', $name)); ?></b>: <? echo $value; ?> </li>
    <?php endforeach; ?>
  <?php endif; ?>
  <li class="list-group-item"><b>Deleted</b>: <?php if($equipmentInfo->deletedAt): ?> <? echo $equipmentInfo->deletedAt?> <?php else: ?> No <?php endif; ?></li> 
  <li class="list-group-item"><b>Comments</b>: <? echo $equipmentInfo->comments?></li>
<?= $this->endSection() ?>

<?= $this->section('relatedContent') ?>
  <h4> Related Files </h4>
  <hr>
  <? echo view_cell('FileTableCell::full', ['equipmentId' => $equipmentInfo->id]);?>
<?= $this->endSection() ?>

<?= $this->section('javascript')?>
  <? echo view_cell('EquipmentTableCell::sidejs'); ?> 
  <? echo view_cell('FileTableCell::fulljs'); ?> 
<?= $this->endSection() ?>
