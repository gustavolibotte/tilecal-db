<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
  <ul class="nav nav-tabs" id="nav-tabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-pane" type="button" role="tab" aria-controls="register-pane" aria-selected="true">Register</button>
    </li>
  </ul>
  <div class="tab-content" id="nav-tabs-content">
    <div class="tab-pane fade show active" id="register-pane" role="tabpanel" tabindex="0">
      <div class="row">
        <div class="col mt-4" id="form-col">
          <? echo form_open_multipart($formUrl, 'id="register-form"'); ?>
            <? foreach($forms as $formHeader=>$form): ?>
              <h4><? echo $formHeader; ?></h4>
              <? echo $form->toHTML(); ?>
            <? endforeach;?>
            <a type="button" href="<? echo $cancelUrl ?>" class="btn btn-danger">Cancel</a>
            <button type="submit" form="register-form" class="btn btn-success" id="register-form-submit-button" aria-pressed="true">Register</button>
          </form>
          <hr>
          <?= $this->renderSection('help') ?>
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
