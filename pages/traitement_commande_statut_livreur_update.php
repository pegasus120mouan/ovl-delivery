<?php
// Connexion à la base de données (à adapter avec vos informations)
require_once '../inc/functions/connexion.php';   
//session_start(); 

// Récupération des données soumises via le formulaire
$commande_id = $_POST['commande_id'];
$statut = $_POST['statut'];
$livreur_id = $_GET['id'];

// Requête SQL d'update
$sql = "UPDATE commandes
        SET statut = :statut
        WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
  ':id' => $commande_id,
  ':statut' => $statut
));

// Redirection vebarrs une page de confirmation ou de retour
//$query_execute = $requete->execute($data);

//var_dump($query_execute);
//die();
if ($query_execute) {
  // $_SESSION['message'] = "Insertion reussie";
  $_SESSION['popup'] = true;
   header('Location: commandes_livreurs.php?id='.$livreur_id);
  exit(0);
} else {
  $_SESSION['delete_pop'] = true;
   header('Location: commandes_livreurs.php?id='.$livreur_id);
  exit(0);
}