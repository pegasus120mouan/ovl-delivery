<!DOCTYPE html>
<html>

<head>
  <title>Édition des commandes</title>
</head>

<body>

  <?php
  // Connexion à la base de données (à adapter avec vos informations)
  require_once '../inc/functions/connexion.php';
  require_once '../inc/functions/requete/requete_commandes.php';
  include('header.php');

  // Récupération de l'ID de la commande depuis l'URL (par exemple, edit_commande.php?id=1)
  $id_commande = $_GET['id'];
  $livreur_id = $_GET['id_user'];

  /*echo   $id_commande;
  echo "<br>";
  echo $livreur_id;*/

  // Requête pour récupérer les anciennes valeurs de la commande
  $sql = "SELECT * FROM commandes WHERE id = :id_commande";
  $requete = $conn->prepare($sql);
  $requete->bindParam(':id_commande', $id_commande, PDO::PARAM_INT);
  $requete->execute();
  $modif_commandes = $requete->fetch(PDO::FETCH_ASSOC);
  // $rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);


  // $rowMontant = $montantGlobal->fetch(PDO::FETCH_ASSOC);
  //$montantColisGlobal = $rowMontant['montant_global_colis'];



  //$requete = $conn->query("SELECT nom_boutique FROM clients");   

  //$livreurs_selection = $conn->query("SELECT prenom_livreur FROM livreurs"); 

  //$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison"); 
  ?>
  <form action="traitement_update_commandes_par_clients.php?id=<?= $id_commande ?>&id_user=<?= $livreur_id ?>" method="post">


    <div class="form-group">
      <label for="exampleInputName1"></label>
      <input type="hidden" class="form-control" id="exampleInputName1" placeholder="Communes" name="id"
        value="<?php echo $modif_commandes['id']; ?>">

        <input type="hidden" class="form-control" id="exampleInputName1" placeholder="Communes" name="user_id"
        value="<?php echo $livreur_id = $_GET['id_user']; ?>">
    </div>

    <div class="form-group">
      <label for="exampleInputName1">Communes</label>
      <input type="text" class="form-control" id="exampleInputName1" placeholder="Communes" name="communes"
        value="<?php echo $modif_commandes['communes']; ?>">
    </div>
    <div class="form-group">
      <label for="exampleInputEmail3">Côut Global</label>
      <input type="text" class="form-control" id="exampleInputEmail3" placeholder="Côut Global" name="cout_global"
        value="<?php echo $modif_commandes['cout_global']; ?>">
    </div>
    <div class="form-group row">
      <label for="select" class="col-3 col-form-label">Coût Livraison</label>
      <div class="col-9">
        <?php
        echo  '<select id="select" name="livraison" class="custom-select">';
        while ($coutLivraison = $cout_livraison->fetch(PDO::FETCH_ASSOC)) {
          echo '<option value="' . $coutLivraison['cout_livraison'] . '">' . $coutLivraison['cout_livraison'] . '</option>';
        }
        echo '</select>'
        ?>
      </div>
      
    </div>
    <div class="form-group row">
      <label for="select" class="col-3 col-form-label">Statut Livraison</label>
      <div class="col-9">
       
        <select id="select" name="statut_livraison" class="custom-select">
      
        <option value="Non Livré">Non Livré</option>
        </select>
       
      </div>
      
    </div>
    <div class="form-group">
      <label for="exampleInputName1">Date</label>
      <input type="date" class="form-control" id="exampleInputName1" placeholder="date" name="date"
        value="<?php echo $modif_commandes['date_commande']; ?>">
    </div>
    </div>

    <button type="submit" class="btn btn-success mr-2" name="saveCommande">Modifier</button>
    <button class="btn btn-light">Annuler</button>
  </form>
</body>

</html>