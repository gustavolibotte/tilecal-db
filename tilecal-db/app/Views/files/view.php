<?= $this->extend('layouts/entry_view') ?>

<?= $this->section('entryProperties') ?>
  <li class="list-group-item"><b>Name</b>: <? echo $entity->name?> </li>
  <li class="list-group-item"><b>Description</b>: <? echo $entity->description?> </li>
  <li class="list-group-item"><b>First Upload</b>: <? echo $entity->created_at?> </li>
  <li class="list-group-item"><b>Last Update</b>: <? echo $entity->updated_at?> </li>
  <li class="list-group-item"><b>Download</b>: <a type="button" class="btn btn-secondary" href="<? echo url_to('file_download', $entity->id); ?>"><i class="bi bi-download"></i></a> </li>
<?= $this->endSection() ?>

<?= $this->section('relatedContent') ?>
  <hr>
  <h4>Related Equipments</h4>
  <table class="table table-bordered" id="related-equipments-table" style="width:100%"></table>
<?= $this->endSection() ?>

<?= $this->section('javascript')?>
  <script type="text/javascript" src="/assets/js/equipment_table_utils.js"></script>
  <script type="text/javascript" src="/assets/js/hist_msg.js"></script>
  <script type="text/javascript" src="/assets/js/string_utils.js"></script>
  <script type="text/javascript" src="/assets/js/files/view.js"></script>
<?= $this->endSection() ?>
