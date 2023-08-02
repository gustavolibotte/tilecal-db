<?= $this->extend('layouts/table_index') ?>

<?= $this->section('content') ?>
  <div class="container mt-4">
  <h1>Equipment Index</h1>
    <div class="mt-3">
      <table class="table table-bordered" id="equipments-table">
        <thead>
          <tr>
            <th>Barcode</th>
            <th>Model</th>
            <th>Location</th>
            <th>Status</th>
            <th>Parent</th>
            <th>Comments</th>
            <th>Created at</th>
            <th>Updated at</th>
          </tr>
        </thead>
        <tbody>
            <?php if($equipments): ?>
            <?php foreach($equipments as $equipment): ?>
            <tr>
              <td><a type="button" class="btn btn-primary" href="<?php echo base_url(sprintf('equipments/view/%s', $equipment->id)); ?>"><?php echo $equipment->barcode; ?></a></td>
              <td><?php echo $equipment->model_label; ?></td>
              <td><?php echo $equipment->location; ?></td>
              <td><?php echo $equipment->status; ?></td>
              <td><a type="button" class="btn btn-secondary" href="<?php echo base_url(sprintf('equipments/view/%s', $equipment->parent_id)); ?>"><?php echo $equipment->parent_model_label; ?></a></td>
              <td><?php echo $equipment->comments; ?></td>
              <td><?php echo $equipment->createdAt; ?></td>
              <td><?php echo $equipment->updatedAt; ?></td>
            </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
  <script type="text/javascript" src="<?=base_url('assets/js/equipment/index.js')?>" ></script> 
<?= $this->endSection() ?>
