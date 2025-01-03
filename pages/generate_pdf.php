<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';

// Récupérer les données JSON envoyées
$data = json_decode(file_get_contents('php://input'), true);
$client_id = $data['clientId'];
$year = $data['year'];

// Créer le PDF
class PDF extends FPDF {
    function Header() {
        $this->Image('../dist/img/logo.png', 10, 10, 30);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'OVL DELIVERY SERVICES', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Sarl au Capital de 1 000 000 CFA', 0, 1, 'C');
        $this->Cell(0, 5, 'Cocody Riviera Golf en face de l\'Ambassade des USA', 0, 1, 'C');
        $this->Cell(0, 5, 'Tel: +225 0787703000 - +22505848282385', 0, 1, 'C');
        $this->Cell(0, 5, 'Email: finance@ovl-delivery.online', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();

// Ajouter une page
$pdf->AddPage();

// Titre
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "Rapport Statistique " . $year, 0, 1, 'C');
$pdf->Ln(5);

// Statistiques globales
$stmt_total = $conn->prepare("SELECT COUNT(*) as total_livres, SUM(cout_reel) as total_cout FROM commandes WHERE utilisateur_id = ? AND YEAR(date_commande) = ? AND statut = 'livré'");
$stmt_total->execute([$client_id, $year]);
$stats_total = $stmt_total->fetch(PDO::FETCH_ASSOC);

$stmt_communes = $conn->prepare("SELECT COUNT(DISTINCT communes) as total_communes FROM commandes WHERE utilisateur_id = ? AND YEAR(date_commande) = ? AND statut = 'livré'");
$stmt_communes->execute([$client_id, $year]);
$total_communes = $stmt_communes->fetch(PDO::FETCH_ASSOC)['total_communes'];

// Afficher les statistiques globales
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, "Statistiques Globales", 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);

$pdf->Cell(100, 10, "Total des colis livrés:", 0, 0);
$pdf->Cell(0, 10, number_format($stats_total['total_livres'], 0, ',', ' '), 0, 1);

$pdf->Cell(100, 10, "Communes desservies:", 0, 0);
$pdf->Cell(0, 10, number_format($total_communes, 0, ',', ' '), 0, 1);

$pdf->Cell(100, 10, "Coût total des livraisons:", 0, 0);
$pdf->Cell(0, 10, number_format($stats_total['total_cout'], 0, ',', ' ') . " FCFA", 0, 1);
$pdf->Ln(10);

// Ajouter les graphiques
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, "Evolution mensuelle des commandes", 0, 1, 'L');
$pdf->Image($data['commandesChart'], 10, $pdf->GetY(), 190);
$pdf->Ln(100);

$pdf->Cell(0, 10, "Evolution mensuelle des revenus", 0, 1, 'L');
$pdf->Image($data['revenusChart'], 10, $pdf->GetY(), 190);

// Envoyer le PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Statistiques_' . $year . '.pdf"');
$pdf->Output('I', 'Statistiques_' . $year . '.pdf');
?>
