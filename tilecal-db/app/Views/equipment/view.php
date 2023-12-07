<?= $this->extend('layouts/entry_view') ?>

<?= $this->section('entryProperties') ?>
  <li class="list-group-item"><b>ID</b>: <? echo $entity->id?> </li>
  <li class="list-group-item"><b>ATLAS ID</b>: <? echo $entity->atlas_id?> </li>
  <li class="list-group-item"><b>Type</b>: <? echo $entity->type_label?> </li>
  <? if ($entity->location_description): ?>
    <li class="list-group-item"><b>Location</b>: <? echo $entity->location_name . ' - ' . $entity->location_description?> </li>
  <? else: ?>
    <li class="list-group-item"><b>Location</b>: <? echo $entity->location_name?> </li>
  <? endif; ?>
  <li class="list-group-item"><b>Detector Location</b>: <? echo $entity->detector_location_name?> </li>
  <li class="list-group-item"><b>Status</b>: <? echo $entity->status_name?> </li>
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
  <div class="row">
    <div class="col">
      <h4> Related Equipment </h4>
    </div>
    <? if($canBeAssembled): ?>
      <div class="col-5 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assemble-modal">
          Add assembled equipment
        </button>
      </div>
    <? endif; ?>
  </div>
  <p> The table includes the parent and children equipment to the shown equipment.</p>
  <table class="table table-bordered" id="related-equipments-table" style="width:100%"></table>
  <hr>
  <h4> Related Files </h4>
  <p> All files that are related to this equipment.</p>
  <table class="table table-bordered" id="related-files-table" style="width:100%"></table>
<?= $this->endSection() ?>

<? $this->section('extraModals')?>
  <? if ($canBeAssembled): ?>
  <div class="modal fade" id="assemble-modal" tabindex="-1" aria-labelledby="assemble-modal" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="assemble-modal-label">Assemble Equipment</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <? echo form_open_multipart($assembleUrl, 'id="assemble-form"'); ?>
          <div class="modal-body">
            <? echo $assembleForm->toHTML();?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Assemble</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <? endif; ?>
<? $this->endSection()?>

<?= $this->section('javascript')?>
  <script type="text/javascript" src="/assets/js/string_utils.js"></script>
  <script type="text/javascript" src="/assets/js/file_table_utils.js"></script>
  <script type="text/javascript" src="/assets/js/equipment_table_utils.js"></script>
  <script type="text/javascript" src="/assets/js/hist_msg.js"></script>
  <script type="text/javascript" src="/assets/js/equipments/view.js"></script>
<?= $this->endSection() ?>
