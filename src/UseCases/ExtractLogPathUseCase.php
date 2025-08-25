<?php

declare(strict_types=1);

namespace Boquizo\FilamentLogViewer\UseCases;

use Illuminate\Support\Facades\Config;
use RuntimeException;

class ExtractLogPathUseCase
{
    /**
     * Execute the use case.
     *
     * @param string $dateOrFilename
     * @return false|string
     */
    public static function execute(string $dateOrFilename): false|string
    {
        return (new self())($dateOrFilename);
    }

    /**
     * Invoke the use case.
     *
     * @param string $dateOrFilename
     * @return false|string
     */
    public function __invoke(string $dateOrFilename): false|string
    {
        $path = $this->path($dateOrFilename);

        if (! file_exists($path)) {
            throw new RuntimeException(
                "The log(s) could not be located at: {$path}"
            );
        }

        return realpath($path);
    }

    /**
     * Build the full path to a log file.
     *
     * @param string $dateOrFilename
     * @return string
     */
    private function path(string $dateOrFilename): string
    {
        $storagePath = Config::string('filament-log-viewer.storage_path', storage_path('logs'));
        $showAll = Config::get('filament-log-viewer.show_all_logs', false);

        if ($showAll) {
            // In “all logs” mode, $dateOrFilename is treated as the full filename
            return $storagePath . DIRECTORY_SEPARATOR . $dateOrFilename;
        }

        // Original behavior: prefix + date + extension
        $prefix = Config::string('filament-log-viewer.pattern.prefix', 'laravel-');
        $extension = Config::string('filament-log-viewer.pattern.extension', '.log');

        return $storagePath . DIRECTORY_SEPARATOR . $prefix . $dateOrFilename . $extension;
    }
}
