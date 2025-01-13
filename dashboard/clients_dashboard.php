<?php
require_once '../inc/functions/connexion.php';

include('header_clients.php');
require_once '../inc/functions/requete/clients/requete_commandes_clients.php';



$id_user = $_SESSION['user_id'];
$nom_user = $_SESSION['nom'];
$prenoms_user = $_SESSION['prenoms'];
$role_user = $_SESSION['user_role'];
//$login_user=$_SESSION['user_login'];

//echo $id_user;
$requete = $conn->prepare("SELECT
commandes.id as commande_id,
utilisateur_id, livreur_id, communes, cout_global,
cout_livraison, cout_reel, statut, date_commande, clients.id as id_client,
clients.nom as client_nom, prenoms, contact, login, avatar, boutique_id, boutiques.nom as boutique_nom
FROM `commandes`  
join (select * from utilisateurs where role = 'clients')  as clients on clients.id=commandes.utilisateur_id
join boutiques on clients.boutique_id=boutiques.id having utilisateur_id=:id_user order by date_commande DESC LIMIT 15");

// Liaison de la variable avec le paramètre de la requête
$requete->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$requete->execute();
$commandes = $requete->fetchAll();

$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$commande_pages = array_chunk($commandes, $limit );
//$commandes_list = $commande_pages[$_GET['page'] ?? ] ;
$commandes_list = $commande_pages[$page - 1] ?? [];

//requete Boutique //
$sql_boutique = "SELECT utilisateurs.id as utilisateur_id, 
 utilisateurs.nom as utilisateur_nom, 
 utilisateurs.prenoms as utilisateur_prenoms, 
 utilisateurs.contact as utilisateur_contact,
 utilisateurs.avatar as utilisateur_avatar,
 boutiques.nom as boutique_nom 
 FROM utilisateurs 
 JOIN boutiques ON utilisateurs.boutique_id = boutiques.id 
 WHERE utilisateurs.id = :id_user";

 $requete_boutique = $conn->prepare($sql_boutique);
 $requete_boutique->bindParam(':id_user', $id_user, PDO::PARAM_INT);
 $requete_boutique->execute();

?>
<!-- Style-->

<style>
  .pagination-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 20px;
}

.pagination-link {
    padding: 8px;
    text-decoration: none;
    color: white;
    background-color: #007bff; 
    border: 1px solid #007bff;
    border-radius: 4px; 
    margin-right: 4px;
}

.items-per-page-form {
    margin-left: 20px;
}

label {
    margin-right: 5px;
}

.items-per-page-select {
    padding: 6px;
    border-radius: 4px; 
}

.submit-button {
    padding: 6px 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px; 
    cursor: pointer;
}
 .custom-icon {
            color: green;
            font-size: 24px;
            margin-right: 8px;
 }
 .spacing {
    margin-right: 10px; 
    margin-bottom: 20px;
}

        @media only screen and (max-width: 767px) {
            
            th {
                display: none; 
            }
            tbody tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid #ccc;
                padding: 10px;
            }
            tbody tr td::before {

                font-weight: bold;
                margin-right: 5px;
            }
        }
        .margin-right-15 {
        margin-right: 15px;
       }
        .block-container {
      background-color:  #d7dbdd ;
      padding: 20px;
      border-radius: 5px;
      width: 100%;
      margin-bottom: 20px;
    }
    </style> 
    <!-- Fin style -->




<div class="row">

    <div class="block-container">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-commande">
      <i class="fa fa-edit"></i>Enregistrer une commande
    </button>

    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#add-point">
      <i class="fa fa-print"></i> Imprimer un point
    </button>

    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#search-commande">
      <i class="fa fa-search" disabled></i> Recherche un point
    </button>

   <button type="button" class="btn btn-dark" onclick="window.location.href='#'">
              <i class="fa fa-print"></i> Exporter la liste des commandes
             </button>
</div>

<!-- Main row -->
  <!--<a href="commandes_update.php"><i class="fa fa-print" style="font-size:24px;color:green">Imprimer point du jour</i></a>
                <button type="button"  class="btn btn-primary"><i class="fa fa-print"></i> Imprimer point du jour</button>-->
  <table id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Communes</th>
        <th>Coût Global</th>
        <th>Livraison</th>
        <th>Côut réel</th>
        <th>Client</th>
        <th>Statut</th>
        <th>Date de la commande</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($commandes_list as $commande) : ?>
      <tr>
        <td><?= $commande['communes'] ?></td>
        <td><?= $commande['cout_global'] ?></td>
        <td><?= $commande['cout_livraison'] ?></td>
        <td><?= $commande['cout_reel'] ?></td>
        <td><?= $commande['boutique_nom'] ?></td>


        <td>
          <?php if ($commande['statut'] !== null) : ?>
          <?php if ($commande['statut'] == 'Non Livré') : ?>
          <span class="badge badge-danger badge-lg"><?= $commande['statut'] ?></span>
          <?php else : ?>
          <span class="badge badge-success badge-lg"><?= $commande['statut'] ?></span>
          <?php endif; ?>
          <?php else : ?>
          <span class="badge badge-success badge-lg">Pas de point</span>
          <?php endif; ?>
        </td>




        <td><?= $commande['date_commande'] ?></td>

        <td class="actions">
            <?php
            $statut = $commande['statut']; // Exemple : "Livré" ou "Non Livré"
            $date_commande = $commande['date_commande']; // Date de la commande
            $today = date('Y-m-d'); // Date actuelle

            // Vérifie si les conditions sont remplies
            $disabled = ($statut === 'Livré' || $statut === 'Non Livré') && $date_commande !== $today ? 'disabled' : '';
            ?>
            <a href="update_commande_client.php?id=<?= $commande['commande_id'] ?>" class="edit">
              <i class="fas fa-pen fa-xs" style="font-size:24px;color:blue; <?= $disabled ? 'pointer-events:none; opacity:0.5;' : '' ?>"></i>
            </a>
            <a href="delete_commande_client.php?id=<?= $commande['commande_id'] ?>" class="trash">
              <i class="fas fa-trash fa-xs" style="font-size:24px;color:red; <?= $disabled ? 'pointer-events:none; opacity:0.5;' : '' ?>"></i>
            </a>
          </td>

      </tr>
      <div class="modal" id="update-<?= $commande['commande_id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
              <form action="traitement_commande_livreurs_update.php" method="post">
                <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
                <div class="form-group">
                  <label>Livreur</label>
                  <select name="livreur_id" class="form-control">
                    <?php
                      foreach ($rows as $row) {
                        echo '<option value="' . $row['id'] . '">' . $row['livreur_name'] . '</option>';
                      }
                      ?></select>

                </div>
                <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Attribuer</button>
                <button class="btn btn-light">Annuler</button>
              </form>
            </div>
          </div>
        </div>
      </div>



      <div class="modal" id="update_statut-<?= $commande['commande_id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
              <form action="traitement_commande_statut_update.php" method="post">
                <input type="hidden" name="commande_id" value="<?= $commande['commande_id'] ?>">

                <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Changer le statut</button>
                <button class="btn btn-light">Annuler</button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if($page > 1 ): ?>
        <a href="?page=<?= $page - 1 ?>" class="btn btn-primary"><</a>
    <?php endif; ?>

    <span><?= $page . '/' . count($commande_pages) ?></span>

    <?php if($page < count($commande_pages)): ?>
        <a href="?page=<?= $page + 1 ?>" class="btn btn-primary">></a>
    <?php endif; ?>

    <form action="" method="get" class="items-per-page-form">
        <label for="limit">Afficher :</label>
        <select name="limit" id="limit" class="items-per-page-select">
            <option value="5" <?php if ($limit == 5) { echo 'selected'; } ?> >5</option>
            <option value="10" <?php if ($limit == 10) { echo 'selected'; } ?>>10</option>
            <option value="15" <?php if ($limit == 15) { echo 'selected'; } ?>>15</option>
        </select>
        <button type="submit" class="submit-button">Valider</button>
    </form>
</div>


  <div class="modal fade" id="add-commande">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer une commande</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="enregistrement/save_commande_client.php">
            <div class="card-body">
              <div class="form-group">
                <label for="exampleInputEmail1">Communes</label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Commune destination"
                  name="communes">
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Côut Global</label>
                <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Coût global Colis"
                  name="cout_global">
              </div>
              <div class="form-group">
                <label>Côut Livraison</label>
                <?php
                echo  '<select id="select" name="livraison" class="form-control">';
                while ($coutLivraison = $cout_livraison->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $coutLivraison['cout_livraison'] . '">' . $coutLivraison['cout_livraison'] . '</option>';
                }
                echo '</select>'
                ?>
              </div>

              <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Enregister</button>
              <button class="btn btn-light">Annuler</button>
            </div>
          </form>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>


    <!-- /.modal-dialog -->
  </div>

  <div class="modal fade" id="search-commande-communes">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche par Communes</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="GET" action="page_recherche.php">
          <div class="card-body">
            <div class="form-group">
              <label for="communeInput">Entrez la commune</label>
              <input type="text" class="form-control" id="communeInput" placeholder="Recherche une commune" name="recherche">
            </div>
            <button type="submit" class="btn btn-primary mr-2">Recherche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Recherche par Date -->
<div class="modal fade" id="search-commande-date">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche par Date</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="GET" action="page_recherche_date.php">
          <div class="card-body">
            <div class="form-group">
              <label for="dateInput">Sélectionner la date</label>
              <input type="date" class="form-control" id="dateInput" name="date">
            </div>
            <button type="submit" class="btn btn-primary mr-2">Recherche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Recherche par Livreur -->


<!-- Recherche par Client -->


<!-- Recherche par Statut -->
<div class="modal fade" id="search-commande-statut">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche par Statut</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="GET" action="page_recherche_statut.php">
          <div class="card-body">
            <div class="form-group">
                    <label>Selectionner le statut</label>
                    <select name="statut" class="form-control">
                    <option value="Livré">Livré</option>
                    <option value="Non Livré">Non Livré</option>
                      
                      </select>

                  </div>
            <button type="submit" class="btn btn-primary mr-2">Recherche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

  <!-- Main Search Modal -->
<div class="modal fade" id="search-commande">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche un point</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
      <button type="button" class="btn btn-outline-dark w-100 mb-2" onclick="showSearchModal('search-commande-communes')">
          <i class="fa fa-map-marker"></i>Recherche par Communes
      </button>  

       <button type="button" class="btn btn-outline-dark w-100 mb-2" onclick="showSearchModal('search-commande-date')">
       <i class="fa fa-calendar"></i>Recherche par Date
       </button>


        <button type="button" class="btn btn-outline-dark w-100 mb-2" onclick="showSearchModal('search-commande-statut')">
        <i class="fa fa-check-circle"></i>Recherche par Statut
        </button>
      </div>
    </div>
  </div>
</div>


  <div class="modal fade" id="add-point">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <form class="forms-sample" method="post" action="traitement/traitement_clients_commandes_print.php">
                        <div class="card-body">
                           <div class="form-group">
                            <select  name="client" class="form-control">
                            <?php
                                while ($selection = $requete_boutique->fetch()) 
                              {
                              echo '<option value="' . $selection['boutique_nom'] . '">' . $selection['boutique_nom'] . '</option>';
                              }
                            ?></select>

                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Sélectionner la date</label>
                                  <input id="date" name="date" type="date" class="form-control">
                                  </div>
                            <input type="submit" class="btn btn-primary mr-2" value="Imprimer">
                        </div>
                    </form>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

</div>

<!-- /.row (main row) -->
</div><!-- /.container-fluid -->
<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<!-- <footer class="main-footer">
    <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.2.0
    </div>
  </footer>-->

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
  <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- <script>
  $.widget.bridge('uibutton', $.ui.button)
</script>-->
<!-- Bootstrap 4 -->
<script src="../plugins/sweetalert2/sweetalert2.min.js"></script>

<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="../plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="../plugins/moment/moment.min.js"></script>
<script src="../plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="../plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.js"></script>
<?php

if (isset($_SESSION['popup']) && $_SESSION['popup'] ==  true) {
?>
<script>
var Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000
});

Toast.fire({
  icon: 'success',
  title: 'Action effectuée avec succès.'
})
</script>

<?php
  $_SESSION['popup'] = false;
}
?>
<script>
function showSearchModal(modalId) {
  // Hide all modals
  document.querySelectorAll('.modal').forEach(modal => {
    $(modal).modal('hide');
  });

  // Show the selected modal
  $('#' + modalId).modal('show');
}
</script>


<!------- Delete Pop--->
<?php

if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] ==  true) {
?>
<script>
var Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000
});

Toast.fire({
  icon: 'error',
  title: 'Action échouée.'
})
</script>

<?php
  $_SESSION['delete_pop'] = false;
}
?>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!--<script src="dist/js/pages/dashboard.js"></script>-->
</body>

</html>