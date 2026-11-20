<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Converts an uploaded PowerPoint file (PPT/PPTX) to PDF with LibreOffice,
 * so it can be shown page by page like any other PDF material.
 */
class PresentationConverter
{
    public const EXTENSIONS = ['ppt', 'pptx'];

    private const TIMEOUT_SECONDS = 180;

    /**
     * Convert the presentation and return the path of a temporary PDF file.
     * The caller must delete the returned file's directory with cleanup().
     *
     * @throws ValidationException when LibreOffice is missing or conversion fails
     */
    public static function toPdf(UploadedFile $file, string $field = 'file'): string
    {
        $binary = self::binary();

        if ($binary === null) {
            throw ValidationException::withMessages([
                $field => 'Konversi PPT/PPTX belum tersedia karena LibreOffice belum terpasang di server. Pasang LibreOffice, atau simpan presentasi sebagai PDF lalu unggah PDF-nya.',
            ]);
        }

        // PPTX is a ZIP archive, legacy PPT an OLE compound file. Anything else
        // would be imported by LibreOffice as plain text and "converted" anyway.
        $header = (string) file_get_contents($file->getRealPath(), false, null, 0, 8);
        if (! str_starts_with($header, "PK\x03\x04") && ! str_starts_with($header, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1")) {
            throw ValidationException::withMessages([
                $field => 'File bukan presentasi PowerPoint yang valid. Pastikan file PPT/PPTX tidak rusak.',
            ]);
        }

        @set_time_limit(self::TIMEOUT_SECONDS + 60);

        $workDir = storage_path('app/tmp/pptx-'.Str::random(16));
        File::ensureDirectoryExists($workDir);

        $extension = strtolower($file->getClientOriginalExtension());
        $source = $workDir.DIRECTORY_SEPARATOR.'presentation.'.$extension;
        $pdf = $workDir.DIRECTORY_SEPARATOR.'presentation.pdf';
        File::copy($file->getRealPath(), $source);

        [$exitCode, $log] = self::run($binary, $workDir, $source);

        if ($exitCode !== 0 || ! is_file($pdf) || filesize($pdf) === 0) {
            self::cleanup($pdf);
            report(new \RuntimeException("PPT conversion failed (exit {$exitCode}): ".trim($log)));

            throw ValidationException::withMessages([
                $field => 'File presentasi gagal dikonversi ke PDF. Pastikan file tidak rusak atau terkunci password, atau simpan sebagai PDF lalu unggah PDF-nya.',
            ]);
        }

        return $pdf;
    }

    /**
     * Run LibreOffice and return [exit code, combined output].
     *
     * Started with proc_open and an explicit environment instead of the
     * Process facade: web servers (php artisan serve, Apache services) pass
     * PHP a stripped environment without TEMP/USERPROFILE, which makes both
     * Symfony Process (temp files for pipes on Windows) and LibreOffice fail
     * silently. Everything here lives inside the conversion's own folder.
     *
     * @return array{0: int, 1: string}
     */
    private static function run(string $binary, string $workDir, string $source): array
    {
        // A private profile directory lets several conversions run at once
        // and keeps LibreOffice from touching the web server user's profile.
        $profileDir = $workDir.DIRECTORY_SEPARATOR.'profile';
        $profileUrl = 'file:///'.ltrim(str_replace('\\', '/', $profileDir), '/');
        $tempDir = $workDir.DIRECTORY_SEPARATOR.'temp';
        File::ensureDirectoryExists($tempDir);

        $env = array_filter([
            'PATH' => getenv('PATH') ?: null,
            'SystemRoot' => getenv('SystemRoot') ?: getenv('SYSTEMROOT') ?: (PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows' : null),
            'windir' => getenv('windir') ?: (PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows' : null),
            'TEMP' => $tempDir,
            'TMP' => $tempDir,
            'TMPDIR' => $tempDir,
            'HOME' => $profileDir,
            'USERPROFILE' => $profileDir,
            'APPDATA' => $profileDir,
            'LOCALAPPDATA' => $profileDir,
        ]);

        $logFile = $workDir.DIRECTORY_SEPARATOR.'soffice.log';
        $command = [
            $binary,
            '-env:UserInstallation='.$profileUrl,
            '--headless',
            '--norestore',
            '--nologo',
            '--nofirststartwizard',
            '--convert-to',
            'pdf',
            '--outdir',
            $workDir,
            $source,
        ];

        $process = proc_open($command, [
            0 => ['file', PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null', 'r'],
            1 => ['file', $logFile, 'w'],
            2 => ['file', $logFile, 'a'],
        ], $pipes, $workDir, $env, ['bypass_shell' => true]);

        if (! is_resource($process)) {
            return [-1, 'proc_open failed'];
        }

        $deadline = microtime(true) + self::TIMEOUT_SECONDS;
        $exitCode = -1;

        while (true) {
            $status = proc_get_status($process);
            if (! $status['running']) {
                $exitCode = $status['exitcode'];
                break;
            }
            if (microtime(true) > $deadline) {
                proc_terminate($process);
                $exitCode = -2;
                break;
            }
            usleep(200000);
        }

        proc_close($process);

        return [$exitCode, is_file($logFile) ? (string) file_get_contents($logFile) : ''];
    }

    /**
     * Remove the temporary directory created for a conversion.
     */
    public static function cleanup(?string $pdf): void
    {
        if ($pdf && Str::contains($pdf, 'pptx-')) {
            File::deleteDirectory(dirname($pdf));
        }
    }

    /**
     * Path to the LibreOffice executable, from LIBREOFFICE_PATH or the usual install locations.
     */
    public static function binary(): ?string
    {
        $candidates = array_filter([
            config('services.libreoffice.path'),
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
            '/usr/bin/soffice',
            '/usr/bin/libreoffice',
            '/usr/local/bin/soffice',
            '/opt/libreoffice/program/soffice',
            '/Applications/LibreOffice.app/Contents/MacOS/soffice',
        ]);

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
