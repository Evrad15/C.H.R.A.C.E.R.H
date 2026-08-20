<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Rapport;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RapportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Affiche la bibliothèque de rapports et les dossiers mensuels.
     */
    public function index(Request $request)
    {
        $disk = Storage::disk('public');
        $baseDir = 'reports';

        if (!$disk->exists($baseDir)) {
            $disk->makeDirectory($baseDir);
        }

        $employees = Employe::with('department')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'department_id']);

        // Récupération de tous les fichiers et sous-dossiers
        $allFiles = $disk->allFiles($baseDir);
        
        $filesInfo = [];
        $foldersInfo = [];
        $totalFiles = 0;
        $currentMonthCount = 0;

        foreach ($allFiles as $file) {
            // Ignorer les fichiers cachés (.gitignore, .DS_Store, etc.)
            if (basename($file) === '.gitignore' || str_starts_with(basename($file), '.')) {
                continue;
            }

            $totalFiles++;
            $size = $disk->size($file);
            $lastModified = $disk->lastModified($file);
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            // Dossier parent relatif (ex: "reports/Juillet 2026" => "Juillet 2026")
            $dirname = dirname($file);
            $folderName = ($dirname === $baseDir) ? 'Racine' : str_replace($baseDir . '/', '', $dirname);

            $relativeFilename = str_replace($baseDir . '/', '', $file);

            $fileItem = [
                'path'          => $file,
                'name'          => basename($file),
                'folder'        => $folderName,
                'extension'     => $extension,
                'size_formatted'=> $this->formatBytes($size),
                'modified_at'   => Carbon::createFromTimestamp($lastModified)->locale('fr')->isoFormat('D MMMM YYYY à HH:mm'),
                'download_url'  => route('rapports.download', ['filename' => $relativeFilename]),
                'open_url'      => route('rapports.open', ['filename' => $relativeFilename]),
                'preview_url'   => route('rapports.preview', ['filename' => $relativeFilename]),
            ];

            $filesInfo[] = $fileItem;

            if (!isset($foldersInfo[$folderName])) {
                $foldersInfo[$folderName] = [
                    'name'  => $folderName,
                    'count' => 0,
                    'files' => [],
                ];
            }
            $foldersInfo[$folderName]['count']++;
            $foldersInfo[$folderName]['files'][] = $fileItem;

            if (stristr($folderName, Carbon::now()->locale('fr')->isoFormat('MMMM')) || stristr($fileItem['modified_at'], Carbon::now()->locale('fr')->isoFormat('MMMM'))) {
                $currentMonthCount++;
            }
        }

        // Rapports enregistrés dans la table `rapports` BDD
        $dbRapports = Rapport::orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total_files'   => $totalFiles,
            'current_month' => $currentMonthCount,
            'db_total'      => Rapport::count(),
            'latest_file'   => count($filesInfo) > 0 ? $filesInfo[0]['modified_at'] : 'Aucun',
        ];

        return view('rapports.index', [
            'filesInfo'   => $filesInfo,
            'foldersInfo' => $foldersInfo,
            'dbRapports'  => $dbRapports,
            'stats'       => $stats,
            'employees'   => $employees,
        ]);
    }

    /**
     * Génère un rapport à la demande (Hebdomadaire, Mensuel, Annuel, Journalier).
     */
    public function generate(Request $request)
    {
        $request->validate([
            'period' => 'required|string',
            'format' => 'required|in:excel,pdf',
            'date'   => 'nullable|date',
            'employee_id' => 'nullable|integer|exists:employés,id',
        ]);

        try {
            $period = $request->input('period');
            $format = $request->input('format');
            $targetDate = $request->filled('date') ? Carbon::parse($request->input('date')) : Carbon::today();
            $employeeId = $request->filled('employee_id') ? (int) $request->input('employee_id') : null;

            $filePath = $this->reportService->generateAndExport($period, $format, $targetDate, $employeeId);

            return redirect()->route('rapports.index')->with('success', 'Rapport généré et enregistré avec succès dans la bibliothèque !');

        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Erreur lors de la génération du rapport : ' . $e->getMessage());
        }
    }

    /**
     * Génère un aperçu HTML lisible ou PDF directement dans la visionneuse sans téléchargement.
     */
    public function previewFile($filename)
    {
        $matchedPath = $this->findMatchingFile($filename);

        if (!$matchedPath || !Storage::disk('public')->exists($matchedPath)) {
            return response()->json(['error' => 'Rapport introuvable'], 404);
        }

        $fullPath = Storage::disk('public')->path($matchedPath);
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            return response()->file($fullPath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
            ]);
        }

        if (in_array($ext, ['xlsx', 'xls'])) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);

                $htmlContent = $writer->generateHtmlAll();

                return response()->json([
                    'type'     => 'excel',
                    'filename' => basename($fullPath),
                    'html'     => $htmlContent,
                ]);

            } catch (\Throwable $e) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'Impossible d\'afficher l\'aperçu Excel : ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->file($fullPath, [
            'Content-Type'        => mime_content_type($fullPath) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline',
        ]);
    }

    /**
     * Ouvre un fichier rapport directement dans le navigateur.
     */
    public function openFile($filename)
    {
        $matchedPath = $this->findMatchingFile($filename);

        if ($matchedPath && Storage::disk('public')->exists($matchedPath)) {
            $fullPath = Storage::disk('public')->path($matchedPath);
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            $mimeType = match ($ext) {
                'pdf'   => 'application/pdf',
                'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xls'   => 'application/vnd.ms-excel',
                default => mime_content_type($fullPath) ?: 'application/octet-stream',
            };

            return response()->file($fullPath, [
                'Content-Type'        => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
            ]);
        }

        abort(404, 'Rapport introuvable : ' . urldecode($filename));
    }

    /**
     * Télécharge un fichier rapport.
     */
    public function downloadFile($filename)
    {
        $matchedPath = $this->findMatchingFile($filename);

        if ($matchedPath && Storage::disk('public')->exists($matchedPath)) {
            $fullPath = Storage::disk('public')->path($matchedPath);
            return response()->download($fullPath, basename($fullPath));
        }

        abort(404, 'Rapport introuvable : ' . urldecode($filename));
    }

    /**
     * Supprime un fichier de rapport du stockage.
     */
    public function deleteFile(Request $request)
    {
        $request->validate(['path' => 'required|string']);

        $rawPath = $request->input('path');
        $matchedPath = $this->findMatchingFile($rawPath);

        if ($matchedPath && Storage::disk('public')->exists($matchedPath)) {
            Storage::disk('public')->delete($matchedPath);
            return redirect()->route('rapports.index')->with('success', 'Fichier rapport supprimé avec succès.');
        }

        return redirect()->back()->with('error', 'Fichier introuvable pour la suppression.');
    }

    /**
     * Recherche intelligente de fichier sur le disque sans sensibilité aux accents/encodages Windows.
     */
    private function findMatchingFile(string $filename): ?string
    {
        $disk = Storage::disk('public');
        $allFiles = $disk->allFiles('reports');

        $targetClean = rawurldecode(urldecode($filename));
        $targetBasename = basename($targetClean);

        // 1. Recherche par chemin exact ou relatif nettoyé
        foreach ($allFiles as $f) {
            $relative = str_replace('reports/', '', $f);
            if ($relative === $targetClean || $f === $targetClean || $f === 'reports/' . ltrim($targetClean, '/')) {
                return $f;
            }
            if (rawurldecode(urldecode($relative)) === $targetClean) {
                return $f;
            }
        }

        // 2. Recherche par nom de fichier (basename)
        foreach ($allFiles as $f) {
            if (basename($f) === $targetBasename) {
                return $f;
            }
        }

        return null;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
