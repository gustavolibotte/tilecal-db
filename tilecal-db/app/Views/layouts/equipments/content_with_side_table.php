<?= $this->extend('layouts/content_with_side_col') ?>

<?= $this->section('css') ?>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
  <?= $this->renderSection('css') ?>
<?= $this->endSection() ?>

<?= $this->section('sideContent') ?>
  <h4 class="text-center"><? echo $sideTableHeader; ?></h4>
  <table class="table table-bordered" id="equipments-side-table">
    <thead>
      <tr>
        <th>Barcode</th>
        <th>Model</th>
      </tr>
    </thead>
    <tbody>
      <?php if($equipments): ?>
      <?php foreach($equipments as $equipment): ?>
        <tr>
          <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('equipments/view/%s', $equipment->id)); ?>"><?php echo $equipment->barcode; ?></a></td>
          <td><? echo $equipment->modelLabel; ?></td>
        </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
  <script src="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.js"></script>
  <script src="https://cdn.datatables.net/plug-ins/1.13.4/features/scrollResize/dataTables.scrollResize.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="<?=base_url('assets/js/equipment/content_with_side_table.js')?>" ></script>  
  <?= $this->renderSection('javascript') ?> 
<?= $this->endSection() ?>
