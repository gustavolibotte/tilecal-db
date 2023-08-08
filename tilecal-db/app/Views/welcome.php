<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
  <div class="row align-items-center mt-4">
    <div class="col mt-4">
      <div class="jumbotron text-center">
        <h1>Welcome to the Tilecal Electronics Database</h1>
        <p>Here we can see the current status of each Tilecal component.
          Use the navbar on top to naviagte through the pages or choose one of the options bellow.</p>
        </p> 
      </div>
    </div>
  </div>

  <div class="row mt-4 align-items-center">
    <div class="col">
      <h3>Equipments</h3>
      <ul>
        <li><a href="<? echo base_url(('equipments')); ?>" class="text-decoration-underline">Index</a></li>
      </ul>
    </div>
    <div class="col">
      <h3>Files</a></h3>
      <ul>
        <li><a href="<? echo base_url(('files')); ?>" class="text-decoration-underline">Index</a></li>
        <li><a href="#" class="text-decoration-underline">Upload</a></li>
      </ul>
    </div>
  </div>
  <?= $this->endSection() ?>