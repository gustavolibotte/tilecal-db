<?= $this->extend('layouts/register_single_tab') ?>

<? $this->section('help') ?>
<div class="row">
  <h2>How to fill this form?</h2>
  <p class="fst-normal">Here you can register multiple equipments at once using a csv file with comma as the separator.</p>
  <p class="fst-normal">On the equipment field, select which equipment type the data inside the file will represent.</p>
  <p class="fst-normal">The csv file should be structured with the registering fields as columns and each row as an equipment. The possible fields are the same as on the equipment specific available fields when accesing by Register->"Equipment Name" but as <a href="https://en.wikipedia.org/wiki/Snake_case">snake_case</a>.</p>
  <p class="fst-normal">As an example, to register a PMT, select "PMT" on the equipment type field. Since its registering page has the fields: "Owner Institute", "Location", "Status", "Serial", "Beta", "Nominal HV", "QE", "Model" and "Comments", these fields in a csv file filled with data are transformed to <a href="https://en.wikipedia.org/wiki/Snake_case">snake_case</a> and the file should be as shown bellow. Fields that have a dropdown option in the specific page should contain one of the options from that dropdown (see the specific registering page). Some dropdowns have a value followed by a description of that value like "value"- "description", only the value part should be in the file.</p>
  <p class="fw-bold">Desired table to insert</p>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>owner_institute</th>
        <th>location</th>
        <th>status</th>
        <th>serial</th>
        <th>beta</th>
        <th>nominal_hv</th>
        <th>qe</th>
        <th>model</th>
        <th>comments</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>CER</td>
        <td>Unknown</td>
        <td>Unknown</td>
        <td>BB0003</td>
        <td>6.482</td>
        <td>708.0</td>
        <td>20.9</td>
        <td>R11187</td>
        <td></td>
      </tr>
      <tr>
        <td>CER</td>
        <td>Unknown</td>
        <td>Unknown</td>
        <td>BB0003</td>
        <td>6.581</td>
        <td>687.0</td>
        <td>20.6</td>
        <td>R11187</td>
        <td></td>
      </tr>
    </tbody>
  </table>
  <p class="fw-bold">In the csv file</p>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>owner_institute,location,status,serial,beta,nominal_hv,qe,model,comments</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>CER,Unknown,Unknown,BB0003,6.482,708.0,20.9,R11187,</td>
      </tr>
      <tr>
        <td>CER,Unknown,Unknown,BB0003,6.581,687.0,20.6,R11187,</td>
      </tr>
    </tbody>
  </table>
</div>

<? $this->endSection()?>
