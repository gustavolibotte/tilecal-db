<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
  <div class="row align-items-center mt-4">
    <div class="col mt-4">
      <div class="jumbotron text-center">
        <h1>An Error has occured: <? echo $errorText?></h1>
        <p><? echo $errorMessage ;?></p>
        </p> 
      </div>
    </div>
  </div>
  <?= $this->endSection() ?>
