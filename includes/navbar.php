<?php
$datos_usuario= $pdo->query("SELECT * FROM usuarios WHERE id_usuario = ".$_SESSION['user_id']."")->fetch(PDO::FETCH_ASSOC);
$id_usuario=$datos_usuario['id_usuario'];
$is_admin=$datos_usuario['is_admin'];
?>
<nav class="navbar bg-primary fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php" style="color:white;">SUMED</a>
    <button class="navbar-toggler" type="button" style="color:white;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasNavbarLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        
      </div>
      <div class="offcanvas-body">
            <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="index.php">INICIO</a>
                <span   class="me-3"><?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></span>
                <br>
              </li>
              <li class="nav-item">
                  <div class="d-flex align-items-center">
                    <a href="almacenes.php" class="btn btn-sm btn-light">
                    <i class="bi bi-archive-fill "></i>&nbsp&nbsp Almacenes
                  </a>
                  </div>
              </li>
           <?php 
              if ($is_admin === 1 || $is_admin === 2){
                echo"  
              <li class='nav-item'>
                  <div class='d-flex align-items-center'>
                    <a href='movimientos.php' class='btn btn-sm btn-light'>
                    <i class='bi bi-arrow-left-right'></i>&nbsp&nbsp Movimientos
                  </a>
                  </div>
              </li>
               
              <li class='nav-item'>
                  <div class='d-flex align-items-center'>
                    <a href='barcode_form.php' class='btn btn-sm btn-light'>
                    <i class='bi bi-upc-scan'></i>&nbsp&nbsp Codigos de barras
                  </a>
                  </div>
              </li>
              <li class='nav-item'>
                  <div class='d-flex align-items-center'>
                    <a href='consulta_traspasos.php' class='btn btn-sm btn-light'>
                    <i class='bi bi-box-arrow-right'></i>&nbsp&nbsp Traspasos
                  </a>
                  </div>
              </li>
              <li class='nav-item'>
                  <div class='d-flex align-items-center'>
                    <a href='traspasos.php' class='btn btn-sm btn-light'>
                    <i class='bi bi-box-arrow-right'></i>&nbsp&nbsp Nuevo Traspaso
                  </a>
                  </div>
              </li>
              
              ";
              }?>
              <?php 
              if ($is_admin === 1 ){
                echo"
              <li class='nav-item'>
                  <div class='d-flex align-items-center'>
                    <a href='usuarios.php' class='btn btn-sm btn-light'>
                    <i class='bi bi-person'></i>&nbsp&nbsp Usuarios
                  </a>
                  </div>
              </li>";
              }
              ?>
             
              <li class="nav-item">
                  <div class="d-flex align-items-center" style="color:black">
                  
                    <a href="logout.php" class="btn btn-sm btn-light"> <i class="bi bi-box-arrow-in-right"> </i>
                    Cerrar sesión
                  </a>
                  </div>
              </li>
              <!-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    MENU
                    </a> 
                <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="#">Action</a></li>
                      <li><a class="dropdown-item" href="#">Another action</a></li>
                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li><a class="dropdown-item" href="#">Something else here</a></li>
                </ul>
              </li> -->
            </ul>
            <!-- <form class="d-flex mt-3" role="search">
              <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
              <button class="btn btn-outline-success" type="submit">Search</button>
            </form> -->
      </div>
    </div>
  </div>
</nav>