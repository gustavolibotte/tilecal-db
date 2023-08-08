<?= $this->extend('layouts/content_with_side_col') ?>

<?= $this->section('css') ?>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
  <?= $this->renderSection('css') ?>
<?= $this->endSection() ?>

<?= $this->section('sideContent') ?>
  <? $this->renderSection('table') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="entry-info-tab" data-bs-toggle="tab" data-bs-target="#entry-info-pane" type="button" role="tab" aria-controls="entry-info-pane" aria-selected="true">Info</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="history-info-tab" data-bs-toggle="tab" data-bs-target="#history-info-pane" type="button" role="tab" aria-controls="history-info-pane" aria-selected="true">History</button>
    </li>
  </ul>
  <div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="entry-info-pane" role="tabpanel" tabindex="0">
      <div class="row">
        <div class="col mt-4" id="data-info-col">
          <div class="row">
            <div class="col">
              <h4><? echo $infoHeader; ?></h4>
            </div>
            <div class="col-1 text-right">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit-modal">
              Edit
            </button>
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
  <div class="modal fade" id="edit-modal" tabindex="-1" aria-labelledby="edit-modal-label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <? if ($editable):?>
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="edit-modal-label">Edit info</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <? echo form_open_multipart($editUrl, 'id="edit-form"'); ?>
          <div class="modal-body">
            <? echo $editForm->toHTML();?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      <? else: ?>
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="edit-modal-label">You can't edit this, if you think this an error contact an admin.</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      <? endif; ?>
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
