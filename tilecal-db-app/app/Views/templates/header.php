<!doctype html>
<html lang="en">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>TileCal Electronics Database Header </title>
  <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/b-2.3.6/datatables.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" type="text/css" href="assets/css/equipment/index.css">
</head>

<body>
<nav class="navbar navbar-expand-lg bg-dark">
  <div class="container">
    <a class="navbar-brand" href="<? echo base_url()?>" style="color: #ffffff;">Tilecal Electronics Database</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto justify-content-end">
      <li class="nav-item dropdown" id="view-equipments-navbar-dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
            Equipments
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<? echo base_url('equipments'); ?>">All</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/3_in_1_board'); ?>">3 in 1 Boards</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/carrier_board'); ?>">Carrier Board</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/cpm'); ?>">CPM</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/daughterboard'); ?>">Daughterboard</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/micro_drawer'); ?>">Micro Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/fenics'); ?>">FENICS</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/gbe_switch'); ?>">GbE Switch</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/hv_distribution_board'); ?>">HV Distribution Board</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/ipmc'); ?>">IPMC</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/micro_drawer'); ?>">Micro Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/mainboard'); ?>">Mainboard</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/mini_drawer'); ?>">Mini Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/micro_drawer'); ?>">Micro Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/mini_drawer'); ?>">Mini Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/module'); ?>">Module</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/phase_1_drawer'); ?>">Phase 1 Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/phase_1_hv_divider'); ?>">Phase 1 HV Divider</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/pmt'); ?>">PMT</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/pmt_block'); ?>">PMT Block</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/super_drawer'); ?>">Super Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/tdaqi'); ?>">TDAQi</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/tilecom'); ?>">TileCoM</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown" id="register-equipments-navbar-dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
            Register
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<? echo base_url('equipments/batch-register')?>">Batch Register</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register')?>">Equipment</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/3_in_1_board'); ?>">3 in 1 Boards</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/carrier_board'); ?>">Carrier Board</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/cpm'); ?>">CPM</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/daughterboard'); ?>">Daughterboard</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/micro_drawer'); ?>">Micro Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/fenics'); ?>">FENICS</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/gbe_switch'); ?>">GbE Switch</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/hv_distribution_board'); ?>">HV Distribution Board</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/ipmc'); ?>">IPMC</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/micro_drawer'); ?>">Micro Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/mainboard'); ?>">Mainboard</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/mini_drawer'); ?>">Mini Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/micro_drawer'); ?>">Micro Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/mini_drawer'); ?>">Mini Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/module'); ?>">Module</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/phase_1_drawer'); ?>">Phase 1 Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/phase_1_hv_divider'); ?>">Phase 1 HV Divider</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/pmt'); ?>">PMT</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/pmt_block'); ?>">PMT Block</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/super_drawer'); ?>">Super Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/tdaqi'); ?>">TDAQi</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/register/tilecom'); ?>">TileCoM</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown" id="files-navbar-dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
            Files
          </a>
          <ul class="dropdown-menu" style="color: #ffffff;">
            <li><a class="dropdown-item" href="<? echo base_url('files'); ?>">View</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('files/upload'); ?>">Upload</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
</body>
</html>