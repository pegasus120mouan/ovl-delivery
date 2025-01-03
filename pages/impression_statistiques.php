<?php
require_once('../jpgraph/src/jpgraph.php');
require_once('../jpgraph/src/jpgraph_bar.php');
require_once('../jpgraph/src/jpgraph_pie.php');
require_once('../inc/functions/connexion.php');

if (isset($_GET['id'])) {
    $id_client = $_GET['id'];
    $id_client = htmlspecialchars($id_client, ENT_QUOTES, 'UTF-8');
    $year = $_GET['year'];

    try {
        // Récupérer les statistiques mensuelles
        $stmt_monthly = $conn->prepare("SELECT 
            MONTH(date_commande) as mois,
            COUNT(*) as total_commandes,
            SUM(CASE WHEN statut = 'livrée' THEN 1 ELSE 0 END) as commandes_livrees,
            SUM(CASE WHEN statut = 'livré' THEN cout_reel ELSE 0 END) as cout_total
        FROM commandes 
        WHERE utilisateur_id = ? 
            AND YEAR(date_commande) = ?
        GROUP BY MONTH(date_commande)
        ORDER BY MONTH(date_commande)");
        
        $stmt_monthly->execute([$id_client, $year]);
        $monthly_stats = $stmt_monthly->fetchAll(PDO::FETCH_ASSOC);

        // Préparer les données pour les graphiques
        $months = [];
        $total_orders = [];
        $delivered_orders = [];
        $total_costs = [];
        
        foreach ($monthly_stats as $stat) {
            $months[] = date("M", mktime(0, 0, 0, $stat['mois'], 1));
            $total_orders[] = $stat['total_commandes'];
            $delivered_orders[] = $stat['commandes_livrees'];
            $total_costs[] = $stat['cout_total'] / 1000; // Convertir en milliers pour l'affichage
        }

        // Configuration commune pour tous les graphiques
        function setupGraph($width, $height, $title) {
            $graph = new Graph($width, $height);
            $graph->SetScale("textlin");
            $graph->SetFrame(false);
            $graph->SetBox(false);
            $graph->img->SetMargin(60, 40, 40, 40);
            
            // Grille
            $graph->ygrid->SetFill(false);
            $graph->ygrid->SetColor('gray');
            $graph->ygrid->SetLineStyle('dashed');
            
            // Titre
            $graph->title->Set($title);
            $graph->title->SetFont(FF_ARIAL, FS_BOLD);
            $graph->title->SetColor('#333333');
            
            return $graph;
        }

        // Graphique des commandes
        $graph = setupGraph(800, 400, "Nombre de colis par mois $year");
        
        // Axes
        $graph->xaxis->SetTickLabels($months);
        $graph->xaxis->SetFont(FF_ARIAL, FS_NORMAL);
        $graph->xaxis->SetColor('#333333');
        $graph->yaxis->SetColor('#333333');
        $graph->yaxis->SetFont(FF_ARIAL, FS_NORMAL);
        
        // Barres
        $b1plot = new BarPlot($total_orders);
        $b2plot = new BarPlot($delivered_orders);
        
        // Style des barres
        $b1plot->SetFillColor("#4CAF50");
        $b2plot->SetFillColor("#2196F3");
        $b1plot->SetColor("#4CAF50");
        $b2plot->SetColor("#2196F3");
        
        // Légendes
        $b1plot->SetLegend("Total Commandes");
        $b2plot->SetLegend("Commandes Livrées");
        
        // Valeurs
        $b1plot->value->Show();
        $b2plot->value->Show();
        $b1plot->value->SetFormat('%d');
        $b2plot->value->SetFormat('%d');
        $b1plot->value->SetColor('#333333');
        $b2plot->value->SetColor('#333333');
        
        // Grouper les barres
        $gbplot = new GroupBarPlot(array($b1plot, $b2plot));
        $graph->Add($gbplot);
        
        // Légende
        $graph->legend->SetFrameWeight(0);
        $graph->legend->SetColor('#333333');
        $graph->legend->SetFillColor('white');
        $graph->legend->SetShadow(false);
        $graph->legend->SetPos(0.5, 0.95, 'center', 'bottom');
        
        // Graphique des coûts
        $cost_graph = setupGraph(800, 400, "Montant total par mois $year");
        
        // Axes
        $cost_graph->xaxis->SetTickLabels($months);
        $cost_graph->xaxis->SetFont(FF_ARIAL, FS_NORMAL);
        $cost_graph->xaxis->SetColor('#333333');
        $cost_graph->yaxis->SetColor('#333333');
        $cost_graph->yaxis->title->Set("Milliers FCFA");
        
        // Barre des coûts
        $cost_plot = new BarPlot($total_costs);
        $cost_plot->SetFillColor("#FFC107");
        $cost_plot->SetColor("#FFC107");
        $cost_plot->value->Show();
        $cost_plot->value->SetFormat('%d');
        $cost_plot->value->SetColor('#333333');
        
        $cost_graph->Add($cost_plot);
        
        // Récupérer les statistiques hebdomadaires
        $stmt_weekly = $conn->prepare("SELECT 
            WEEK(date_commande) as semaine,
            COUNT(*) as total_commandes,
            SUM(CASE WHEN statut = 'livrée' THEN 1 ELSE 0 END) as commandes_livrees
        FROM commandes 
        WHERE utilisateur_id = ? 
            AND YEAR(date_commande) = ?
        GROUP BY WEEK(date_commande)
        ORDER BY WEEK(date_commande)");
        
        $stmt_weekly->execute([$id_client, $year]);
        $weekly_stats = $stmt_weekly->fetchAll(PDO::FETCH_ASSOC);

        // Préparer les données pour le graphique hebdomadaire
        $weeks = [];
        $weekly_orders = [];
        $weekly_delivered = [];
        
        foreach ($weekly_stats as $stat) {
            $weeks[] = 'S' . str_pad($stat['semaine'], 2, '0', STR_PAD_LEFT);
            $weekly_orders[] = $stat['total_commandes'];
            $weekly_delivered[] = $stat['commandes_livrees'];
        }

        // Graphique hebdomadaire
        $weekly_graph = setupGraph(800, 400, "Évolution hebdomadaire des colis $year");
        
        // Axes
        $weekly_graph->xaxis->SetTickLabels($weeks);
        $weekly_graph->xaxis->SetFont(FF_ARIAL, FS_NORMAL);
        $weekly_graph->xaxis->SetColor('#333333');
        $weekly_graph->xaxis->SetLabelAngle(45);
        $weekly_graph->yaxis->SetColor('#333333');
        
        // Barres hebdomadaires
        $w1plot = new BarPlot($weekly_orders);
        $w2plot = new BarPlot($weekly_delivered);
        
        // Style des barres
        $w1plot->SetFillColor("#4CAF50");
        $w2plot->SetFillColor("#2196F3");
        $w1plot->SetColor("#4CAF50");
        $w2plot->SetColor("#2196F3");
        
        // Légendes
        $w1plot->SetLegend("Total Colis");
        $w2plot->SetLegend("Colis Livrés");
        
        // Valeurs
        $w1plot->value->Show();
        $w2plot->value->Show();
        $w1plot->value->SetFormat('%d');
        $w2plot->value->SetFormat('%d');
        $w1plot->value->SetColor('#333333');
        $w2plot->value->SetColor('#333333');
        
        // Grouper les barres
        $weekly_group = new GroupBarPlot(array($w1plot, $w2plot));
        $weekly_graph->Add($weekly_group);
        
        // Légende
        $weekly_graph->legend->SetFrameWeight(0);
        $weekly_graph->legend->SetColor('#333333');
        $weekly_graph->legend->SetFillColor('white');
        $weekly_graph->legend->SetShadow(false);
        $weekly_graph->legend->SetPos(0.5, 0.95, 'center', 'bottom');
        
        // Récupérer et créer le graphique des top communes
        $stmt_top_livrees = $conn->prepare("SELECT 
            communes as nom_commune,
            COUNT(*) as total
        FROM commandes 
        WHERE utilisateur_id = ? 
            AND YEAR(date_commande) = ?
            AND (statut = 'livré' OR statut = 'livrée')
            AND communes IS NOT NULL
            AND communes != ''
        GROUP BY communes
        ORDER BY total DESC, communes ASC
        LIMIT 10");
        
        $stmt_top_livrees->execute([$id_client, $year]);
        $top_communes = $stmt_top_livrees->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($top_communes)) {
            $commune_names = array_column($top_communes, 'nom_commune');
            $commune_totals = array_column($top_communes, 'total');

            // Graphique des communes
            $communes_graph = new Graph(800, 500); // Augmentation de la hauteur
            $communes_graph->SetScale('textlin');
            $communes_graph->SetFrame(false);
            $communes_graph->SetBox(false);
            
            // Marges optimisées pour les noms de communes
            $communes_graph->img->SetMargin(120, 40, 40, 120); // Augmentation de la marge du bas
            
            // Titre
            $communes_graph->title->Set("Top 10 des communes livrées");
            $communes_graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);
            $communes_graph->title->SetColor('#333333');
            
            // Grille
            $communes_graph->ygrid->Show(true);
            $communes_graph->ygrid->SetColor('#EEEEEE');
            $communes_graph->xgrid->Show(false);
            
            // Configurer l'axe Y
            $communes_graph->yaxis->SetFont(FF_ARIAL, FS_NORMAL, 10);
            $communes_graph->yaxis->SetColor('#333333');
            $communes_graph->yaxis->SetLabelMargin(10);
            $communes_graph->yaxis->SetTitleMargin(50);
            $communes_graph->yaxis->title->Set("Nombre de livraisons réussies");
            $communes_graph->yaxis->title->SetFont(FF_ARIAL, FS_NORMAL, 12);
            
            // Configurer l'axe X
            $communes_graph->xaxis->SetFont(FF_ARIAL, FS_NORMAL, 10);
            $communes_graph->xaxis->SetColor('#333333');
            $communes_graph->xaxis->SetLabelAngle(45);
            $communes_graph->xaxis->SetTickLabels(array_map('ucfirst', $commune_names));
            $communes_graph->xaxis->SetLabelMargin(15); // Augmentation de la marge des labels
            
            // Barres verticales
            $bplot = new BarPlot($commune_totals);
            $bplot->SetFillColor("#64B5F6"); // Bleu clair comme dans l'image
            $bplot->SetColor("#64B5F6");
            $bplot->SetWidth(0.7);
            
            // Afficher les valeurs au-dessus des barres
            $bplot->value->Show();
            $bplot->value->SetFont(FF_ARIAL, FS_NORMAL, 10);
            $bplot->value->SetFormat('%d');
            $bplot->value->SetColor('#333333');
            
            // Ajouter les barres au graphique
            $communes_graph->Add($bplot);
        }

        // Récupérer le coût total
        $stmt_cout_total = $conn->prepare("SELECT 
            COALESCE(SUM(cout_reel), 0) as total_cout
        FROM commandes 
        WHERE utilisateur_id = ? 
            AND YEAR(date_commande) = ?
            AND (statut = 'livré' OR statut = 'livrée')");

        $stmt_cout_total->execute([$id_client, $year]);
        $total_cout = $stmt_cout_total->fetch(PDO::FETCH_ASSOC)['total_cout'];

        // Sauvegarder les graphiques
        $temp_dir = sys_get_temp_dir(); // Utiliser le dossier temp du système
        
        // Générer des noms de fichiers uniques
        $bar_chart = tempnam($temp_dir, 'bar_') . '.png';
        $cost_chart = tempnam($temp_dir, 'cost_') . '.png';
        $communes_chart = tempnam($temp_dir, 'communes_') . '.png';
        $weekly_chart = tempnam($temp_dir, 'weekly_') . '.png';

        // Supprimer les fichiers s'ils existent
        foreach ([$bar_chart, $cost_chart, $communes_chart, $weekly_chart] as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        try {
            // Sauvegarder les graphiques
            $graph->Stroke($bar_chart);
            $cost_graph->Stroke($cost_chart);
            if (isset($communes_graph)) {
                $communes_graph->Stroke($communes_chart);
            }
            $weekly_graph->Stroke($weekly_chart);

            // Générer le PDF
            require('../fpdf/fpdf.php');
            $pdf = new FPDF();
            
            // En-tête du PDF
            $pdf->AddPage('L');

            // Titre et icônes de navigation
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, "OVL DELIVERY SERVICES", 0, 1, 'C');
            
            // Ligne d'icônes
            $pdf->SetFont('Arial', '', 12);
            $icons = "🏠  📱  📷  🌐";
            $pdf->Cell(0, 10, $icons, 0, 1, 'C');
            
            // Informations de contact
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 6, "Sarl au Capital de 1 000 000 CFA", 0, 1, 'C');
            $pdf->Cell(0, 6, "Cocody Riviera Golf en face de l'Ambassade des USA", 0, 1, 'C');
            $pdf->Cell(0, 6, "Tel: +225 0787703000 - +225059482385", 0, 1, 'C');
            $pdf->Cell(0, 6, "Email: finance@ovl-delivery.online", 0, 1, 'C');
            $pdf->Cell(0, 6, "ovl-delivery.online", 0, 1, 'C');
            $pdf->Ln(10);

            // Titre du montant total
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, "Montant total généré en $year", 0, 1, 'C');
            $pdf->Ln(5);

            // Rectangle noir pour le montant
            $pdf->SetFillColor(0, 0, 0);
            $rectWidth = $pdf->GetPageWidth() * 0.8;
            $rectX = ($pdf->GetPageWidth() - $rectWidth) / 2;
            $rectHeight = 30;
            $pdf->Rect($rectX, $pdf->GetY(), $rectWidth, $rectHeight, 'F');

            // Montant en blanc sur fond noir
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 24);
            $montant_formatte = number_format($total_cout, 0, ',', ' ');
            $pdf->SetY($pdf->GetY() + ($rectHeight/2) - 6);
            $pdf->Cell(0, 12, $montant_formatte . " Cfa", 0, 1, 'C');

            // Réinitialiser les couleurs
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(255, 255, 255);
            
            $pdf->Ln(20);

            // Ajouter les graphiques
            if (file_exists($bar_chart)) {
                $pdf->Image($bar_chart, 10, 40, 280);
                @unlink($bar_chart); // Nettoyer après utilisation
            }
            
            $pdf->AddPage('L');
            if (file_exists($cost_chart)) {
                $pdf->Image($cost_chart, 10, 40, 280);
                @unlink($cost_chart); // Nettoyer après utilisation
            }

            if (isset($communes_graph) && file_exists($communes_chart)) {
                $pdf->AddPage('L');
                $pdf->Image($communes_chart, 10, 20, 280, 180); // Ajustement de la position et de la taille
                @unlink($communes_chart); // Nettoyer après utilisation
            }

            if (isset($weekly_graph) && file_exists($weekly_chart)) {
                $pdf->AddPage('L');
                $pdf->Image($weekly_chart, 10, 40, 280);
                @unlink($weekly_chart);
            }

            // Envoyer le PDF
            $pdf->Output('D', "rapport_livraisons_${year}.pdf");

        } catch (Exception $e) {
            // Nettoyer les fichiers en cas d'erreur
            foreach ([$bar_chart, $cost_chart, $communes_chart, $weekly_chart] as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
            throw $e;
        }
    } catch (PDOException $e) {
        die("Erreur de base de données : " . $e->getMessage());
    } catch (Exception $e) {
        die($e->getMessage());
    }
} else {
    echo "Erreur : Aucun identifiant client transmis.";
}
?>
