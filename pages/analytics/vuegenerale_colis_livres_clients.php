<?php
require_once '../../inc/functions/connexion.php';

$date = date("d/m/Y");

$id_boutique=$_GET['id'];


$stmt = $conn->prepare("SELECT 
    commandes.*, 
    boutiques.nom AS nom_boutique, 
    CONCAT(livreur.nom, ' ', livreur.prenoms) AS fullname,
    livreur.nom AS nom_livreur, 
    livreur.prenoms AS prenoms_livreur
FROM 
    commandes
JOIN 
    utilisateurs AS livreur ON commandes.livreur_id = livreur.id
JOIN 
    utilisateurs AS client ON commandes.utilisateur_id = client.id
JOIN 
    boutiques ON client.boutique_id = boutiques.id
WHERE 
    DATE(commandes.date_commande) = CURDATE() 
    AND commandes.statut = 'Livré'
    AND client.id = :id_boutique");
//$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':id_boutique', $id_boutique, PDO::PARAM_INT);
$stmt->execute();
$commandes_livres_clients = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Liste des colis livrés</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="../dashboard.php" class="nav-link">Acceuil</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="../commandes.php" class="nav-link">Les commandes</a>
        </li>
      </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>

      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="../../dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Brad Diesel
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="../../dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  John Pierce
                  <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">I got your message bro</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="../../dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Nora Silvester
                  <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">The subject goes here</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
        </div>
      </li>
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">15</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">15 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 4 new messages
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 3 new reports
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="commandes.php" class="brand-link">
        <img src="../../dist/img/logo.png" alt="OVL Delivery Services" class="brand-image img-circle elevation-3"
          style="opacity: .8">
        <span class="brand-text font-weight-light">OVL Delivery Services</span>
      </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            <img src="../../dossiers_images/<?php echo $_SESSION['avatar']; ?>" class="img-circle elevation-2" alt="Logo">
            <!-- <img src="../../dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">-->
          </div>
          <div class="info">
            <a href="#" class="d-block"><?php echo $_SESSION['nom']; ?> <?php echo $_SESSION['prenoms']; ?></a>
          </div>
        </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
            <li class="nav-item menu-open">
              <a href="#" class="nav-link active">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>
                  Mes tableaux de bords
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="../dashboard.php" class="nav-link active">
                    <i class="fas fa-motorcycle"></i>
                    <p>Bilan aujourd'hui</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="../vuegeneral.php" class="nav-link">
                    <i class="fas fa-bicycle"></i>
                    <p>Bilan Hier</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fas fa-money-bill-wave-alt"></i>
                <p>
                  Points de livraisons
                  <i class="fas fa-angle-left right"></i>
                  <span class="badge badge-info right">6</span>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="../point_client.php" class="nav-link">
                    <i class="fas fa-balance-scale"></i>
                    <p>Points par clients</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="../point_livraison.php" class="nav-link">
                    <i class="fas fa-wallet"></i>
                    <p>Points des Livreurs</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-chart-pie"></i>
                <p>
                  Commandes
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="../commandes.php" class="nav-link">
                    <i class="fas fa-clone"></i>
                    <p>Liste des commandes</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="../commandes_livrees.php" class="nav-link">
                    <i class="far fa-clone"></i>
                    <p>Commandes Livrées</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="../commandes_non_livrees.php" class="nav-link">
                    <i class="fas fa-file"></i>
                    <p>Commandes non livrées</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fas fa-male"></i>
                <p>
                  Clients
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="../clients.php" class="nav-link">
                    <i class="fas fa-user-alt"></i>
                    <p>Liste des clients</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="pages/UI/general.html" class="nav-link">
                    <i class="fas fa-user-tie"></i>
                    <p>Particulier</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="pages/UI/icons.html" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Boutique</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fas fa-taxi"></i>
              
                <p>
                  Engins
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="pages/tables/simple.html" class="nav-link">
                    <i class="fas fa-bicycle"></i>
                    <p>Listes des engins</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="pages/tables/data.html" class="nav-link">
                    <i class="fas fa-folder-open"></i>
                    <p>Contrats</p>
                  </a>
                </li>

              </ul>
            </li>



            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-table"></i>
                <p>
                  Listes des utilisateurs
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="../liste_livreurs.php" class="nav-link">
                    <i class="fas fa-male	"></i>
                    <p>Listes des livreurs</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="../liste_admins.php" class="nav-link">
                  <i class="fas fa-user-tie"></i>
                    <p>Listes des admins</p>
                  </a>
                </li>


              </ul>
            </li>
          <li class="nav-header">CAISSE</li>
             <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon far fa-calendar-alt"></i>
                <p>
                  Soldes
                  <span class="badge badge-info right">2</span>
                </p>
              </a>
             </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon far fa-image"></i>
                <p>
                  Caisse
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="pages/kanban.html" class="nav-link">
                <i class="far fa-money-bill-alt"></i>
                <p>
                  Dépenses
                </p>
              </a>
           </li>
          


           <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fa fa-area-chart"></i>
                <p>
                  STATISTIQUES
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="../clients_analytics.php" class="nav-link">
                    <i class="fa fa-bar-chart"></i>
                    <p>Clients</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="../liste_admins.php" class="nav-link">
                    <i class="fa fa-line-chart"></i>
                    <p>Livreurs</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="../liste_admins.php" class="nav-link">
                    <i class="fa fa-pie-chart"></i>
                    <p>Caisse</p>
                  </a>
                </li>
              </ul>
            </li>

            <li class="nav-item">
              <a href="../../logout.php" class="nav-link">
                <i class="fa fa-sign-out"></i>
                <p>
                  Déconnexion
                </p>
              </a>
           </li>

          </ul>
        </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Listes des colis livrés <?php echo $date; ?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Vue Génerale</a></li>
              <li class="breadcrumb-item active">Colis Livrés</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">

            <!-- /.card -->

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                <?php echo $commandes_livres_clients[0]['nom_boutique']; ?>                </h3>

                </h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                <th>Communes</th>
                <th>Côut Global</th>
                <th>Livraison</th>
                <th>Côut réel</th>
                <th>Nom Boutique</th>
                <th>Livreur</th>
                <th>Statut</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($commandes_livres_clients as $commandes_livres_client): ?>
                  <tr>
                  <td><?=$commandes_livres_client['communes']?></td>
                  <td><?=$commandes_livres_client['cout_global']?></td>
                  <td><?=$commandes_livres_client['cout_livraison']?></td>
                  <td><?=$commandes_livres_client['cout_reel']?></td>
                  <td><?=$commandes_livres_client['nom_boutique']?></td>
                  <td><?=$commandes_livres_client['fullname']?></td>
                  <td><?=$commandes_livres_client['statut']?></td>
                  </tr>
                  </tbody>
                  <?php endforeach; ?>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper --><!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->

<script>
  $(function () {
    $("#example1").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
  });
</script>
</body>
</html>
