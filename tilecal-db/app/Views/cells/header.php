<header>
<nav class="navbar navbar-expand-lg bg-dark">
  <div class="container-fluid">
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
            <li><a class="dropdown-item" href="<? echo base_url('equipments'); ?>">Index</a></li>
            <? if ($user->isAuthorized('delete')):?>
              <li><a class="dropdown-item" href="<? echo base_url('equipments/deleted'); ?>">Deleted</a></li>
            <? endif; ?>
            <li><hr class="dropdown-divider"></li>
            <? foreach ($equipmentTypes as $equipType): ?>
              <li><a class="dropdown-item" href="<? echo base_url(sprintf('equipments?type_name=%s', $equipType->name)); ?>"><?echo $equipType->label;?></a></li>
            <? endforeach;?>
          </ul>
        </li>
        <? if(false): ?>
          <li class="nav-item dropdown" id="view-disassembly-navbar-dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
              Disassembly
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Phase 1 Drawer</a></li>
              <li><a class="dropdown-item" href="#">PMT Block</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown" id="view-assembly-navbar-dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
              Assembly
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Micro Drawer</a></li>
              <li><a class="dropdown-item" href="#">Mini Drawer</a></li>
              <li><a class="dropdown-item" href="#">Module</a></li>
              <li><a class="dropdown-item" href="#">PMT Block</a></li>
              <li><a class="dropdown-item" href="#">Super Drawer</a></li>
            </ul>
          </li>
        <?endif;?>
        <? if ($user->isAuthorized('register')): ?>
          <li class="nav-item dropdown" id="register-equipments-navbar-dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
              Register
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<? echo base_url('equipments/batch-register')?>">Batch Register</a></li>
              <li><hr class="dropdown-divider"></li>
              <? foreach ($equipmentTypes as $equipType): ?>
                <li><a class="dropdown-item" href="<? echo base_url(sprintf('equipments/%s/register', $equipType->name)); ?>"><?echo $equipType->label;?></a></li>
              <? endforeach;?>
            </ul>
          </li>
        <?endif; ?>
        <li class="nav-item dropdown" id="files-navbar-dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
            Files
          </a>
          <ul class="dropdown-menu" style="color: #ffffff;">
            <li><a class="dropdown-item" href="<? echo base_url('files'); ?>">Index</a></li>
            <? if ($user->isAuthorized('upload')):?>
              <li><a class="dropdown-item" href="<? echo base_url('files/upload'); ?>">Upload</a></li>
            <? endif; ?>
            <? if ($user->isAuthorized('delete')):?>
              <li><a class="dropdown-item" href="<? echo base_url('files/deleted'); ?>">Deleted</a></li>
            <? endif; ?>
          </ul>
        </li>
        <a class="nav-link active" href="<? echo base_url('help') ?>" style="color: #ffffff;">
          Help
        </a>
        <li class="nav-link active" style="color: #ffffff;">
          <? echo $user->username?>
        </li>
      </ul>
    </div>
  </div>
</nav>
</header>
