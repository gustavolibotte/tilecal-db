<?= $this->extend('layouts/equipments/content_with_side_table') ?>

<? $this->section('content') ?>
  <ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">
        Register
      </button>
    </li>
  </ul>
  <div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
      <?= form_open_multipart(base_url(sprintf('equipments/%s/register', $model->name)), 'id="register-equipment-form"'); ?>
      <div class="row">
        <div class="col" id="form-col">
          <?= csrf_field() ?>
            <div class="row">
              <div class="col mt-4" id="equipment-info-row">
                <h4>Equipment info</h4>
                <? if($successMessage): ?>
                  <div class="alert alert-success alert-dismissible" role="alert">
                    <div>Register success</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                <? endif; ?>
                <? echo $equipmentForm->toHTML(); ?>
            </div>
            <?php if($enableFileLink): ?>
              <div class="row" id="add-files-row">
                <div class="col mt-4">
                  <div class="row">
                    <div class="col-11"><h4>Link files</h4></div>
                    <div class="col-1 text-right">
                      <button class="btn btn-secondary mt-1" type="button" id="add-file-btn"><i class="bi bi-plus-lg"></i></button>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col"  id="add-files-col">
                      <input type="text" class="form-control mt-1" name="files[]" placeholder="File name">
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>
            <div class="row">
              <div class="col mt-4 text-right">
              <a type="button" form="upload-file-form" href="<? echo base_url('equipments/pmt')?>" class="btn btn-danger">Cancel</a>
                <button type="submit" class="btn btn-success" id="register-equipment-form-submit-button" aria-pressed="true">Register</button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
  <!-- App JS modules -->
  <script type="text/javascript" src="<?=base_url('assets/js/equipment/view.js')?>" ></script>  
<?= $this->endSection() ?>
