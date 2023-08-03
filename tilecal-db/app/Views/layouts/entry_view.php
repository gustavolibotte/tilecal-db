<?= $this->extend('layouts/content_with_side_col') ?>

<?= $this->section('css') ?>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
  <?= $this->renderSection('css') ?>
<?= $this->endSection() ?>

<?= $this->section('sideContent') ?>
  <? $this->renderSection('table') ?>
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
              <h4><? echo $infoHeader; ?></h4>
            </div>
            <div class="col-1 text-right">
              <a type="button"class="btn btn-primary" href="#">Edit</a>
            </div>
          </div>
          <div class="row">
            <div class="col" id="params-col">
              <ul class="list-group mt-2">
                <? $this->renderSection('entryProperties')?>
              </ul>
            </div>
          </div>
          <div class="row">
            <div class="col mt-4" id="files-info-col">
              <? $this->renderSection('relatedContent')?>
            </div>
          </div>
          </div>
      </div>
    </div>
    <div class="tab-pane fade" id="history-info-pane" role="tabpanel" tabindex="0">
      <div class="row">
        <div class="col mt-4" id="history-info-col">
          <h4>Change history</h4>
          <? if($histories):?>
            <? foreach($histories as $hist): ?>
              <ul class="list-group" id="equipment-history-list">
                <li class="list-group-item"><? echo $hist->message; ?></li>
              </ul>
            <? endforeach; ?>
          <? endif; ?>
        </div>
      </div>
    </div>
  </div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
  <script src="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.js"></script>
  <script src="https://cdn.datatables.net/plug-ins/1.13.4/features/scrollResize/dataTables.scrollResize.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <?= $this->renderSection('javascript') ?> 
<?= $this->endSection() ?>
