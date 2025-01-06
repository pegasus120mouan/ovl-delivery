<?php
header('Content-Type: text/html; charset=UTF-8');

setlocale(LC_TIME, 'fr_FR.utf8', 'fra');  // Force la configuration en français

require_once '../inc/functions/connexion.php';


$sql_cg = "SELECT SUM(cout_global) AS total_cout_global
        FROM commandes
        WHERE statut = 'Livré' AND date_commande >= DATE_FORMAT(NOW(), '%Y-%m-01')";
$requAdmin = $conn->prepare($sql_cg);
$requAdmin->execute();
$header_calcul = $requAdmin->fetch(PDO::FETCH_ASSOC);


// Coût réel livraisons
$sql_cr = "SELECT SUM(cout_reel) AS total_cout_reel
        FROM commandes
        WHERE statut = 'Livré' AND date_commande >= DATE_FORMAT(NOW(), '%Y-%m-01')";

$requete_cr = $conn->prepare($sql_cr);
$requete_cr->execute();
$header_calcul_cr = $requete_cr->fetch(PDO::FETCH_ASSOC);


// Coût livraisons
/*$sql_cl = "SELECT SUM(cout_livraison) AS total_cout_livraison
        FROM commandes
        WHERE statut = 'Livré' AND date_commande >= DATE_FORMAT(NOW(), '%Y-%m-01')";*/

$sql_cl = "SELECT SUM(gain_jour) AS total_cout_livraison
        FROM points_livreurs
        WHERE date_commande >= DATE_FORMAT(NOW(), '%Y-%m-01')";

$requete_cl = $conn->prepare($sql_cl);
$requete_cl->execute();
$header_calcul_cl = $requete_cl->fetch(PDO::FETCH_ASSOC);


// Nombre de colis livrés
$sql_colis = "SELECT COUNT(*) AS total_delivered_packages
FROM commandes
WHERE statut = 'Livré' AND date_commande >= DATE_FORMAT(NOW(), '%Y-%m-01')";

$requete_colis = $conn->prepare($sql_colis);
$requete_colis->execute();
$header_calcul_nb = $requete_colis->fetch(PDO::FETCH_ASSOC);


$query_commandes = $conn->prepare("SELECT COUNT(*) as count_livrees FROM commandes 
    WHERE statut = 'livré' 
    AND DATE(date_commande) = CURDATE()");
$query_commandes->execute();
$commandes_livrees = $query_commandes->fetch(PDO::FETCH_ASSOC);
$count_livrees = $commandes_livrees['count_livrees'];


$query_commandes_non_livrees = $conn->prepare("SELECT COUNT(*) as count_non_livrees FROM commandes 
    WHERE statut = 'Non Livré' 
    AND DATE(date_commande) = CURDATE()");
$query_commandes_non_livrees->execute();
$commandes_non_livrees = $query_commandes_non_livrees->fetch(PDO::FETCH_ASSOC);
$count_non_livrees = $commandes_non_livrees['count_non_livrees'];


if (!isset($_SESSION['user_id'])) {
    // Redirigez vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: ../index.php");
    exit();
  }


//$stmt = $conn->prepare("SELECT * FROM users");
//$stmt->execute();
//$users = $stmt->fetchAll();
//foreach($users as $user)
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tableau de bord</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="icon" href="../dist/img/logo.png" type="image/x-icon">
  <link rel="shortcut icon" href="../dist/img/logo.png" type="image/x-icon">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="../../plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../../plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../../plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
  <link rel="stylesheet" href="../../plugins/summernote/summernote-bs4.min.css">
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> 
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
  <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
  <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.8.0/mapbox-gl.css" rel="stylesheet">

</head>
<style>.power-off-btn i {
  font-size: 2rem;  /* Augmente la taille de l'icône */
  color: red !important; /* Assure que l'icône est rouge */
}

.power-off-btn i:hover {
  color: darkred !important; /* Change la couleur au survol */
  transform: scale(1.2); /* Ajoute un effet d'agrandissement au survol */
  transition: transform 0.2s, color 0.2s; /* Animation douce */
}


</style>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <!-- Preloader 
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div>-->

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="dashboard.php" class="nav-link">Acceuil</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="commandes.php" class="nav-link">Les commandes</a>
        </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        <li class="nav-item">
          <a class="nav-link" data-widget="navbar-search" href="recherche_colis.php" role="button">
            <i class="fas fa-search"></i>
          </a>
          <div class="navbar-search-block">
            <form class="form-inline">
              <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar" type="search" name="communeInput" placeholder="Recherche un colis" aria-label="Search">
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

        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            <span class="badge badge-warning navbar-badge">15</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <span class="dropdown-item dropdown-header">0 Notifications</span>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> <?php echo $count_non_livrees; ?> Commandes Non validées
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> <?php echo $count_livrees; ?> Commandes validées
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-file mr-2"></i> 3 new reports
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item dropdown-footer">Toutes les notifications</a>
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
            <i class="fas fa-expand-arrows-alt"></i>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link power-off-btn" href="../logout.php" role="button">
            <i class="fas fa-power-off"></i>
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
            <img src="../dossiers_images/<?php echo $_SESSION['avatar']; ?>" class="img-circle elevation-2" alt="Logo">
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
                  <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-motorcycle"></i>
                    <p>Bilan aujourd'hui</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="vuegeneral.php" class="nav-link">
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
                  <a href="point_client.php" class="nav-link">
                    <i class="fas fa-balance-scale"></i>
                    <p>Points par clients</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="point_livraison.php" class="nav-link">
                    <i class="fas fa-wallet"></i>
                    <p>Points des Livreurs</p>
                  </a>
                </li>
                 <li class="nav-item">
                  <a href="liste_montants.php" class="nav-link">
                    <i class="fas fa-list"></i>
                    <p>Liste des montants</p>
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
                  <a href="commandes.php" class="nav-link">
                    <i class="fas fa-clone"></i>
                    <p>Liste des commandes</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="commandes_livrees.php" class="nav-link">
                    <i class="far fa-clone"></i>
                    <p>Commandes Livrées</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="commandes_non_livrees.php" class="nav-link">
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
                  <a href="clients.php" class="nav-link">
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
                  <a href="listes_engins.php" class="nav-link">
                    <i class="fas fa-bicycle"></i>
                    <p>Listes des engins</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="contrats.php" class="nav-link">
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
                  <a href="liste_livreurs.php" class="nav-link">
                    <i class="fas fa-male	"></i>
                    <p>Listes des livreurs</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="liste_admins.php" class="nav-link">
                  <i class="fas fa-user-tie"></i>
                    <p>Listes des admins</p>
                  </a>
                </li>

                 <li class="nav-item">
                  <a href="gestion_access.php" class="nav-link">
                  <i class="fas fa-lock"></i>
                    <p>Gestion des acccès</p>
                  </a>
                </li>


              </ul>
            </li>
             <li class="nav-item">
              <a href="cout_livraison.php" class="nav-link">
                <i class="nav-icon far fa-calendar-alt"></i>
                <p>
                  Coût Livraison
                </p>
              </a>
             </li>





          <li class="nav-header"><strong>GESTION CAISSE</strong></li>
             <li class="nav-item">
              <a href="analytics/vuegenerale_soldes.php" class="nav-link">
                <i class="nav-icon far fa-calendar-alt"></i>
                <p>
                  Soldes
                  <span class="badge badge-info right">2</span>
                </p>
              </a>
             </li>
            <li class="nav-item">
              <a href="analytics/vue_gestion_caisse.php" class="nav-link">
                <i class="nav-icon far fa-image"></i>
                <p>
                  Caisse
                </p>
              </a>
            </li>


                        <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-chart-pie"></i>
                <p>
                  Points des dépots
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="listes_des_depots.php" class="nav-link">
                    <i class="fas fa-clone"></i>
                    <p>Liste des dépots</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="commandes_livrees.php" class="nav-link">
                    <i class="far fa-clone"></i>
                    <p>Commandes Livrées</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="commandes_non_livrees.php" class="nav-link">
                    <i class="fas fa-file"></i>
                    <p>Commandes non livrées</p>
                  </a>
                </li>
              </ul>
            </li>


            <li class="nav-item">
              <a href="analytics/vue_gestion_caisse.php" class="nav-link">
                <i class="nav-icon far fa-image"></i>
                <p>
                  Caisse
                </p>
              </a>
            </li>

              <li class="nav-item">
              <a href="analytics/vue_ovl_statisques.php" class="nav-link">
                <i class="nav-icon fas fa-balance-scale"></i>
                <p>
                 <strong>Mes statisques</strong>
                </p>
              </a>
            </li>

            <li class="nav-item">
              <a href="dettes.php" class="nav-link">
                <i class='fas fa-coins'></i>
                <p>
                  Dettes
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="imprevus.php" class="nav-link">
                <i class='fas fa-question-circle'></i>
                <p>
                  Imprevus
                </p>
              </a>
           </li>

           <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fa fa-camera-retro"></i>
                <p>
                  Banners
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="banner_admin.php" class="nav-link">
                   <i class="fa fa-solid fa-camera"></i>
                    <p>Banner Administrateur</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="livreurs_analytics.php" class="nav-link">
                    <i class="fa fa-solid fa-image"></i>
                    <p>Livreurs</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="gestion_caisse.php" class="nav-link">
                    <i class="fa fa-pie-chart"></i>
                    <p>Caisse</p>
                  </a>
                </li>
              </ul>
            </li>




           <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fa fa-area-chart"></i>
                <p>
                  Analytics
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="clients_analytics.php" class="nav-link">
                    <i class="fa fa-bar-chart"></i>
                    <p>Clients</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="livreurs_analytics.php" class="nav-link">
                    <i class="fa fa-line-chart"></i>
                    <p>Livreurs</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="gestion_caisse.php" class="nav-link">
                    <i class="fa fa-pie-chart"></i>
                    <p>OVL</p>
                  </a>
                </li>
              </ul>
            </li>
            
              <li class="nav-item">
              <a href="geolocalisation.php" class="nav-link">
              <i class="fa fa-map-marker"></i>

                <p>
                  Géolocalisation
                </p>
              </a>
           </li>


            <li class="nav-header"><strong>GESTION DES PAIES</strong></li>
             <li class="nav-item">
              <a href="listes_employes.php" class="nav-link">
                <i class="nav-icon far fa-calendar-alt"></i>
                <p>
                  Listes des employés
                  <span class="badge badge-info right">2</span>
                </p>
              </a>
             </li>
            <li class="nav-item">
              <a href="analytics/vue_gestion_caisse.php" class="nav-link">
                <i class="nav-icon far fa-image"></i>
                <p>
                  Gestion des paiements
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
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Tableau de bord</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Acceuil</a></li>
                <li class="breadcrumb-item active"><?php echo $_SESSION['user_role']; ?></li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <!-- Small boxes (Stat box) -->
          <div class="row">
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-info">
                <div class="inner">
                  <h3><?php echo $header_calcul['total_cout_global'];   ?>
                  <span class="right badge badge-dark">CFA</span>
                </h3>
                <p>Montant Global de <strong><?php echo ucfirst(strftime('%B')); ?></strong></p>

                </div>
              </div>
            </div>
            
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-success">
                <div class="inner">
                <h3><?php echo $header_calcul_cr['total_cout_reel'];?>
                <span class="right badge badge-dark">CFA</span>
               </h3>
               <p>Montant clients de <strong><?php echo ucfirst(strftime('%B')); ?></strong></p>
                </div>
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-warning">
                <div class="inner">
                <h3><?php echo $header_calcul_cl['total_cout_livraison'];?>
                <span class="right badge badge-dark">CFA</span>
                </h3>
                <p>Gain <strong><?php echo ucfirst(strftime('%B')); ?></strong></p>

                </div>
                 </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-danger">
                <div class="inner">
                 <h3><?php echo $header_calcul_nb['total_delivered_packages'];?>
                </h3>
                <p>Nbre de colis livrés en <strong><?php echo ucfirst(strftime('%B')); ?></strong></p>
                </div>
              </div>
            </div>
            <!-- ./col -->
          </div>
          <!-- /.row -->