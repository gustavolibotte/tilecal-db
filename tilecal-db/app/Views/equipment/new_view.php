<?= $this->extend('layouts/content_with_side_col') ?>

<?= $this->section('css') ?>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/style.css'); ?>">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/view-style.css'); ?>">
<?= $this->endSection() ?>

<?= $this->section('sideContent') ?>
  <h4 class="text-center">Related Equipments</h4>
  <table class="table table-bordered" id="equipments-side-table">
    <thead>
      <tr>
        <th>Barcode</th>
        <th>Model</th>
      </tr>
    </thead>
    <tbody>
      <?php if($relatedEquipments): ?>
      <?php foreach($relatedEquipments as $equipment): ?>
        <tr>
          <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('equipments/view/%s', $equipment->id)); ?>"><?php echo $equipment->barcode; ?></a></td>
          <td><? echo $equipment->modelLabel; ?></td>
        </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="equip-info-tab" data-bs-toggle="tab" data-bs-target="#equip-info-pane" type="button" role="tab" aria-controls="equip-info-pane" aria-selected="true">Info</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="history-info-tab" data-bs-toggle="tab" data-bs-target="#history-info-pane" type="button" role="tab" aria-controls="history-info-pane" aria-selected="true">History</button>
    </li>
  </ul>
  <div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="equip-info-pane" role="tabpanel" tabindex="0">
      <div class="row">
        <div class="col mt-4" id="data-info-col">
          <div class="row">
            <div class="col">
              <h4>Equipment info</h4>
            </div>
            <div class="col-1 text-right">
              <a type="button"class="btn btn-primary" href="<?echo base_url(sprintf('equipments/edit/%s/%s', $equipmentInfo->modelName, $equipmentInfo->id))?>">Edit</a>
            </div>
          </div>
          <div class="row">
            <div class="col" id="equipment-params-col">
              <ul class="list-group mt-2">
                <li class="list-group-item"><b>ID</b>: <? echo $equipmentInfo->id?> </li>
                <li class="list-group-item"><b>Model</b>: <? echo $equipmentInfo->modelLabel?> </li>
                <li class="list-group-item"><b>Location</b>: <? echo $equipmentInfo->location?> </li>
                <li class="list-group-item"><b>Status</b>: <? echo $equipmentInfo->status?> </li>
                <li class="list-group-item"><b>Parent ID</b>: <? echo $equipmentInfo->parentID?></li>
                <li class="list-group-item"><b>Serial</b>: <? echo $equipmentInfo->serial?> </li>
                <li class="list-group-item"><b>Deleted</b>: <?php if($equipmentInfo->deletedAt): ?> <? echo $equipmentInfo->deletedAt?> <?php else: ?> No <?php endif; ?></li> 
                <li class="list-group-item"><b>Comments</b>: <? echo $equipmentInfo->comments?></li> 
              </ul>
            </div>
          </div>
          <div class="row">
            <div class="col mt-4" id="model-params-col">
              <div class="row">
                <div class="col">
                  <h4>Model Params</h4>
                </div>
              </div>
              <?php if($equipmentInfo->modelParams): ?>
                <ul class="list-group mt-2">
                  <?php foreach($equipmentInfo->modelParams as $name=>$value): ?>
                    <li class="list-group-item"><b><? echo ucwords(str_replace('_', ' ', $name)); ?></b>: <? echo $value; ?> </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          </div>
          <div class="row">
            <div class="col mt-4" id="files-info-col">
              <h4>Related Files</h4>
              <table class="table table-bordered" id="related-files-table">
                <thead>
                  <tr>
                    <th>Filename</th>
                    <th>Description</th>
                    <th>Download</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if($files): ?>
                  <?php foreach($files as $file): ?>
                    <tr>
                      <td><? echo $file->name; ?></td>
                      <td><? echo $file->description; ?></td>
                      <td><a type="button" class="btn btn-secondary" href="<? echo base_url(sprintf('files/%s', $file->id)); ?>"><i class="bi bi-download"></i></a></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
          </div>
      </div>
    </div>
    <div class="tab-pane fade" id="history-info-pane" role="tabpanel" tabindex="0">
      <div class="row">
        <div class="col mt-4" id="history-info-col">
          <h4>Equipment history</h4>
          <? if($equipmentHist):?>
            <? foreach($equipmentHist as $history): ?>
              <ul class="list-group" id="equipment-hsitory-list">
                <li class="list-group-item"><? echo $history->message; ?></li>
              </ul>
            <? endforeach; ?>
          <? endif; ?>
        </div>
      </div>
    </div>
  </div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.js"></script>
  <script src="https://cdn.datatables.net/plug-ins/1.13.4/features/scrollResize/dataTables.scrollResize.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <!-- App JS modules -->
  <script type="text/javascript" src="<?=base_url('assets/js/equipment/view.js')?>" ></script>  
<?= $this->endSection() ?>