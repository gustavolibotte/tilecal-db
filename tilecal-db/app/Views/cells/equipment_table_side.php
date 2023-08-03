<table class="table table-bordered" id="equipment-side-table">
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
