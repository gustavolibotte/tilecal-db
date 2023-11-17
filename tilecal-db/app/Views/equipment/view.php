<?= $this->extend('layouts/entry_view') ?>

<?= $this->section('entryProperties') ?>
  <li class="list-group-item"><b>ID</b>: <? echo $entity->id?> </li>
  <li class="list-group-item"><b>ATLAS ID</b>: <? echo $entity->atlas_id?> </li>
  <li class="list-group-item"><b>Type</b>: <? echo $entity->type_label?> </li>
  <? if ($entity->location_description): ?>
    <li class="list-group-item"><b>Location</b>: <? echo $entity->location . ' - ' . $entity->location_description?> </li>
  <? else: ?>
    <li class="list-group-item"><b>Location</b>: <? echo $entity->location?> </li>
  <? endif; ?>
  <li class="list-group-item"><b>Detector Location</b>: <? echo $entity->detector_location_name?> </li>
  <li class="list-group-item"><b>Status</b>: <? echo $entity->status?> </li>
  <li class="list-group-item"><b>Owner</b>: <? echo $entity->owner_name?> </li>
  <li class="list-group-item"><b>Serial</b>: <? echo $entity->serial?> </li>
  <?php if($entity->type_params): ?>
    <?php foreach($entity->type_params as $name=>$value): ?>
      <li class="list-group-item"><b><? echo ucwords(str_replace('_', ' ', $name)); ?></b>: <? echo $value; ?> </li>
    <?php endforeach; ?>
  <?php endif; ?>
  <li class="list-group-item"><b>Deleted</b>: <?php if($entity->deletedAt): ?> <? echo $entity->deletedAt?> <?php else: ?> No <?php endif; ?></li> 
  <li class="list-group-item"><b>Comments</b>: <? echo $entity->comments?></li>
<?= $this->endSection() ?>

<?= $this->section('relatedContent') ?>
  <hr>
  <h4> Related Equipments </h4>
  <p> The table includes the parent and children equipment to the shown equipment.</p>
  <table class="table table-bordered" id="related-equipments-table" style="width:100%"></table>
  <hr>
  <h4> Related Files </h4>
  <p> All files that are related to this equipment.</p>
  <table class="table table-bordered" id="related-files-table" style="width:100%"></table>
<?= $this->endSection() ?>

<?= $this->section('javascript')?>
  <script type="text/javascript" src="/assets/js/string_utils.js"></script>
  <script type="text/javascript" src="/assets/js/file_table_utils.js"></script>
  <script type="text/javascript" src="/assets/js/equipment_table_utils.js"></script>
  <script type="text/javascript" src="/assets/js/hist_msg.js"></script>
  <script type="text/javascript" src="/assets/js/equipments/view.js"></script>
<?= $this->endSection() ?>
