<?php

namespace App\Console\Commands;

use App\Models\TestDrive;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProtectLicensePhotosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-drives:protect-license-photos
                            {--dry-run : Hanya tampilkan inventarisasi dan rencana migrasi tanpa menyalin atau menghapus file}
                            {--delete-public : Hapus berkas dari disk publik setelah disalin dan diverifikasi ke disk privat}
                            {--force : Jalankan penghapusan tanpa konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasikan berkas foto SIM test drive dari penyimpanan publik ke disk privat local dengan verifikasi integritas checksum SHA-256';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $deletePublic = (bool) $this->option('delete-public');
        $force = (bool) $this->option('force');

        $this->info('================================================================');
        $this->info(' PROSEDUR MIGRASI DAN PERLINDUNGAN FOTO SIM (AUDIT #6)');
        $this->info('================================================================');

        if ($isDryRun) {
            $this->warn('MODE: DRY-RUN (Tidak ada perubahan pada sistem penyimpanan)');
        }

        $testDrives = TestDrive::whereNotNull('driver_license_image')->get();
        $publicFiles = Storage::disk('public')->allFiles('licenses');

        $this->line("Memeriksa {$testDrives->count()} data test drive dan ".count($publicFiles).' berkas di disk public...');

        $tableRows = [];
        $migratedCount = 0;
        $alreadyPrivateCount = 0;
        $missingCount = 0;
        $errorCount = 0;
        $filesEligibleForDeletion = [];

        // 1. Proses berkas yang tercatat di database
        foreach ($testDrives as $td) {
            $rawPath = (string) $td->driver_license_image;
            $path = ltrim(str_replace('\\', '/', $rawPath), '/');

            // Tangani jika prefix 'public/' sempat tersimpan di DB
            if (str_starts_with($path, 'public/')) {
                $path = substr($path, 7);
                if (! $isDryRun) {
                    $td->update(['driver_license_image' => $path]);
                }
            }

            $inPublic = Storage::disk('public')->exists($path);
            $inLocal = Storage::disk('local')->exists($path);

            if (! $inPublic && ! $inLocal) {
                $tableRows[] = [$path, "TD #{$td->id}", 'HILANG (Tidak ditemukan)', '-'];
                $missingCount++;

                continue;
            }

            if ($inPublic) {
                $publicContent = (string) Storage::disk('public')->get($path);
                $publicChecksum = hash('sha256', $publicContent);

                if ($inLocal) {
                    $localContent = (string) Storage::disk('local')->get($path);
                    $localChecksum = hash('sha256', $localContent);

                    if ($localChecksum === $publicChecksum) {
                        $tableRows[] = [$path, "TD #{$td->id}", 'Sudah di Privat (Checksum Valid)', substr($publicChecksum, 0, 12).'...'];
                        $alreadyPrivateCount++;
                        $filesEligibleForDeletion[] = $path;
                    } else {
                        if ($isDryRun) {
                            $tableRows[] = [$path, "TD #{$td->id}", '[DRY-RUN] Butuh Timpa (Checksum Beda)', substr($publicChecksum, 0, 12).'...'];
                        } else {
                            Storage::disk('local')->put($path, $publicContent);
                            $localContent = (string) Storage::disk('local')->get($path);
                            $localChecksum = hash('sha256', $localContent);

                            if ($localChecksum === $publicChecksum) {
                                $tableRows[] = [$path, "TD #{$td->id}", 'Diperbarui di Privat (Checksum Valid)', substr($publicChecksum, 0, 12).'...'];
                                $migratedCount++;
                                $filesEligibleForDeletion[] = $path;
                            } else {
                                $tableRows[] = [$path, "TD #{$td->id}", 'GAGAL: Mismatch Checksum setelah Timpa', '-'];
                                $errorCount++;
                                Log::error("[Migrasi SIM] Checksum mismatch saat menimpa {$path}");
                            }
                        }
                    }
                } else {
                    if ($isDryRun) {
                        $tableRows[] = [$path, "TD #{$td->id}", '[DRY-RUN] Akan Disalin ke Privat', substr($publicChecksum, 0, 12).'...'];
                    } else {
                        Storage::disk('local')->put($path, $publicContent);
                        $localContent = (string) Storage::disk('local')->get($path);
                        $localChecksum = hash('sha256', $localContent);

                        if ($localChecksum === $publicChecksum) {
                            $tableRows[] = [$path, "TD #{$td->id}", 'Berhasil Disalin & Diverifikasi', substr($publicChecksum, 0, 12).'...'];
                            $migratedCount++;
                            $filesEligibleForDeletion[] = $path;
                        } else {
                            $tableRows[] = [$path, "TD #{$td->id}", 'GAGAL: Mismatch Checksum', '-'];
                            $errorCount++;
                            Log::error("[Migrasi SIM] Checksum mismatch saat menyalin {$path}");
                        }
                    }
                }
            } else {
                // Hanya ada di local privat
                $localContent = (string) Storage::disk('local')->get($path);
                $localChecksum = hash('sha256', $localContent);
                $tableRows[] = [$path, "TD #{$td->id}", 'Sudah di Privat', substr($localChecksum, 0, 12).'...'];
                $alreadyPrivateCount++;
            }
        }

        // 2. Periksa berkas yatim (orphan) di folder licenses publik
        $trackedPaths = $testDrives->map(function ($t) {
            $p = ltrim(str_replace('\\', '/', (string) $t->driver_license_image), '/');

            return str_starts_with($p, 'public/') ? substr($p, 7) : $p;
        })->all();

        foreach ($publicFiles as $orphanPath) {
            $normalizedOrphan = ltrim(str_replace('\\', '/', $orphanPath), '/');

            // Abaikan berkas marker git atau file tersembunyi
            if (basename($normalizedOrphan) === '.gitignore' || str_starts_with(basename($normalizedOrphan), '.')) {
                continue;
            }

            if (in_array($normalizedOrphan, $trackedPaths, true)) {
                continue;
            }

            $orphanContent = (string) Storage::disk('public')->get($normalizedOrphan);
            $orphanChecksum = hash('sha256', $orphanContent);

            if ($isDryRun) {
                $tableRows[] = [$normalizedOrphan, 'Yatim (Publik)', '[DRY-RUN] Akan Diamankan ke Privat', substr($orphanChecksum, 0, 12).'...'];
            } else {
                Storage::disk('local')->put($normalizedOrphan, $orphanContent);
                $localOrphanContent = (string) Storage::disk('local')->get($normalizedOrphan);
                $localOrphanChecksum = hash('sha256', $localOrphanContent);

                if ($localOrphanChecksum === $orphanChecksum) {
                    $tableRows[] = [$normalizedOrphan, 'Yatim (Publik)', 'Diamankan ke Privat (Yatim)', substr($orphanChecksum, 0, 12).'...'];
                    $filesEligibleForDeletion[] = $normalizedOrphan;
                } else {
                    $tableRows[] = [$normalizedOrphan, 'Yatim (Publik)', 'GAGAL: Mismatch Checksum Yatim', '-'];
                    $errorCount++;
                    Log::error("[Migrasi SIM] Checksum mismatch berkas yatim {$normalizedOrphan}");
                }
            }
        }

        $this->table(['Jalur Berkas', 'Referensi', 'Status Migrasi', 'Checksum (SHA-256)'], $tableRows);

        $this->newLine();
        $this->info("Ringkasan: {$migratedCount} disalin/diverifikasi, {$alreadyPrivateCount} sudah privat, {$missingCount} hilang, {$errorCount} gagal.");

        // 3. Penghapusan salinan publik jika diminta
        $deletedCount = 0;
        if ($deletePublic) {
            $filesEligibleForDeletion = array_unique($filesEligibleForDeletion);

            if (empty($filesEligibleForDeletion)) {
                $this->info('Tidak ada berkas publik yang memenuhi syarat untuk dihapus.');
            } elseif ($isDryRun) {
                $this->warn('[DRY-RUN] Terdapat '.count($filesEligibleForDeletion).' berkas publik yang siap dihapus setelah disalin ke privat.');
            } else {
                $confirmed = $force || $this->confirm(
                    'Konfirmasi: Apakah Anda yakin ingin menghapus '.count($filesEligibleForDeletion).' berkas foto SIM dari storage publik?',
                    false
                );

                if ($confirmed) {
                    foreach ($filesEligibleForDeletion as $deletePath) {
                        if (Storage::disk('local')->exists($deletePath) && Storage::disk('public')->exists($deletePath)) {
                            $localDelContent = (string) Storage::disk('local')->get($deletePath);
                            $publicDelContent = (string) Storage::disk('public')->get($deletePath);

                            if (hash('sha256', $localDelContent) === hash('sha256', $publicDelContent)) {
                                Storage::disk('public')->delete($deletePath);
                                $deletedCount++;
                            } else {
                                $this->error("Gagal menghapus {$deletePath} dari publik: Checksum tidak cocok dengan disk privat.");
                                $errorCount++;
                            }
                        }
                    }
                    $this->info("Berhasil menghapus {$deletedCount} berkas foto SIM dari storage publik.");
                } else {
                    $this->warn('Penghapusan berkas publik dibatalkan oleh pengguna.');
                }
            }
        } else {
            $this->comment('Catatan: Berkas publik dipertahankan. Gunakan opsi --delete-public untuk menghapus salinan publik setelah terverifikasi aman.');
        }

        return $errorCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
