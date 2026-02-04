<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Mpdf\Mpdf;
use Webklex\PDFMerger\PDFMerger;
use App\Models\Memo;
use App\Models\Undangan;
use App\Models\Risalah;
use App\Models\Divisi;
use App\Models\Department;
use App\Models\Director;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use FontLib\TrueType\Collection;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Mpdf\Output\Destination;


class CetakPDFController extends Controller
{
    /**
     * Merge PDF files with error handling - Using available libraries only
     */
    private function mergePDFs($mainPdfPath, $attachmentPath, $outputPath)
    {
        // Method 1: Try setasign/fpdi if available (most reliable for PDF merge)
        if (class_exists('\setasign\Fpdi\Fpdi')) {
            try {
                return $this->fpdiMergePDFs($mainPdfPath, $attachmentPath, $outputPath);
            } catch (Exception $e) {
                Log::error('FPDI PDF Merger Error: ' . $e->getMessage());
            }
        }

        // Method 2: Try Webklex PDFMerger with proper constructor
        if (class_exists('Webklex\PDFMerger\PDFMerger')) {
            try {
                // Try with Filesystem instance
                $filesystem = new Filesystem();
                $pdfMerger = new PDFMerger($filesystem);
                $pdfMerger->addPDF($mainPdfPath, 'all');
                $pdfMerger->addPDF($attachmentPath, 'all');
                $pdfMerger->merge('file', $outputPath);
                Log::info('PDF merged successfully using Webklex PDFMerger');
                return true;
            } catch (Exception $e) {
                Log::error('Webklex PDF Merger Error: ' . $e->getMessage());
            }
        }

        // Method 3: Try mPDF merger if available
        if (class_exists('\Mpdf\Mpdf')) {
            try {
                return $this->mpdfMergePDFs($mainPdfPath, $attachmentPath, $outputPath);
            } catch (Exception $e) {
                Log::error('mPDF Merger Error: ' . $e->getMessage());
            }
        }

        // Method 4: Create ZIP file with both PDFs
        // try {
        //     $zipPath = $this->createZipWithPDFs($mainPdfPath, $attachmentPath, $outputPath);
        //     if ($zipPath) {
        //         // If ZIP was created, rename it to the expected output path
        //         if (rename($zipPath, $outputPath)) {
        //             Log::info('Created ZIP file as PDF merge alternative');
        //             return true;
        //         }
        //     }
        // } catch (Exception $e) {
        //     Log::error('ZIP creation failed: ' . $e->getMessage());
        // }

        // Method 5: Fallback - just copy main file
        Log::warning('All PDF merger methods failed. Using fallback (main file only)');
        return $this->fallbackMergePDFs($mainPdfPath, $attachmentPath, $outputPath);
    }

    /**
     * FPDI-based PDF merger (most reliable if available)
     */
    private function fpdiMergePDFs($mainPdfPath, $attachmentPath, $outputPath)
    {
        try {
            $pdf = new \setasign\Fpdi\Fpdi();

            // Import main PDF
            $pageCount = $pdf->setSourceFile($mainPdfPath);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $tplSize = $pdf->getTemplateSize($templateId);
                $orientation = ($tplSize['width'] > $tplSize['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$tplSize['width'], $tplSize['height']]);
                $pdf->useTemplate($templateId, 0, 0, $tplSize['width'], $tplSize['height']);
            }

            // Import attachment PDF
            $pageCount = $pdf->setSourceFile($attachmentPath);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $tplSize = $pdf->getTemplateSize($templateId);
                $orientation = ($tplSize['width'] > $tplSize['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$tplSize['width'], $tplSize['height']]);
                $pdf->useTemplate($templateId, 0, 0, $tplSize['width'], $tplSize['height']);
            }

            $pdf->Output('F', $outputPath);
            return true;
        } catch (Exception $e) {
            Log::error('FPDI merge error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * mPDF-based merger (limited functionality)
     */
    private function mpdfMergePDFs($mainPdfPath, $attachmentPath, $outputPath)
    {
        try {
            // This is a simplified approach using mPDF
            // Note: mPDF is primarily for HTML to PDF, not PDF merging

            $mpdf = new \Mpdf\Mpdf();

            // Add a simple page indicating the files
            $html =
                '
            <h1>Dokumen Gabungan</h1>
            <p>File utama: ' .
                basename($mainPdfPath) .
                '</p>
            <p>Lampiran: ' .
                basename($attachmentPath) .
                '</p>
            <p>Catatan: Karena keterbatasan sistem, file PDF tidak dapat digabung.
            Silakan download file terpisah jika diperlukan.</p>
            ';

            $mpdf->WriteHTML($html);
            $mpdf->Output($outputPath, 'F');

            // This doesn't actually merge PDFs, just creates a notice
            // Copy the main PDF instead
            return copy($mainPdfPath, $outputPath);
        } catch (Exception $e) {
            Log::error('mPDF merge error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * TCPDF-based PDF merger
     */
    private function tcpdfMerger($mainPdfPath, $attachmentPath, $outputPath)
    {
        if (!class_exists('TCPDF_IMPORT')) {
            return false;
        }

        try {
            // This is a placeholder for TCPDF merger implementation
            // You would need to install and configure TCPDI for this to work
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Fallback PDF merger using simple file operations
     */
    private function fallbackMergePDFs($mainPdfPath, $attachmentPath, $outputPath)
    {
        // Simple fallback: just copy the main PDF file
        // In production, you might want to notify user about attachment being separate
        try {
            if (copy($mainPdfPath, $outputPath)) {
                Log::info('PDF merge fallback: Copied main PDF only, attachment skipped');
                // Optionally, you could create a ZIP file with both PDFs
                // $this->createZipWithPDFs($mainPdfPath, $attachmentPath, $outputPath);
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error('Fallback merge failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Alternative: Create ZIP file with both PDFs if merge fails
     */
    private function createZipWithPDFs($mainPdfPath, $attachmentPath, $outputPath)
    {
        try {
            // Create temporary ZIP file
            $zipPath = str_replace('.pdf', '.zip', $outputPath);

            if (class_exists('ZipArchive')) {
                $zip = new \ZipArchive();

                if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
                    $zip->addFile($mainPdfPath, 'dokumen_utama.pdf');
                    $zip->addFile($attachmentPath, 'lampiran.pdf');
                    $zip->close();

                    Log::info('Created ZIP file with separate PDFs: ' . $zipPath);
                    return $zipPath;
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to create ZIP: ' . $e->getMessage());
        }
        return false;
    }

    /**
     * Simple solution: Save attachment separately and return notice
     */
    private function handleAttachmentSeparately($mainPdfPath, $attachmentPath, $outputPath)
    {
        try {
            // Copy main PDF to output
            copy($mainPdfPath, $outputPath);

            // Save attachment with different name
            $attachmentOutputPath = str_replace('.pdf', '_lampiran.pdf', $outputPath);
            copy($attachmentPath, $attachmentOutputPath);

            Log::info("PDFs saved separately: Main={$outputPath}, Attachment={$attachmentOutputPath}");
            return true;
        } catch (Exception $e) {
            Log::error('Failed to handle attachment separately: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Create temporary attachment file from base64
     */
    private function createTempAttachment($base64Data, $id, $type)
    {
        $tempPath = storage_path("app/temp_lampiran_{$type}_{$id}.pdf");
        file_put_contents($tempPath, base64_decode($base64Data));
        return $tempPath;
    }

    public function cetakmemoPDF($id)
    {
        try {
            $memo = Memo::findOrFail($id);
            $tujuanNames = explode(';', (string)$memo->tujuan_string);

            $manager = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereRaw("LOWER(TRIM(CONCAT_WS(' ', firstname, lastname))) = LOWER(TRIM(?))", [$memo->nama_bertandatangan])
                ->first();

            if ($manager) {
                $level = $this->detectLevel($manager);
                $manager->level_kerja = $level;
                $manager->bagian_text = $this->getBagianText($manager, $level);
            }

            $headerPath = public_path('assets/img/bheader.png');
            $footerPath = public_path('assets/img/bfooter.png');
            $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : null;
            $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : null;

            $formatMemoPdf = \PDF::loadView('format-surat.format-memo', [
                'memo' => $memo,
                'headerImage' => $headerBase64,
                'footerImage' => $footerBase64,
                'tujuanNames' => $tujuanNames,
                'manager' => $manager,
                'qrCode' => $memo->qr_approved_by,
                'isPdf' => true,
                'docStatus'   => $memo->status,
            ])->setPaper('A4', 'portrait');

            $mainPath = storage_path('app/temp_format_memo_' . $memo->id . '.pdf');
            $formatMemoPdf->save($mainPath);

            $attPdfs = $this->createTempPdfsFromAnyMany($memo->lampiran ?? null, $memo->id, 'memo');
            $output  = storage_path('app/merged_memo_' . $memo->id . '.pdf');

            $fileName = Str::slug($memo->judul) . '-' . $memo->id . '.pdf';
            if ($this->mergeAllPdfs($mainPath, $attPdfs, $output)) {
                $this->cleanupTempFiles([$mainPath]);
                return response()->download($output, $fileName)->deleteFileAfterSend(true);
            }

            $this->cleanupTempFiles($attPdfs);
            return response()->download($mainPath, $fileName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Error in cetakmemoPDF: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat PDF'], 500);
        }
    }


    public function viewmemoPDF($id_memo)
    {
        try {
            $memo = Memo::findOrFail($id_memo);
            $tujuanNames = explode(';', (string)$memo->tujuan_string);

            $manager = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereRaw("LOWER(TRIM(CONCAT_WS(' ', firstname, lastname))) = LOWER(TRIM(?))", [$memo->nama_bertandatangan])
                ->first();

            if ($manager) {
                $level = $this->detectLevel($manager);
                $manager->level_kerja = $level;
                $manager->bagian_text = $this->getBagianText($manager, $level);
            }

            $headerPath = public_path('assets/img/bheader.png');
            $footerPath = public_path('assets/img/bfooter.png');
            $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : null;
            $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : null;

            $formatMemoPdf = PDF::loadView('format-surat.format-memo', [
                'memo' => $memo,
                'headerImage' => $headerBase64,
                'footerImage' => $footerBase64,
                'tujuanNames' => $tujuanNames,
                'manager' => $manager,
                'isPdf' => true,
                'docStatus'   => $memo->status,
            ])->setPaper('A4', 'portrait');

            $mainPath = storage_path('app/temp_format_memo_' . $memo->id . '.pdf');
            $formatMemoPdf->save($mainPath);

            $attPdfs = $this->createTempPdfsFromAnyMany($memo->lampiran ?? null, $memo->id, 'memo');
            $output  = storage_path('app/view_memo_' . $memo->id . '.pdf');

            if ($this->mergeAllPdfs($mainPath, $attPdfs, $output)) {
                $this->cleanupTempFiles([$mainPath]);
                return response()->file($output, ['Content-Type' => 'application/pdf'])->deleteFileAfterSend(true);
            }

            $this->cleanupTempFiles($attPdfs);
            return response()->file($mainPath, ['Content-Type' => 'application/pdf'])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Error in viewmemoPDF: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menampilkan PDF: ' . $e->getMessage()], 500);
        }
    }

    public function viewMemoPdfUrl($id_memo)
    {
        try {
            $memo = Memo::findOrFail($id_memo);
            $tujuanNames = explode(';', (string)$memo->tujuan_string);

            $manager = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereRaw("LOWER(TRIM(CONCAT_WS(' ', firstname, lastname))) = LOWER(TRIM(?))", [$memo->nama_bertandatangan])
                ->first();

            if ($manager) {
                $level = $this->detectLevel($manager);
                $manager->level_kerja = $level;
                $manager->bagian_text = $this->getBagianText($manager, $level);
            }

            $headerPath = public_path('assets/img/bheader.png');
            $footerPath = public_path('assets/img/bfooter.png');
            $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : null;
            $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : null;

            $formatMemoPdf = \PDF::loadView('format-surat.format-memo', [
                'memo' => $memo,
                'headerImage' => $headerBase64,
                'footerImage' => $footerBase64,
                'tujuanNames' => $tujuanNames,
                'manager' => $manager,
                'isPdf' => true,
                'docStatus'   => $memo->status,
            ])->setPaper('A4', 'portrait');
            $tempPath = storage_path('app/temp_format_undangan_' . $memo->id_memo . '.pdf');
            $formatMemoPdf->save($tempPath);

            $attPdfs = $this->createTempPdfsFromAnyMany($memo->lampiran ?? null, $memo->id_memo, 'memo');
            $finalFileName = $this->sanitizeFileName($memo->judul) . ' - ' . $memo->id_memo . '.pdf';
            $finalPath = storage_path('app/public/pdf/' . $finalFileName);

            if (!file_exists(dirname($finalPath))) {
                mkdir(dirname($finalPath), 0755, true);
            }

            if ($this->mergeAllPdfs($tempPath, $attPdfs, $finalPath)) {
                $this->cleanupTempFiles([$tempPath]);
            } else {
                rename($tempPath, $finalPath);
                $this->cleanupTempFiles($attPdfs);
            }

            $fileUrl = asset('storage/pdf/' . $finalFileName);

            return response()->json([
                'status' => 'success',
                'file_name' => $finalFileName,
                'url' => $fileUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in viewmemoPDF: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menampilkan PDF: ' . $e->getMessage()], 500);
        }
    }
    // Tambahkan method helper untuk sanitize filename
    private function sanitizeFileName($filename, $maxLength = 80)
    {
        // Remove HTML tags dan decode entities
        $filename = html_entity_decode(strip_tags($filename), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Replace karakter yang tidak diizinkan di nama file dengan underscore
        // Karakter yang tidak diizinkan: \ / : * ? " < > |
        $filename = preg_replace('/[\\\\\/:\*\?"<>\|]/', '_', $filename);

        // Replace multiple spaces dengan single space
        $filename = preg_replace('/\s+/', ' ', $filename);

        // Trim whitespace
        $filename = trim($filename);

        // Limit panjang filename
        if (strlen($filename) > $maxLength) {
            $filename = substr($filename, 0, $maxLength);
        }

        // Jika kosong, berikan nama default
        return $filename ?: 'undangan';
    }

    public function cetakundanganPDF($id)
    {
        try {
            $undangan = Undangan::findOrFail($id);

            $headerPath = public_path('assets/img/bheader.png');
            $footerPath = public_path('assets/img/bfooter.png');
            $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : null;
            $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : null;

            $tujuanIds = explode(';', (string)$undangan->tujuan);
            $tujuanUsers = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereIn('id', $tujuanIds)->get()
                ->map(function ($user) {
                    $level = $this->detectLevel($user);
                    $user->level_kerja = $level;
                    $user->bagian_text = $this->getBagianText($user, $level);
                    return $user;
                })
                ->sortBy(fn($u) => optional($u->position)->id_position)
                ->values();

            $manager = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereRaw("LOWER(TRIM(CONCAT_WS(' ', firstname, lastname))) = LOWER(TRIM(?))", [$undangan->nama_bertandatangan])
                ->first();
            if ($manager) {
                $level = $this->detectLevel($manager);
                $manager->level_kerja = $level;
                $manager->bagian_text = $this->getBagianText($manager, $level);
            }

            $cleanTag = html_entity_decode(strip_tags((string)$undangan->isi_undangan), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $formatUndanganPdf = \PDF::loadView('format-surat.format-undangan', [
                'undangan'    => $undangan,
                'tujuanUsers' => $tujuanUsers,
                'cleanTag'    => $cleanTag,
                'manager'     => $manager,
                'headerImage' => $headerBase64,
                'footerImage' => $footerBase64,
                'isPdf'       => true,
                'docStatus'   => $undangan->status,
            ])->setPaper('A4', 'portrait');

            $mainPath = storage_path('app/temp_format_undangan_' . $undangan->id . '.pdf');
            $formatUndanganPdf->save($mainPath);

            $attPdfs = $this->createTempPdfsFromAnyMany($undangan->lampiran ?? null, $undangan->id, 'undangan');
            $output  = storage_path('app/cetak_undangan_' . $undangan->id . '.pdf');

            $fileName = $this->sanitizeFileName($undangan->judul) . ' - ' . $undangan->id . '.pdf';

            if ($this->mergeAllPdfs($mainPath, $attPdfs, $output)) {
                $this->cleanupTempFiles([$mainPath]);
                return response()->download($output, $fileName)->deleteFileAfterSend(true);
            }

            $this->cleanupTempFiles($attPdfs);
            return response()->download($mainPath, $fileName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Error in cetakundanganPDF: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat PDF undangan'], 500);
        }
    }


    public function detectLevel($user)
    {
        if (!empty($user->unit_id_unit)) {
            return 'unit';
        }
        if (!empty($user->section_id_section)) {
            return 'section';
        }
        if (!empty($user->department_id_department)) {
            return 'department';
        }
        if (!empty($user->divisi_id_divisi)) {
            return 'divisi';
        }
        if (!empty($user->director_id_director)) {
            return 'director';
        }
        return null;
    }

    public function getBagianText($user, $level)
    {
        switch ($level) {
            case 'unit':
                return optional($user->unit)->name_unit;
            case 'section':
                return optional($user->section)->name_section;
            case 'department':
                return optional($user->department)->name_department;
            case 'divisi':
                return optional($user->divisi)->nm_divisi; // khusus nm_divisi
            case 'director':
                return optional($user->director)->name_director;
            default:
                return '-';
        }
    }

    public function viewundanganPDF($id_undangan)
    {
        try {
            $undangan = Undangan::findOrFail($id_undangan);

            $headerPath = public_path('assets/img/bheader.png');
            $footerPath = public_path('assets/img/bfooter.png');
            $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : null;
            $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : null;

            $tujuanIds = explode(';', (string)$undangan->tujuan);
            $tujuanUsers = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereIn('id', $tujuanIds)->get()
                ->map(function ($user) {
                    $level = $this->detectLevel($user);
                    $user->level_kerja = $level;
                    $user->bagian_text = $this->getBagianText($user, $level);

                    if (isset($user->position->nm_position)) {
                        $raw = $user->position->nm_position;
                        $fmt = preg_replace('/\s*\([^)]*\)\s*/', ' ', $raw);
                        $fmt = trim(preg_replace('/\s+/', ' ', $fmt));
                        if (!in_array($fmt, ['Staff', 'Direktur'])) {
                            $abbr = [
                                'Penanggung Jawab Senior Manager' => 'PJ SM',
                                'Penanggung Jawab Manager'       => 'PJ M',
                                'Penanggung Jawab Supervisor'    => 'PJ SPV',
                                'Senior Manager'                 => 'SM',
                                'General Manager'                => 'GM',
                                'Manager'                        => 'M',
                                'Supervisor'                     => 'SPV',
                            ];
                            foreach ($abbr as $full => $a) {
                                if (strpos($fmt, $full) !== false) {
                                    $fmt = str_replace($full, $a, $fmt);
                                    break;
                                }
                            }
                        }
                        $user->position->nm_position = $fmt;
                    }
                    return $user;
                })
                ->sortBy(fn($u) => optional($u->position)->id_position)
                ->values();

            $manager = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereRaw("LOWER(TRIM(CONCAT_WS(' ', firstname, lastname))) = LOWER(TRIM(?))", [$undangan->nama_bertandatangan])
                ->first();
            if ($manager) {
                $level = $this->detectLevel($manager);
                $manager->level_kerja = $level;
                $manager->bagian_text = $this->getBagianText($manager, $level);
            }

            $cleanTag = html_entity_decode(strip_tags((string)$undangan->isi_undangan), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $formatUndanganPdf = \PDF::loadView('format-surat.format-undangan', [
                'undangan'    => $undangan,
                'tujuanUsers' => $tujuanUsers,
                'cleanTag'    => $cleanTag,
                'manager'     => $manager,
                'headerImage' => $headerBase64,
                'footerImage' => $footerBase64,
                'isPdf'       => true,
                'docStatus'   => $undangan->status,
            ])->setPaper('A4', 'portrait');

            $mainPath = storage_path('app/temp_format_undangan_' . $undangan->id . '.pdf');
            $formatUndanganPdf->save($mainPath);

            $attPdfs = $this->createTempPdfsFromAnyMany($undangan->lampiran ?? null, $undangan->id, 'undangan');
            $output  = storage_path('app/view_undangan_' . $undangan->id . '.pdf');

            if ($this->mergeAllPdfs($mainPath, $attPdfs, $output)) {
                $this->cleanupTempFiles([$mainPath]);
                return response()->file($output, ['Content-Type' => 'application/pdf'])->deleteFileAfterSend(true);
            }

            $this->cleanupTempFiles($attPdfs);
            return response()->file($mainPath, ['Content-Type' => 'application/pdf'])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Error in viewundanganPDF: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Gagal menampilkan PDF undangan'], 500);
        }
    }

    public function viewUndanganPdfUrl($id)
    {
        $undangan = Undangan::findOrFail($id);
        $headerPath = public_path('assets/img/bheader.png');
        $footerPath = public_path('assets/img/bfooter.png');
        $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : null;
        $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : null;
        $cleanTag = html_entity_decode(strip_tags((string)$undangan->isi_undangan), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $tujuanIds = explode(';', (string)$undangan->tujuan);
        $tujuanUsers = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
            ->whereIn('id', $tujuanIds)->get()
            ->map(function ($user) {
                $level = $this->detectLevel($user);
                $user->level_kerja = $level;
                $user->bagian_text = $this->getBagianText($user, $level);

                if (isset($user->position->nm_position)) {
                    $raw = $user->position->nm_position;
                    $fmt = preg_replace('/\s*\([^)]*\)\s*/', ' ', $raw);
                    $fmt = trim(preg_replace('/\s+/', ' ', $fmt));
                    if (!in_array($fmt, ['Staff', 'Direktur'])) {
                        $abbr = [
                            'Penanggung Jawab Senior Manager' => 'PJ SM',
                            'Penanggung Jawab Manager'       => 'PJ M',
                            'Penanggung Jawab Supervisor'    => 'PJ SPV',
                            'Senior Manager'                 => 'SM',
                            'General Manager'                => 'GM',
                            'Manager'                        => 'M',
                            'Supervisor'                     => 'SPV',
                        ];
                        foreach ($abbr as $full => $a) {
                            if (strpos($fmt, $full) !== false) {
                                $fmt = str_replace($full, $a, $fmt);
                                break;
                            }
                        }
                    }
                    $user->position->nm_position = $fmt;
                }
                return $user;
            })
            ->sortBy(fn($u) => optional($u->position)->id_position)
            ->values();

        $manager = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
            ->whereRaw("LOWER(TRIM(CONCAT_WS(' ', firstname, lastname))) = LOWER(TRIM(?))", [$undangan->nama_bertandatangan])
            ->first();
        if ($manager) {
            $level = $this->detectLevel($manager);
            $manager->level_kerja = $level;
            $manager->bagian_text = $this->getBagianText($manager, $level);
        }
        $formatUndanganPdf = \PDF::loadView('format-surat.format-undangan', [
            'undangan'    => $undangan,
            'tujuanUsers' => $tujuanUsers,
            'cleanTag'    => $cleanTag,
            'manager'     => $manager,
            'headerImage' => $headerBase64,
            'footerImage' => $footerBase64,
            'isPdf'       => true,
            'docStatus'   => $undangan->status,
        ])->setPaper('A4', 'portrait');

        $tempPath = storage_path('app/temp_format_undangan_' . $undangan->id_undangan . '.pdf');
        $formatUndanganPdf->save($tempPath);

        $attPdfs = $this->createTempPdfsFromAnyMany($undangan->lampiran ?? null, $undangan->id_undangan, 'undangan');
        $finalFileName = $this->sanitizeFileName($undangan->judul) . ' - ' . $undangan->id_undangan . '.pdf';
        $finalPath = storage_path('app/public/pdf/' . $finalFileName);

        if (!file_exists(dirname($finalPath))) {
            mkdir(dirname($finalPath), 0755, true);
        }

        if ($this->mergeAllPdfs($tempPath, $attPdfs, $finalPath)) {
            $this->cleanupTempFiles([$tempPath]);
        } else {
            rename($tempPath, $finalPath);
            $this->cleanupTempFiles($attPdfs);
        }

        $fileUrl = asset('storage/pdf/' . $finalFileName);

        return response()->json([
            'status' => 'success',
            'file_name' => $finalFileName,
            'url' => $fileUrl,
        ]);
    }



    public function laporanmemoPDF(Request $request)
    {
        try {
            $memos = Memo::query();
            $memoController = new MemoController();
            $kodeUser = null;

            // Filter berdasarkan pencarian judul jika ada
            if ($request->filled('search')) {
                $memos->where('judul', 'like', '%' . $request->search . '%');
            }

            $manager = User::where('id', $request->manager_id)->first();

            $memos->whereDate('tgl_dibuat', '>=', $request->tgl_awal)->whereDate('tgl_dibuat', '<=', $request->tgl_akhir);

            // Ambil semua data yang sudah difilter
            $memos = $memos->orderBy('tgl_dibuat', 'asc')->get();

            // Ambil path gambar header dan footer
            $headerPath = public_path('assets/img/bheader.png');
            $footerPath = public_path('assets/img/bfooter.png');

            $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : null;
            $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : null;

            // Generate PDF dari view
            $pdf = PDF::loadView('format-surat.format-cetakLaporan-memo', [
                'memos' => $memos,
                'tgl_awal' => $request->tgl_awal,
                'tgl_akhir' => $request->tgl_akhir,
                'headerImage' => $headerBase64,
                'footerImage' => $footerBase64,
                'manager' => $manager,
                'isPdf' => true,
            ])->setPaper('A4', 'portrait');

            // Tampilkan PDF langsung di browser
            return $pdf->stream('laporan-memo.pdf');
        } catch (Exception $e) {
            Log::error('Error in laporanmemoPDF: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat laporan memo PDF' . $e->getMessage()], 500);
        }
    }

    public function laporanundanganPDF(Request $request)
    {
        try {
            // Ambil data undangan
            $undangans = Undangan::query();
            $memoController = new MemoController();

            if ($request->filled('search')) {
                $undangans->where('judul', 'like', '%' . $request->search . '%');
            }

            $kodeUser = null;
            if (Auth::user()->role->nm_role == 'admin') {
                $kodeUser = $memoController->getDivDeptKode(Auth::user());
            }

            if (!$kodeUser && $request->filled('kode') && $request->kode != 'pilih') {
                $kodeUser = $request->kode;
            }

            if ($kodeUser) {
                $undangans->where(function ($query) use ($kodeUser) {
                    $query->where('kode', $kodeUser);
                });
            }
            $manager = User::where('id', $request->manager_id)->first();

            $undangans->whereDate('tgl_dibuat', '>=', $request->tgl_awal)->whereDate('tgl_dibuat', '<=', $request->tgl_akhir);

            // Ambil semua data yang sudah difilter
            $undangans = $undangans->orderBy('tgl_dibuat', 'asc')->get();

            // Ambil path gambar header dan footer
            $headerPath = public_path('assets/img/bheader.png');
            $footerPath = public_path('assets/img/bfooter.png');

            $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : null;
            $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : null;

            // Generate PDF dari view
            $pdf = PDF::loadView('format-surat.format-cetakLaporan-undangan', [
                'undangans' => $undangans,
                'tgl_awal' => $request->tgl_awal,
                'tgl_akhir' => $request->tgl_akhir,
                'headerImage' => $headerBase64,
                'footerImage' => $footerBase64,
                'manager' => $manager,
                'isPdf' => true,
            ])->setPaper('A4', 'portrait');

            // Tampilkan PDF langsung di browser
            return $pdf->stream('laporan-undangan.pdf');
        } catch (Exception $e) {
            Log::error('Error in laporanundanganPDF: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat laporan undangan PDF'], 500);
        }
    }

    public function cetakrisalahPDF($id)
    {
        try {
            $risalah = Risalah::findOrFail($id);
            if ($risalah->with_undangan) {
                $undangan = Undangan::where('judul', $risalah->judul)->first();
            } else {
                $undangan = null;
            }
            $headerPath = public_path('assets/img/bheader.png');
            $footerPath = public_path('assets/img/bfooter.png');
            $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : null;
            $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : null;

            $pemimpin = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereRaw("LOWER(TRIM(CONCAT_WS(' ', firstname, lastname))) = LOWER(TRIM(?))", [$risalah->nama_pemimpin_acara])
                ->first();

            $notulis = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereRaw("LOWER(TRIM(CONCAT_WS(' ', firstname, lastname))) = LOWER(TRIM(?))", [$risalah->nama_notulis_acara])
                ->first();

            if ($pemimpin) {
                $level = $this->detectLevel($pemimpin);
                $pemimpin->level_kerja = $level;
                $pemimpin->bagian_text = $this->getBagianText($pemimpin, $level);
            }
            if ($notulis) {
                $level = $this->detectLevel($notulis);
                $notulis->level_kerja = $level;
                $notulis->bagian_text = $this->getBagianText($notulis, $level);
            }

            $cleanIsi = html_entity_decode(strip_tags((string)$risalah->isi_risalah), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $formatRisalahPdf = \PDF::loadView('format-surat.format-risalah', [
                'risalah' => $risalah,
                'undangan' => $undangan,
                'cleanIsi' => $cleanIsi,
                'pemimpin' => $pemimpin,
                'notulis'   => $notulis,
                'headerImage' => $headerBase64,
                'footerImage' => $footerBase64,
                'qrCode' => $risalah->qr_approved_by,
                'isPdf' => true,
                'docStatus'   => $risalah->status,
            ])->setPaper('A4', 'portrait');

            $mainPath = storage_path('app/temp_format_risalah_' . $risalah->id_risalah . '.pdf');
            $formatRisalahPdf->save($mainPath);

            // Ambil lampiran dan konversi ke array jika JSON string
            $lampiranField = $risalah->lampiran ?? null;
            Log::info('Risalah Cetak - Lampiran raw data: ' . ($lampiranField ? substr((string)$lampiranField, 0, 100) : 'kosong'));

            $attPdfs = $this->createTempPdfsFromAnyMany($lampiranField, $risalah->id_risalah, 'risalah');
            Log::info('Risalah Cetak - Total lampiran PDF yang dibuat: ' . count($attPdfs));

            $output  = storage_path('app/merged_risalah_' . $risalah->id_risalah . '.pdf');

            $fileName = Str::slug($risalah->judul) . '-' . $risalah->id . '.pdf';

            // Coba merge jika ada lampiran
            if (!empty($attPdfs)) {
                if ($this->mergeAllPdfs($mainPath, $attPdfs, $output)) {
                    Log::info('Risalah Cetak - Merge PDF berhasil');
                    $this->cleanupTempFiles([$mainPath]);
                    return response()->download($output, $fileName)->deleteFileAfterSend(true);
                } else {
                    Log::warning('Risalah Cetak - Merge PDF gagal, download surat utama saja');
                    $this->cleanupTempFiles($attPdfs);
                }
            } else {
                Log::info('Risalah Cetak - Tidak ada lampiran, download surat utama saja');
            }

            // Fallback: download surat utama saja jika tidak ada lampiran atau merge gagal
            return response()->download($mainPath, $fileName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Error in cetakrisalahPDF: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Gagal membuat PDF risalah: ' . $e->getMessage()], 500);
        }
    }

    public function viewRisalahPdfUrl($id_risalah)
    {
        try {
            $risalah = Risalah::findOrFail($id_risalah);
            if ($risalah->with_undangan) {
                $undangan = Undangan::where('judul', $risalah->judul)->first();
            } else {
                $undangan = null;
            }
            $headerPath = public_path('assets/img/bheader.png');
            $footerPath = public_path('assets/img/bfooter.png');
            $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : null;
            $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : null;

            $pemimpin = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereRaw("LOWER(TRIM(CONCAT_WS(' ', firstname, lastname))) = LOWER(TRIM(?))", [$risalah->nama_pemimpin_acara])
                ->first();

            $notulis = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereRaw("LOWER(TRIM(CONCAT_WS(' ', firstname, lastname))) = LOWER(TRIM(?))", [$risalah->nama_notulis_acara])
                ->first();

            if ($pemimpin) {
                $level = $this->detectLevel($pemimpin);
                $pemimpin->level_kerja = $level;
                $pemimpin->bagian_text = $this->getBagianText($pemimpin, $level);
            }
            if ($notulis) {
                $level = $this->detectLevel($notulis);
                $notulis->level_kerja = $level;
                $notulis->bagian_text = $this->getBagianText($notulis, $level);
            }

            $cleanIsi = html_entity_decode(strip_tags((string)$risalah->isi_risalah), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $formatRisalahPdf = \PDF::loadView('format-surat.format-risalah', [
                'risalah' => $risalah,
                'undangan' => $undangan,
                'cleanIsi' => $cleanIsi,
                'pemimpin' => $pemimpin,
                'notulis'   => $notulis,
                'headerImage' => $headerBase64,
                'footerImage' => $footerBase64,
                'isPdf' => true,
                'docStatus'   => $risalah->status,
            ])->setPaper('A4', 'portrait');


            $tempPath = storage_path('app/temp_format_risalah_' . $risalah->id_risalah . '.pdf');
            $formatRisalahPdf->save($tempPath);

            $attPdfs = $this->createTempPdfsFromAnyMany($risalah->lampiran ?? null, $risalah->id_risalah, 'risalah');
            $finalFileName = $this->sanitizeFileName($risalah->judul) . ' - ' . $risalah->id_risalah . '.pdf';
            $finalPath = storage_path('app/public/pdf/' . $finalFileName);

            if (!file_exists(dirname($finalPath))) {
                mkdir(dirname($finalPath), 0755, true);
            }

            if ($this->mergeAllPdfs($tempPath, $attPdfs, $finalPath)) {
                $this->cleanupTempFiles([$tempPath]);
            } else {
                rename($tempPath, $finalPath);
                $this->cleanupTempFiles($attPdfs);
            }

            $fileUrl = asset('storage/pdf/' . $finalFileName);

            return response()->json([
                'status' => 'success',
                'file_name' => $finalFileName,
                'url' => $fileUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in viewrisalahPDF: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menampilkan PDF risalah'], 500);
        }
    }

    public function viewrisalahPDF($id_risalah)
    {
        try {
            $risalah = Risalah::findOrFail($id_risalah);

            if ($risalah->with_undangan) {
                $undangan = Undangan::where('judul', $risalah->judul)->first();
            } else {
                $undangan = null;
            }
            $headerPath = public_path('assets/img/bheader.png');
            $footerPath = public_path('assets/img/bfooter.png');
            $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : null;
            $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : null;

            $pemimpin = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereRaw("LOWER(TRIM(CONCAT_WS(' ', firstname, lastname))) = LOWER(TRIM(?))", [$risalah->nama_pemimpin_acara])
                ->first();

            $notulis = User::with(['position', 'director', 'divisi', 'department', 'section', 'unit'])
                ->whereRaw("LOWER(TRIM(CONCAT_WS(' ', firstname, lastname))) = LOWER(TRIM(?))", [$risalah->nama_notulis_acara])
                ->first();

            if ($pemimpin) {
                $level = $this->detectLevel($pemimpin);
                $pemimpin->level_kerja = $level;
                $pemimpin->bagian_text = $this->getBagianText($pemimpin, $level);
            }
            if ($notulis) {
                $level = $this->detectLevel($notulis);
                $notulis->level_kerja = $level;
                $notulis->bagian_text = $this->getBagianText($notulis, $level);
            }
            $cleanIsi = html_entity_decode(strip_tags((string)$risalah->isi_risalah), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $formatRisalahPdf = \PDF::loadView('format-surat.format-risalah', [
                'risalah' => $risalah,
                'undangan' => $undangan,
                'cleanIsi' => $cleanIsi,
                'pemimpin' => $pemimpin,
                'notulis' => $notulis,
                'headerImage' => $headerBase64,
                'footerImage' => $footerBase64,
                'isPdf' => true,
                'docStatus'   => $risalah->status,
            ])->setPaper('A4', 'portrait');

            $mainPath = storage_path('app/temp_format_risalah_' . $risalah->id_risalah . '.pdf');
            $formatRisalahPdf->save($mainPath);

            // Ambil lampiran dan konversi ke array jika JSON string
            $lampiranField = $risalah->lampiran ?? null;
            Log::info('Risalah View - Lampiran raw data: ' . ($lampiranField ? substr((string)$lampiranField, 0, 100) : 'kosong'));

            $attPdfs = $this->createTempPdfsFromAnyMany($lampiranField, $risalah->id_risalah, 'risalah');
            Log::info('Risalah View - Total lampiran PDF yang dibuat: ' . count($attPdfs));

            $output  = storage_path('app/view_risalah_' . $risalah->id_risalah . '.pdf');

            // Coba merge jika ada lampiran
            if (!empty($attPdfs)) {
                if ($this->mergeAllPdfs($mainPath, $attPdfs, $output)) {
                    Log::info('Risalah View - Merge PDF berhasil');
                    $this->cleanupTempFiles([$mainPath]);
                    return response()->file($output, ['Content-Type' => 'application/pdf'])->deleteFileAfterSend(true);
                } else {
                    Log::warning('Risalah View - Merge PDF gagal, menampilkan surat utama saja');
                    $this->cleanupTempFiles($attPdfs);
                }
            } else {
                Log::info('Risalah View - Tidak ada lampiran, menampilkan surat utama saja');
            }

            // Fallback: tampilkan surat utama saja jika tidak ada lampiran atau merge gagal
            return response()->file($mainPath, ['Content-Type' => 'application/pdf'])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Error in viewrisalahPDF: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function laporanrisalahPDF(Request $request)
    {
        try {
            // Ambil data risalah
            $risalahs = Risalah::query();
            $memoController = new MemoController();
            $kodeUser = null;

            // Filter berdasarkan pencarian judul jika ada
            if ($request->filled('search')) {
                $risalahs->where('judul', 'like', '%' . $request->search . '%');
            }

            if (Auth::user()->role->nm_role == 'admin') {
                $kodeUser = $memoController->getDivDeptKode(Auth::user());
            }

            if (!$kodeUser && $request->filled('kode') && $request->kode != 'pilih') {
                $kodeUser = $request->kode;
            }

            if ($kodeUser) {
                $risalahs->where(function ($query) use ($kodeUser) {
                    $query->where('kode', $kodeUser);
                });
            }

            $manager = User::where('id', $request->manager_id)->first();

            $risalahs->whereDate('tgl_dibuat', '>=', $request->tgl_awal)->whereDate('tgl_dibuat', '<=', $request->tgl_akhir);

            // Ambil semua data yang sudah difilter
            $risalahs = $risalahs->orderBy('tgl_dibuat', 'desc')->get();

            // Ambil path gambar header dan footer
            $headerPath = public_path('assets/img/bheader.png');
            $footerPath = public_path('assets/img/bfooter.png');

            $headerBase64 = file_exists($headerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($headerPath)) : null;
            $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : null;

            // Generate PDF dari view
            $pdf = PDF::loadView('format-surat.format-cetakLaporan-risalah', [
                'risalahs' => $risalahs,
                'tgl_awal' => $request->tgl_awal,
                'tgl_akhir' => $request->tgl_akhir,
                'headerImage' => $headerBase64,
                'footerImage' => $footerBase64,
                'manager' => $manager,
                'isPdf' => true,
            ])->setPaper('A4', 'portrait');

            // Tampilkan PDF langsung di browser
            return $pdf->stream('laporan-risalah.pdf');
        } catch (Exception $e) {
            Log::error('Error in laporanrisalahPDF: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat laporan risalah PDF'], 500);
        }
    }
    /**
     * Terima berbagai bentuk field lampiran:
     * - string dipisah ';'  → "path1;path2;data:...;base64..."
     * - JSON array string   → '["path1","data:..."]'
     * - JSON array dengan object berisi path → '[{"path":"...","name":"..."},...]'
     * - array               → ["path1", "data:..."]
     * - relasi collection   → mis. $memo->lampirans (punya properti path/base64)
     * Kembalikan: array fullpath PDF sementara (tiap lampiran jadi PDF).
     */
    private function createTempPdfsFromAnyMany($lampiranField, $idRef, string $prefix = 'lampiran'): array
    {
        // Normalisasi jadi array "item"
        $items = [];

        if (is_null($lampiranField)) {
            return [];
        } elseif (is_string($lampiranField)) {
            $trim = trim($lampiranField);

            // JSON array?
            if (strlen($trim) > 1 && ($trim[0] === '[' || $trim[0] === '{')) {
                try {
                    $decoded = json_decode($trim, true);
                    if (is_array($decoded)) {
                        $items = $decoded;
                    } else {
                        $items = [$lampiranField];
                    }
                } catch (\Throwable $e) {
                    $items = [$lampiranField];
                }
            } else {
                // Pisah dengan ';' kalau ada
                $parts = array_filter(array_map('trim', explode(';', $lampiranField)), fn($v) => $v !== '');
                $items = count($parts) ? $parts : [$lampiranField];
            }
        } elseif (is_array($lampiranField)) {
            $items = $lampiranField;
        } elseif ($lampiranField instanceof \Illuminate\Support\Collection) {
            // Ambil property yang paling masuk akal
            $items = $lampiranField->map(function ($row) {
                foreach (['path', 'file', 'url', 'lampiran', 'content', 'blob', 'base64'] as $key) {
                    if (isset($row[$key])) return $row[$key];
                    if (isset($row->$key)) return $row->$key;
                }
                return null;
            })->filter()->values()->all();
        } else {
            // Fallback: jadikan string
            $items = [(string)$lampiranField];
        }

        // Konversi tiap item → temp PDF
        $pdfs = [];
        $idx = 1;
        foreach ($items as $it) {
            try {
                // Jika $it adalah array dengan key 'path', ambil path-nya
                $itemPath = $it;
                if (is_array($it) && isset($it['path'])) {
                    $itemPath = $it['path'];
                    Log::info("Lampiran ditemukan dengan path: {$itemPath}");
                } elseif (is_array($it)) {
                    // Jika adalah array tapi tidak punya 'path', cari key lain
                    foreach (['file', 'url', 'lampiran', 'content'] as $key) {
                        if (isset($it[$key])) {
                            $itemPath = $it[$key];
                            break;
                        }
                    }
                }

                $pdfs[] = $this->createTempPdfFromAny((string)$itemPath, "{$idRef}_{$idx}", $prefix);
                $idx++;
            } catch (\Throwable $e) {
                Log::warning("Skip lampiran gagal dikonversi: " . $e->getMessage());
            }
        }
        return $pdfs;
    }

    /**
     * Merge banyak PDF (base + array lampiran) menjadi 1 file output.
     * Menggunakan mergePDFs(pair) yang sudah kamu punya dengan chaining.
     */
    private function mergeAllPdfs(string $basePdf, array $attachmentPdfs, string $outputPath): bool
    {
        if (empty($attachmentPdfs)) {
            // Tidak ada lampiran → copy base ke output agar konsisten
            try {
                \Illuminate\Support\Facades\File::copy($basePdf, $outputPath);
                return true;
            } catch (\Throwable $e) {
                return false;
            }
        }

        $current = $basePdf;
        $tempFiles = [];

        foreach ($attachmentPdfs as $i => $att) {
            $intermediate = storage_path('app/tmp_merge_step_' . uniqid() . '.pdf');
            if (!$this->mergePDFs($current, $att, $intermediate)) {
                // Bersihkan temp yang sudah dibuat
                $this->cleanupTempFiles(array_merge($tempFiles, [$att, $intermediate]));
                return false;
            }
            if ($current !== $basePdf) {
                // Hapus intermediate sebelumnya
                $tempFiles[] = $current;
            }
            $current = $intermediate;
        }

        // Pindah hasil akhir ke outputPath
        try {
            \Illuminate\Support\Facades\File::move($current, $outputPath);
            // Bersihkan sisanya
            $this->cleanupTempFiles(array_merge($tempFiles, $attachmentPdfs));
            return true;
        } catch (\Throwable $e) {
            $this->cleanupTempFiles(array_merge($tempFiles, $attachmentPdfs, [$current]));
            return false;
        }
    }

    /**==========================HELPER UNTUK MERGE PDF===========================
     * Terima lampiran dalam bentuk:
     * - path storage (public/..., storage/..., full path) ke file PDF/JPG/JPEG/PNG
     * - base64 murni atau data URI (application/pdf, image/*)
     * Kembalikan: path file PDF sementara untuk di-merge.
     ==============================================================================*/
    private function createTempPdfFromAny(string $lampiran, $idRef, string $prefix = 'lampiran'): string
    {
        // Data URI?
        if (Str::startsWith($lampiran, 'data:')) {
            return $this->createTempPdfFromBlob($lampiran, $idRef, $prefix);
        }

        // Base64 murni (tanpa 'data:')
        if ($this->looksLikeBase64($lampiran)) {
            return $this->createTempPdfFromBlob($lampiran, $idRef, $prefix);
        }

        // Anggap path file di storage/public/fullpath
        return $this->createTempPdfFromPath($lampiran, $idRef, $prefix);
    }

    private function looksLikeBase64(string $s): bool
    {
        // Hindari decode string pendek/random
        if (strlen($s) < 64) return false;
        $decoded = base64_decode($s, true);
        if ($decoded === false) return false;

        // Heuristik: re-encode harus sama (abaikan padding)
        return rtrim(base64_encode($decoded), '=') === rtrim(preg_replace('/\s+/', '', $s), '=');
    }

    private function isImageMime(?string $mime): bool
    {
        return $mime && str_starts_with($mime, 'image/');
    }

    private function isImageExt(string $ext): bool
    {
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tif', 'tiff', 'svg']);
    }

    /**
     * FINAL: Convert image/PDF blob to temp PDF (merge-friendly)
     */
    private function createTempPdfFromBlob(string $base64BLOB, $idRef, string $prefix = 'lampiran'): string
    {
        // Normalisasi base64 / data URI
        $mimeType = null;
        if (Str::startsWith($base64BLOB, 'data:')) {
            [$meta, $payload] = explode(',', $base64BLOB, 2);
            $mimeType = Str::between($meta, 'data:', ';');
            $bytes = base64_decode($payload, true);
        } else {
            $bytes = base64_decode($base64BLOB, true);
        }

        if (!$bytes) {
            throw new \RuntimeException('BLOB lampiran kosong/tidak valid');
        }

        // Deteksi mime bila belum ada
        if (!$mimeType) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $bytes);
            finfo_close($finfo);
        }

        $dir = storage_path('app/tmp_pdf');
        if (!File::exists($dir)) File::makeDirectory($dir, 0775, true);

        $tempPdf = "{$dir}/temp_{$prefix}_{$idRef}.pdf";

        // ===== PDF blob: langsung simpan =====
        if ($mimeType === 'application/pdf') {
            File::put($tempPdf, $bytes);
            Log::info("createTempPdfFromBlob - PDF blob saved: {$tempPdf}");
            return $tempPdf;
        }

        // ===== Image blob: normalisasi -> embed ke mPDF =====
        if ($this->isImageMime($mimeType)) {
            $info = @getimagesizefromstring($bytes);
            if ($info === false) {
                throw new \RuntimeException('Gagal membaca dimensi gambar lampiran (blob)');
            }

            [$widthPx, $heightPx] = $info;
            Log::info("createTempPdfFromBlob - Image dimensions: {$widthPx}x{$heightPx}px");

            $dpi = 96;
            $widthMm  = ($widthPx / $dpi) * 25.4;
            $heightMm = ($heightPx / $dpi) * 25.4;

            Log::info("createTempPdfFromBlob - Page size: {$widthMm}mm x {$heightMm}mm");

            // Simpan bytes gambar ke file sementara
            $tmpImg = "{$dir}/img_{$prefix}_{$idRef}_src.bin";
            File::put($tmpImg, $bytes);

            // Normalisasi ke PNG RGB (hilangkan CMYK/alpha/ICC issue)
            $normalizedPng = "{$dir}/img_{$prefix}_{$idRef}_norm.png";
            if (!$this->normalizeImageToPNG($tmpImg, $normalizedPng, $widthPx, $heightPx)) {
                if (File::exists($tmpImg)) File::delete($tmpImg);
                throw new \RuntimeException("Gagal normalisasi gambar (blob)");
            }

            clearstatcache(false, $normalizedPng);
            Log::info("createTempPdfFromBlob - Normalized PNG: {$normalizedPng} size=" . filesize($normalizedPng));

            // IMPORTANT: buat PDF yang merge-friendly (PDF 1.4 + no compression)
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => [$widthMm, $heightMm],
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'dpi' => 96,
                'img_dpi' => 96,

                // ini yang sering menyelesaikan "putih setelah merge"
                'pdf_version' => '1.4',
                'compress' => false,
            ]);

            $mpdf->SetAutoPageBreak(false);

            // Lebih stabil dibanding HTML base64: langsung Image() file PNG
            $mpdf->AddPageByArray(['orientation' => 'P', 'sheet-size' => [$widthMm, $heightMm]]);
            $mpdf->Image($normalizedPng, 0, 0, $widthMm, $heightMm, 'png', '', true, false);

            $mpdf->Output($tempPdf, \Mpdf\Output\Destination::FILE);

            // Cleanup
            if (File::exists($tmpImg)) File::delete($tmpImg);
            if (File::exists($normalizedPng)) File::delete($normalizedPng);

            clearstatcache(false, $tempPdf);
            $pdfSize = File::exists($tempPdf) ? filesize($tempPdf) : 0;
            Log::info("createTempPdfFromBlob - PDF created: {$tempPdf} size={$pdfSize}");

            if ($pdfSize < 1000) {
                throw new \RuntimeException("PDF generation failed (file too small)");
            }

            return $tempPdf;
        }

        // Tipe tidak dikenal
        \PDF::loadHTML('<h3>Lampiran tidak didukung</h3><p>MIME: ' . e($mimeType) . '</p>')
            ->setPaper('A4', 'portrait')->save($tempPdf);

        return $tempPdf;
    }

    /**
     * Convert file path (image/pdf) to temp PDF (merge-friendly)
     */
    private function createTempPdfFromPath(string $path, $idRef, string $prefix = 'lampiran'): string
    {
        Log::info("createTempPdfFromPath - Processing: {$path}");

        $full = $this->resolveStoragePath($path);
        Log::info("createTempPdfFromPath - Resolved: {$full}");

        $mime = @mime_content_type($full) ?: null;
        $ext  = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        Log::info("createTempPdfFromPath - MIME={$mime}, EXT={$ext}");

        $dir = storage_path('app/tmp_pdf');
        if (!File::exists($dir)) File::makeDirectory($dir, 0775, true);

        $tempPdf = "{$dir}/temp_{$prefix}_{$idRef}.pdf";

        // PDF: copy
        if ($mime === 'application/pdf' || $ext === 'pdf') {
            File::copy($full, $tempPdf);
            Log::info("createTempPdfFromPath - PDF copied: {$tempPdf}");
            return $tempPdf;
        }

        // Image: normalize -> embed -> output
        if (($mime && $this->isImageMime($mime)) || $this->isImageExt($ext)) {
            $info = @getimagesize($full);
            if ($info === false) throw new \RuntimeException("Gagal membaca dimensi gambar: {$full}");
            [$widthPx, $heightPx] = $info;

            $dpi = 96;
            $widthMm  = ($widthPx / $dpi) * 25.4;
            $heightMm = ($heightPx / $dpi) * 25.4;

            $normalizedPng = "{$dir}/img_{$prefix}_{$idRef}_norm.png";
            if (!$this->normalizeImageToPNG($full, $normalizedPng, $widthPx, $heightPx)) {
                throw new \RuntimeException("Gagal normalisasi gambar (path)");
            }

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => [$widthMm, $heightMm],
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'dpi' => 96,
                'img_dpi' => 96,

                // merge-friendly
                'pdf_version' => '1.4',
                'compress' => false,
            ]);

            $mpdf->SetAutoPageBreak(false);
            $mpdf->AddPageByArray(['orientation' => 'P', 'sheet-size' => [$widthMm, $heightMm]]);
            $mpdf->Image($normalizedPng, 0, 0, $widthMm, $heightMm, 'png', '', true, false);
            $mpdf->Output($tempPdf, \Mpdf\Output\Destination::FILE);

            if (File::exists($normalizedPng)) File::delete($normalizedPng);

            clearstatcache(false, $tempPdf);
            $pdfSize = File::exists($tempPdf) ? filesize($tempPdf) : 0;
            Log::info("createTempPdfFromPath - PDF created: {$tempPdf} size={$pdfSize}");

            if ($pdfSize < 1000) {
                throw new \RuntimeException("PDF generation failed (file too small)");
            }

            return $tempPdf;
        }

        // Unsupported
        \PDF::loadHTML('<h3>Lampiran tidak didukung</h3><p>File: ' . e(basename($full)) . '</p>')
            ->setPaper('A4', 'portrait')->save($tempPdf);

        return $tempPdf;
    }

    /**
     * Normalisasi gambar ke PNG RGB dengan GD
     */
    private function normalizeImageToPNG(string $inputPath, string $outputPath, int $targetWidth, int $targetHeight): bool
    {
        try {
            $info = @getimagesize($inputPath);
            if ($info === false) {
                Log::error("normalizeImageToPNG - Cannot getimagesize: {$inputPath}");
                return false;
            }

            [$width, $height, $type] = $info;
            Log::info("normalizeImageToPNG - src={$width}x{$height} type={$type}");

            $source = match ($type) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($inputPath),
                IMAGETYPE_PNG  => @imagecreatefrompng($inputPath),
                IMAGETYPE_GIF  => @imagecreatefromgif($inputPath),
                IMAGETYPE_BMP  => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($inputPath) : false,
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($inputPath) : false,
                default        => false,
            };

            if ($source === false) {
                Log::error("normalizeImageToPNG - Cannot create GD resource for {$inputPath}");
                return false;
            }

            $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
            if ($canvas === false) {
                imagedestroy($source);
                Log::error("normalizeImageToPNG - Cannot create canvas");
                return false;
            }

            // background putih untuk hilangkan alpha/transparency
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);

            // copy/resize
            if ($width === $targetWidth && $height === $targetHeight) {
                imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);
            } else {
                imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
            }

            // Simpan PNG (compression 0 = besar tapi aman)
            $ok = imagepng($canvas, $outputPath, 0);

            imagedestroy($source);
            imagedestroy($canvas);

            if (!$ok) {
                Log::error("normalizeImageToPNG - Failed to save PNG: {$outputPath}");
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("normalizeImageToPNG - Exception: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Overlay header/footer images onto an existing PDF using FPDI+TCPDF.
     * Returns true on success, false on failure.
     */
    /**
     * Tempel header/footer ke PDF.
     * mode:
     *  - 'overlay'      : konten tidak digeser (bisa overlap).
     *  - 'content-box'  : konten diskalakan & diposisikan di antara header/footer (tanpa overlap).
     */
    private function overlayHeaderFooterOnPdf(
        string $srcPath,
        string $destPath,
        string $headerPath = null,
        float $headerMm = 28,
        string $footerPath = null,
        float $footerMm = 20,
        string $mode = 'content-box' // <-- default aman
    ): bool {
        if (!class_exists('\\setasign\\Fpdi\\Tcpdf\\Fpdi')) {
            \Log::warning('FPDI-TCPDF not available');
            return false;
        }

        try {
            $pdf = new \setasign\Fpdi\Tcpdf\Fpdi('P', 'mm');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false, 0);
            $pdf->setImageScale(1.0);

            $pageCount = $pdf->setSourceFile($srcPath);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplId = $pdf->importPage($pageNo);
                $tplSize = $pdf->getTemplateSize($tplId); // ['width','height'] dalam mm (berdasarkan unit TCPDF)

                // Buat halaman baru dengan ukuran halaman sumber (bisa A4 atau ukuran lain)
                $orientation = ($tplSize['width'] > $tplSize['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$tplSize['width'], $tplSize['height']]);

                $pageW = $pdf->getPageWidth();
                $pageH = $pdf->getPageHeight();

                // Clamp tinggi header/footer agar tidak berlebihan
                $headerMm = min(max(0.0, $headerMm), $pageH * 0.45);
                $footerMm = min(max(0.0, $footerMm), $pageH * 0.45);

                if ($mode === 'overlay') {
                    // Tempel konten full page (potensi overlap)
                    $pdf->useTemplate($tplId, 0, 0, $pageW, $pageH);
                } else {
                    // MODE CONTENT-BOX: skala isi agar pas di antara header/footer
                    $contentTopY    = $headerMm;
                    $contentBottomY = $pageH - $footerMm;
                    $contentH       = max(0.0, $contentBottomY - $contentTopY);
                    $contentW       = $pageW;

                    // Faktor skala (preserve rasio): muat ke area konten
                    $scale = min($contentW / $tplSize['width'], $contentH / $tplSize['height']);

                    $drawW = $tplSize['width'] * $scale;
                    $drawH = $tplSize['height'] * $scale;

                    // Center secara horizontal; vertikal mulai tepat setelah header
                    $drawX = max(0.0, ($pageW - $drawW) / 2.0);
                    $drawY = $contentTopY + max(0.0, ($contentH - $drawH) / 2.0);

                    $pdf->useTemplate($tplId, $drawX, $drawY, $drawW, $drawH);
                }

                // Opsi: tutup area header/footer dengan kotak putih agar elemen lama tak terlihat
                // (aktifkan jika PDF sumber punya footer/header sendiri)
                if ($mode === 'content-box') {
                    $pdf->SetFillColor(255, 255, 255);
                    if ($headerMm > 0) $pdf->Rect(0, 0, $pageW, $headerMm, 'F');
                    if ($footerMm > 0) $pdf->Rect(0, $pageH - $footerMm, $pageW, $footerMm, 'F');
                }

                // Gambar HEADER
                if ($headerPath && file_exists($headerPath) && $headerMm > 0) {
                    $pdf->Image($headerPath, 0, 0, $pageW, $headerMm, '', '', '', true, 300, '', false, false, 0, false, false, true);
                }

                // Gambar FOOTER
                if ($footerPath && file_exists($footerPath) && $footerMm > 0) {
                    $pdf->Image($footerPath, 0, $pageH - $footerMm, $pageW, $footerMm, '', '', '', true, 300, '', false, false, 0, false, false, true);
                }
            }

            $pdf->Output($destPath, 'F');
            return true;
        } catch (\Throwable $e) {
            \Log::error('overlayHeaderFooterOnPdf error: ' . $e->getMessage());
            return false;
        }
    }


    private function resolveStoragePath(string $path): string
    {
        Log::info("Mencoba resolve path lampiran: {$path}");

        if (File::isFile($path)) {
            Log::info("Path lampiran ditemukan di lokasi 1: {$path}");
            return $path;
        }

        $candidates = [
            storage_path('app/' . ltrim($path, '/')),
            storage_path('app/public/' . ltrim($path, '/')),
        ];

        if (Str::startsWith($path, 'storage/')) {
            $candidates[] = storage_path('app/public/' . substr($path, strlen('storage/')));
        }

        foreach ($candidates as $c) {
            Log::info("Checking candidate: {$c}");
            if (File::isFile($c)) {
                Log::info("Path lampiran ditemukan: {$c}");
                return $c;
            }
        }

        Log::error("Lampiran tidak ditemukan pada path: {$path}. Candidates: " . json_encode($candidates));
        throw new \RuntimeException("Lampiran tidak ditemukan pada path: {$path}");
    }

    private function cleanupTempFiles(array $paths): void
    {
        foreach ($paths as $p) {
            try {
                if ($p && File::exists($p)) File::delete($p);
            } catch (\Throwable $e) {
            }
        }
    }

    public function getGMFromKode($kode)
    {
        $divisi = Divisi::where('kode_divisi', $kode)->first();
        $users = collect();

        if ($divisi) {
            $users = User::where('divisi_id_divisi', $divisi->id_divisi)->get();
        } else {
            $department = Department::where('kode_department', $kode)->first();
            if ($department) {
                $users = User::where('department_id_department', $department->id_department)->get();
            } else {
                $director = Director::where('kode_director', $kode)->first();
                if ($director) {
                    $users = User::where('director_id_director', $director->id_director)->get();
                } else {
                    return response()->json(['error' => 'Kode tidak valid'], 404);
                }
            }
        }

        for ($i = 1; $i <= 9; $i++) {
            $user = $users->firstWhere('position_id_position', $i);
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Download semua lampiran dari memo dalam format ZIP
     */
    public function downloadAllAttachmentsZip($id_memo)
    {
        try {
            $memo = Memo::findOrFail($id_memo);

            if (!$memo->lampiran) {
                return response()->json(['error' => 'Tidak ada lampiran untuk diunduh'], 404);
            }

            // Parse lampiran data
            $lampiranItems = $this->parseLampiranData($memo->lampiran);

            if (empty($lampiranItems)) {
                return response()->json(['error' => 'Tidak ada lampiran valid untuk diunduh'], 404);
            }

            // Buat ZIP file
            $zipFileName = 'lampiran_' . Str::slug($memo->judul) . '_' . $memo->id_memo . '.zip';
            $zipPath = storage_path('app/lampiran_zip_' . time() . '_' . uniqid() . '.zip');

            if (!class_exists('ZipArchive')) {
                return response()->json(['error' => 'ZipArchive tidak tersedia di server'], 500);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                return response()->json(['error' => 'Gagal membuat file ZIP'], 500);
            }

            $addedFiles = 0;
            foreach ($lampiranItems as $index => $lampiran) {
                try {
                    // Handle both string paths and array with 'path' key
                    $filePath = null;
                    $fileName = null;

                    if (is_array($lampiran) && isset($lampiran['path'])) {
                        $filePath = $this->resolveStoragePath($lampiran['path']);
                        $fileName = $lampiran['name'] ?? basename($filePath);
                    } elseif (is_string($lampiran)) {
                        $filePath = $this->resolveStoragePath($lampiran);
                        $fileName = basename($filePath);
                    }

                    if ($filePath && File::exists($filePath)) {
                        // Gunakan nama file dari data jika ada, atau ekstrak dari path
                        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
                        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                        $archiveName = $fileName;
                        $counter = 1;
                        while ($zip->locateName($archiveName) !== false) {
                            $archiveName = $baseName . '_' . $counter . '.' . $extension;
                            $counter++;
                        }

                        $zip->addFile($filePath, $archiveName);
                        $addedFiles++;
                        Log::info("File ditambahkan ke ZIP: {$filePath} -> {$archiveName}");
                    }
                } catch (\Throwable $e) {
                    Log::warning('Gagal menambahkan lampiran ke ZIP: ' . $e->getMessage());
                    // Lanjutkan ke lampiran berikutnya
                    continue;
                }
            }

            $zip->close();

            if ($addedFiles === 0) {
                File::delete($zipPath);
                return response()->json(['error' => 'Tidak ada file lampiran yang valid'], 404);
            }

            Log::info("ZIP dibuat dengan {$addedFiles} file: {$zipPath}");

            return response()->download($zipPath, $zipFileName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Error in downloadAllAttachmentsZip: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengunduh lampiran: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Parse lampiran data dari berbagai format
     * Return array yang bisa berisi:
     * - string path
     * - array dengan 'path' key
     */
    private function parseLampiranData($lampiranField): array
    {
        if (is_null($lampiranField)) {
            return [];
        }

        $items = [];

        if (is_string($lampiranField)) {
            $trim = trim($lampiranField);

            // JSON array?
            if (strlen($trim) > 1 && ($trim[0] === '[' || $trim[0] === '{')) {
                try {
                    $decoded = json_decode($trim, true);
                    if (is_array($decoded)) {
                        // Check jika array of objects dengan 'path' key
                        if (count($decoded) > 0 && isset($decoded[0]['path'])) {
                            $items = $decoded; // Return array of arrays dengan 'path' key
                        } elseif (count($decoded) > 0 && is_string($decoded[0])) {
                            $items = $decoded; // Return array of strings
                        } else {
                            $items = [$lampiranField];
                        }
                    } else {
                        $items = [$lampiranField];
                    }
                } catch (\Throwable $e) {
                    Log::warning('JSON decode failed: ' . $e->getMessage());
                    $items = [$lampiranField];
                }
            } else {
                // Pisah dengan ';'
                $parts = array_filter(array_map('trim', explode(';', $lampiranField)), fn($v) => $v !== '');
                $items = count($parts) ? $parts : [$lampiranField];
            }
        } elseif (is_array($lampiranField)) {
            $items = $lampiranField;
        } elseif ($lampiranField instanceof \Illuminate\Support\Collection) {
            $items = $lampiranField->map(function ($row) {
                foreach (['path', 'file', 'url', 'lampiran', 'content', 'blob', 'base64'] as $key) {
                    if (isset($row[$key])) return $row[$key];
                    if (isset($row->$key)) return $row->$key;
                }
                return null;
            })->filter()->values()->all();
        }


        Log::info('parseLampiranData result: ' . json_encode($items));
        return $items;
    }

    /**
     * Download semua lampiran dari undangan dalam format ZIP
     */
    public function downloadAllAttachmentsZipUndangan($id_undangan)
    {
        try {
            $undangan = Undangan::findOrFail($id_undangan);

            if (!$undangan->lampiran) {
                return response()->json(['error' => 'Tidak ada lampiran untuk diunduh'], 404);
            }

            // Parse lampiran data
            $lampiranItems = $this->parseLampiranData($undangan->lampiran);

            if (empty($lampiranItems)) {
                return response()->json(['error' => 'Tidak ada lampiran valid untuk diunduh'], 404);
            }

            // Buat ZIP file
            $zipFileName = 'lampiran_' . Str::slug($undangan->judul) . '_' . $undangan->id_undangan . '.zip';
            $zipPath = storage_path('app/lampiran_zip_' . time() . '_' . uniqid() . '.zip');

            if (!class_exists('ZipArchive')) {
                return response()->json(['error' => 'ZipArchive tidak tersedia di server'], 500);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                return response()->json(['error' => 'Gagal membuat file ZIP'], 500);
            }

            $addedFiles = 0;
            foreach ($lampiranItems as $index => $lampiran) {
                try {
                    // Handle both string paths and array with 'path' key
                    $filePath = null;
                    $fileName = null;

                    if (is_array($lampiran) && isset($lampiran['path'])) {
                        $filePath = $this->resolveStoragePath($lampiran['path']);
                        $fileName = $lampiran['name'] ?? basename($filePath);
                    } elseif (is_string($lampiran)) {
                        $filePath = $this->resolveStoragePath($lampiran);
                        $fileName = basename($filePath);
                    }

                    if ($filePath && File::exists($filePath)) {
                        // Gunakan nama file dari data jika ada, atau ekstrak dari path
                        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
                        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                        $archiveName = $fileName;
                        $counter = 1;
                        while ($zip->locateName($archiveName) !== false) {
                            $archiveName = $baseName . '_' . $counter . '.' . $extension;
                            $counter++;
                        }

                        $zip->addFile($filePath, $archiveName);
                        $addedFiles++;
                        Log::info("File ditambahkan ke ZIP: {$filePath} -> {$archiveName}");
                    }
                } catch (\Throwable $e) {
                    Log::warning('Gagal menambahkan lampiran ke ZIP: ' . $e->getMessage());
                    // Lanjutkan ke lampiran berikutnya
                    continue;
                }
            }

            $zip->close();

            if ($addedFiles === 0) {
                File::delete($zipPath);
                return response()->json(['error' => 'Tidak ada file lampiran yang valid'], 404);
            }

            Log::info("ZIP dibuat dengan {$addedFiles} file: {$zipPath}");

            return response()->download($zipPath, $zipFileName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Error in downloadAllAttachmentsZipUndangan: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengunduh lampiran: ' . $e->getMessage()], 500);
        }
    }

    public function downloadAllAttachmentsZipRisalah($id_risalah)
    {
        try {
            $risalah = Risalah::findOrFail($id_risalah);

            if (!$risalah->lampiran) {
                return response()->json(['error' => 'Tidak ada lampiran untuk diunduh'], 404);
            }

            // Parse lampiran data
            $lampiranItems = $this->parseLampiranData($risalah->lampiran);

            if (empty($lampiranItems)) {
                return response()->json(['error' => 'Tidak ada lampiran valid untuk diunduh'], 404);
            }

            // Buat ZIP file
            $zipFileName = 'lampiran_' . Str::slug($risalah->judul) . '_' . $risalah->id_risalah . '.zip';
            $zipPath = storage_path('app/lampiran_zip_' . time() . '_' . uniqid() . '.zip');

            if (!class_exists('ZipArchive')) {
                return response()->json(['error' => 'ZipArchive tidak tersedia di server'], 500);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                return response()->json(['error' => 'Gagal membuat file ZIP'], 500);
            }

            $addedFiles = 0;
            foreach ($lampiranItems as $index => $lampiran) {
                try {
                    // Handle both string paths and array with 'path' key
                    $filePath = null;
                    $fileName = null;

                    if (is_array($lampiran) && isset($lampiran['path'])) {
                        $filePath = $this->resolveStoragePath($lampiran['path']);
                        $fileName = $lampiran['name'] ?? basename($filePath);
                    } elseif (is_string($lampiran)) {
                        $filePath = $this->resolveStoragePath($lampiran);
                        $fileName = basename($filePath);
                    }

                    if ($filePath && File::exists($filePath)) {
                        // Gunakan nama file dari data jika ada, atau ekstrak dari path
                        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
                        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                        $archiveName = $fileName;
                        $counter = 1;
                        while ($zip->locateName($archiveName) !== false) {
                            $archiveName = $baseName . '_' . $counter . '.' . $extension;
                            $counter++;
                        }

                        $zip->addFile($filePath, $archiveName);
                        $addedFiles++;
                        Log::info("File ditambahkan ke ZIP: {$filePath} -> {$archiveName}");
                    }
                } catch (\Throwable $e) {
                    Log::warning('Gagal menambahkan lampiran ke ZIP: ' . $e->getMessage());
                    // Lanjutkan ke lampiran berikutnya
                    continue;
                }
            }

            $zip->close();

            if ($addedFiles === 0) {
                File::delete($zipPath);
                return response()->json(['error' => 'Tidak ada file lampiran yang valid'], 404);
            }

            Log::info("ZIP dibuat dengan {$addedFiles} file: {$zipPath}");

            return response()->download($zipPath, $zipFileName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Error in downloadAllAttachmentsZipRisalah: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengunduh lampiran: ' . $e->getMessage()], 500);
        }
    }
}
