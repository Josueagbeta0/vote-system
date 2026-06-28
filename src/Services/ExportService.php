<?php
namespace App\Services;

use TCPDF;

/**
 * ExportService - Génération de rapports (PDF, CSV)
 */
class ExportService {

    /**
     * Générer le procès-verbal PDF
     */
    public function generateResultsPDF($election, $results, $stats) {
        // Initialiser TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Métadonnées
        $pdf->SetCreator('VoteSystem');
        $pdf->SetAuthor('VoteSystem Admin');
        $pdf->SetTitle('Résultats - ' . $election['title']);
        $pdf->SetSubject('Procès-Verbal des Résultats');

        // En-têtes/Pieds de page (désactivés pour la simplicité)
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Marges
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Ajouter une page
        $pdf->AddPage();

        // --- Contenu ---
        
        // Titre
        $html = '<h1 style="text-align:center;">Procès-Verbal des Résultats</h1>';
        $html .= '<h2 style="text-align:center; color:#666;">' . $election['title'] . '</h2>';
        $html .= '<hr>';
        
        // Informations Générales
        $html .= '<h3>1. Informations Générales</h3>';
        $html .= '<table cellspacing="5" cellpadding="5">';
        $html .= '<tr><td><strong>Date de début :</strong> ' . date('d/m/Y H:i', strtotime($election['start_date'])) . '</td></tr>';
        $html .= '<tr><td><strong>Date de clôture :</strong> ' . date('d/m/Y H:i', strtotime($election['end_date'])) . '</td></tr>';
        $html .= '<tr><td><strong>Établissement :</strong> ' . ($election['organization_name'] ?? 'N/A') . '</td></tr>';
        $html .= '</table>';

        // Statistiques
        $html .= '<h3>2. Participation</h3>';
        $html .= '<ul>';
        $html .= '<li><strong>Électeurs inscrits :</strong> ' . $stats['total_voters'] . '</li>';
        $html .= '<li><strong>Suffrages exprimés :</strong> ' . $stats['voted_count'] . '</li>';
        $html .= '<li><strong>Taux de participation :</strong> ' . number_format($stats['participation_rate'], 2) . '%</li>';
        $html .= '<li><strong>Nombre de candidats :</strong> ' . $stats['candidate_count'] . '</li>';
        $html .= '</ul>';

        // Résultats
        $html .= '<h3>3. Résultats Détaillés</h3>';
        
        $html .= '<table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">';
        $html .= '<thead style="background-color:#f2f2f2;">';
        $html .= '<tr>';
        $html .= '<th width="10%"><strong>Rang</strong></th>';
        $html .= '<th width="50%"><strong>Candidat</strong></th>';
        $html .= '<th width="20%" align="center"><strong>Voix</strong></th>';
        $html .= '<th width="20%" align="center"><strong>Pourcentage</strong></th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $rank = 1;
        $winnerVoteCount = -1;

        foreach ($results as $res) {
            $pct = ($stats['total_votes'] > 0) ? round(($res['vote_count'] / $stats['total_votes']) * 100, 2) : 0;
            
            // Highlight winner
            $style = '';
            if ($rank == 1) {
                $style = 'background-color:#e6ffe6; font-weight:bold;';
                $winnerVoteCount = $res['vote_count'];
            } elseif ($res['vote_count'] == $winnerVoteCount) {
                 // Ex aequo
                 $style = 'background-color:#e6ffe6; font-weight:bold;';
            }

            $html .= '<tr style="' . $style . '">';
            $html .= '<td>#' . $rank . '</td>';
            $html .= '<td>' . $res['name'] . '</td>';
            $html .= '<td align="center">' . $res['vote_count'] . '</td>';
            $html .= '<td align="center">' . $pct . '%</td>';
            $html .= '</tr>';
            
            $rank++;
        }
        $html .= '</tbody></table>';

        // Signature zone
        $html .= '<br><br><br><br>';
        $html .= '<table width="100%">';
        $html .= '<tr>';
        $html .= '<td width="50%">Fait le ' . date('d/m/Y') . '</td>';
        $html .= '<td width="50%" align="right">Signature / Cachet</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Écrire le HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Sortie (Téléchargement forcée)
        $filename = 'resultats_' . preg_replace('/[^a-z0-9]/i', '_', strtolower($election['title'])) . '_' . date('Ymd') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Générer le rapport Excel (CSV)
     */
    public function generateResultsCSV($election, $results, $stats) {
        $filename = 'resultats_' . preg_replace('/[^a-z0-9]/i', '_', strtolower($election['title'])) . '_' . date('Ymd') . '.csv';

        // Headers HTTP pour le téléchargement
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Ouvrir la sortie php
        $output = fopen('php://output', 'w');

        // Ajouter le BOM UTF-8 pour être sûr qu'Excel lise bien les accents
        fputs($output, "\xEF\xBB\xBF");

        // En-têtes du fichier CSV
        fputcsv($output, ['ELECTION', $election['title']], ';');
        fputcsv($output, ['DATE CLOTURE', $election['end_date']], ';');
        fputcsv($output, ['INSCRITS', $stats['total_voters']], ';');
        fputcsv($output, ['VOTANTS', $stats['voted_count']], ';');
        fputcsv($output, [], ';'); // Ligne vide

        // En-têtes des colonnes
        fputcsv($output, ['Rang', 'Nom du Candidat', 'Nombre de Voix', 'Pourcentage'], ';');

        // Données
        $rank = 1;
        foreach ($results as $res) {
            $pct = ($stats['total_votes'] > 0) ? round(($res['vote_count'] / $stats['total_votes']) * 100, 2) : 0;
            
            fputcsv($output, [
                $rank,
                $res['name'],
                $res['vote_count'],
                $pct . '%'
            ], ';');
            $rank++;
        }
        
        fclose($output);
        exit;
    }
}
