<?php
require_once '../inc/functions/connexion.php';
include('header.php');

if (!isset($_GET['id']) || !isset($_POST['year'])) {
    header('Location: commandes_clients.php');
    exit;
}

$client_id = $_GET['id'];
$year = $_POST['year'];

// Requête pour obtenir le total des colis livrés sur l'année
$stmt_total = $conn->prepare("SELECT 
    COUNT(*) as total_livres
FROM commandes 
WHERE utilisateur_id = ? 
    AND YEAR(date_commande) = ?
    AND statut = 'livré'");

$stmt_total->execute([$client_id, $year]);
$total_livres = $stmt_total->fetch(PDO::FETCH_ASSOC)['total_livres'];

// Requête pour obtenir le nombre total de communes livrées
$stmt_communes = $conn->prepare("SELECT 
    COUNT(DISTINCT communes) as total_communes
FROM commandes 
WHERE utilisateur_id = ? 
    AND YEAR(date_commande) = ?
    AND statut = 'livré'
    AND communes IS NOT NULL");

$stmt_communes->execute([$client_id, $year]);
$total_communes = $stmt_communes->fetch(PDO::FETCH_ASSOC)['total_communes'];

// Requête pour obtenir la somme totale des coûts réels des commandes livrées
$stmt_cout_total = $conn->prepare("SELECT 
    SUM(cout_reel) as total_cout
FROM commandes 
WHERE utilisateur_id = ? 
    AND YEAR(date_commande) = ?
    AND statut = 'livré'");

$stmt_cout_total->execute([$client_id, $year]);
$total_cout = $stmt_cout_total->fetch(PDO::FETCH_ASSOC)['total_cout'];

// Requête pour obtenir les statistiques mensuelles
$stmt = $conn->prepare("SELECT 
    MONTH(date_commande) as mois,
    COUNT(*) as total_commandes,
    SUM(CASE WHEN statut = 'livré' THEN cout_reel ELSE 0 END) as total_cout_reel,
    SUM(CASE WHEN statut = 'livré' THEN 1 ELSE 0 END) as commandes_livrees,
    SUM(CASE WHEN statut = 'non livré' THEN 1 ELSE 0 END) as commandes_non_livrees
FROM commandes 
WHERE utilisateur_id = ? AND YEAR(date_commande) = ?
GROUP BY MONTH(date_commande)
ORDER BY MONTH(date_commande)");

$stmt->execute([$client_id, $year]);
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Requête pour le top 10 des communes livrées
$stmt_top_livrees = $conn->prepare("SELECT 
    communes,
    COUNT(*) as total
FROM commandes 
WHERE utilisateur_id = ? 
    AND YEAR(date_commande) = ?
    AND statut = 'livré'
    AND communes IS NOT NULL
GROUP BY communes
ORDER BY total DESC
LIMIT 10");

$stmt_top_livrees->execute([$client_id, $year]);
$top_communes_livrees = $stmt_top_livrees->fetchAll(PDO::FETCH_ASSOC);

// Requête pour le top 10 des communes non livrées
$stmt_top_non_livrees = $conn->prepare("SELECT 
    communes,
    COUNT(*) as total
FROM commandes 
WHERE utilisateur_id = ? 
    AND YEAR(date_commande) = ?
    AND statut = 'non livré'
    AND communes IS NOT NULL
GROUP BY communes
ORDER BY total DESC
LIMIT 10");

$stmt_top_non_livrees->execute([$client_id, $year]);
$top_communes_non_livrees = $stmt_top_non_livrees->fetchAll(PDO::FETCH_ASSOC);

// Préparer les données pour les graphiques
$mois = [];
$total_commandes = [];
$couts_reels = [];
$commandes_livrees = [];
$commandes_non_livrees = [];

$communes_livrees = array_column($top_communes_livrees, 'communes');
$totaux_livrees = array_column($top_communes_livrees, 'total');
$communes_non_livrees = array_column($top_communes_non_livrees, 'communes');
$totaux_non_livrees = array_column($top_communes_non_livrees, 'total');

$noms_mois = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

foreach ($stats as $stat) {
    $mois[] = $noms_mois[$stat['mois']];
    $total_commandes[] = $stat['total_commandes'];
    $couts_reels[] = $stat['total_cout_reel'];
    $commandes_livrees[] = $stat['commandes_livrees'];
    $commandes_non_livrees[] = $stat['commandes_non_livrees'];
}
?>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    .content-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    
    .card {
        break-inside: avoid;
    }
    
    .info-box {
        border: 1px solid #ddd;
        margin-bottom: 15px;
    }
    
    canvas {
        max-width: 100% !important;
        height: auto !important;
    }
    
    @page {
        size: A4;
        margin: 1cm;
    }
}

.print-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}
</style>

<div class="row">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Statistiques <?= $year ?></h1>
                </div>
                <div class="col-sm-6">
                <form action="impression_statistiques.php" method="get" target="_blank" class="float-right no-print">
    <input type="hidden" name="id" value="<?php echo $client_id; ?>">
    <input type="hidden" name="year" value="<?php echo $year; ?>">
    <button type="submit" class="btn btn-primary print-button">
        <i class="fas fa-print"></i> Imprimer en PDF
    </button>
</form>

                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistiques globales -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total des colis livrés en <?= $year ?></span>
                            <span class="info-box-number"><?= $total_livres ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-map-marker-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Communes desservies en <?= $year ?></span>
                            <span class="info-box-number"><?= $total_communes ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-money-bill-wave"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Coût total des livraisons en <?= $year ?></span>
                            <span class="info-box-number"><?= number_format($total_cout, 0, ',', ' ') ?> FCFA</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Graphique des commandes -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Nombre de colis par mois</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="commandesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Graphique des coûts -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Revenus des livraisons par mois</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="coutsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Graphique des statuts -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Statuts des commandes par mois</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="statutsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top 10 des communes -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Top 10 des communes livrées</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="topLivreesChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Top 10 des communes non livrées</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="topNonLivreesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ChartJS -->
<script src="../../plugins/chart.js/Chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour attendre que les graphiques soient rendus avant l'impression
    function handlePrint() {
        window.onbeforeprint = function() {
            // Attendre que tous les graphiques soient complètement rendus
            setTimeout(function() {
                // L'impression se lancera automatiquement
            }, 500);
        };
    }

    handlePrint();

    // Graphique des commandes
    new Chart(document.getElementById('commandesChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($mois) ?>,
            datasets: [{
                label: 'Nombre de colis',
                data: <?= json_encode($total_commandes) ?>,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Evolution du nombre de colis par mois'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' colis';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + ' colis';
                        }
                    }
                }
            }
        }
    });

    // Graphique des coûts
    new Chart(document.getElementById('coutsChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($mois) ?>,
            datasets: [{
                label: 'Revenus des livraisons',
                data: <?= json_encode($couts_reels) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Revenus mensuels des livraisons effectuées'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' FCFA';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' FCFA';
                        }
                    }
                }
            }
        }
    });

    // Graphique des statuts
    new Chart(document.getElementById('statutsChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($mois) ?>,
            datasets: [{
                label: 'Commandes livrées',
                data: <?= json_encode($commandes_livrees) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1
            }, {
                label: 'Commandes non livrées',
                data: <?= json_encode($commandes_non_livrees) ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgb(255, 99, 132)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    stacked: true
                },
                x: {
                    stacked: true
                }
            }
        }
    });

    // Graphique du top 10 des communes livrées
    new Chart(document.getElementById('topLivreesChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($communes_livrees) ?>,
            datasets: [{
                label: 'Nombre de livraisons réussies',
                data: <?= json_encode($totaux_livrees) ?>,
                backgroundColor: 'rgba(46, 204, 113, 0.2)',
                borderColor: '#2ecc71',
                borderWidth: 2
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.x + ' commandes';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Nombre de commandes'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Communes'
                    }
                }
            }
        }
    });

    // Graphique du top 10 des communes non livrées
    new Chart(document.getElementById('topNonLivreesChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($communes_non_livrees) ?>,
            datasets: [{
                label: 'Nombre de livraisons non réussies',
                data: <?= json_encode($totaux_non_livrees) ?>,
                backgroundColor: 'rgba(231, 76, 60, 0.2)',
                borderColor: '#e74c3c',
                borderWidth: 2
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.x + ' commandes';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Nombre de commandes'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Communes'
                    }
                }
            }
        }
    });
});
</script>