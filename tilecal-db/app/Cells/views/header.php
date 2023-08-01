<header>
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
            <li><a class="dropdown-item" href="<? echo base_url('equipments/deleted'); ?>">Deleted</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/3_in_1_board'); ?>">3 in 1 Boards</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/carrier_board'); ?>">Carrier Board</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/cpm'); ?>">CPM</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/daughterboard'); ?>">Daughterboard</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/fenics'); ?>">FENICS</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/gbe_switch'); ?>">GbE Switch</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/hv_distribution_board'); ?>">HV Distribution Board</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/ipmc'); ?>">IPMC</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/mainboard'); ?>">Mainboard</a></li>
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
        <li class="nav-item dropdown" id="view-disassembly-navbar-dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
            Disassembly
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<? echo base_url('disassembly/phase_1_drawer'); ?>">Phase 1 Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('disassembly/pmt_block'); ?>">PMT Block</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown" id="view-assembly-navbar-dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
            Assembly
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<? echo base_url('equipments/micro_drawer'); ?>">Micro Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/mini_drawer'); ?>">Mini Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/module'); ?>">Module</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/pmt_block'); ?>">PMT Block</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/super_drawer'); ?>">Super Drawer</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown" id="register-equipments-navbar-dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
            Register
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<? echo base_url('equipments/batch-register')?>">Batch Register</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/3_in_1_board/register'); ?>">3 in 1 Boards</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/carrier_board/register'); ?>">Carrier Board</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/cpm/register'); ?>">CPM</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/daughterboard/register'); ?>">Daughterboard</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/micro_drawer/register'); ?>">Micro Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/fenics/register'); ?>">FENICS</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/gbe_switch/register'); ?>">GbE Switch</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/hv_distribution_board/register'); ?>">HV Distribution Board</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/ipmc/register'); ?>">IPMC</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/micro_drawer/register'); ?>">Micro Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/mainboard/register'); ?>">Mainboard</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/mini_drawer/register'); ?>">Mini Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/micro_drawer/register'); ?>">Micro Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/mini_drawer/register'); ?>">Mini Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/module/register'); ?>">Module</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/phase_1_drawer/register'); ?>">Phase 1 Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/phase_1_hv_divider/register'); ?>">Phase 1 HV Divider</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/pmt/register'); ?>">PMT</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/pmt_block/register'); ?>">PMT Block</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/super_drawer/register'); ?>">Super Drawer</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/tdaqi/register'); ?>">TDAQi</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('equipments/tilecom/register'); ?>">TileCoM</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown" id="files-navbar-dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
            Files
          </a>
          <ul class="dropdown-menu" style="color: #ffffff;">
            <li><a class="dropdown-item" href="<? echo base_url('files'); ?>">View</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('files/upload'); ?>">Upload</a></li>
            <li><a class="dropdown-item" href="<? echo base_url('files/deleted'); ?>">Deleted</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
</header>
