<?php

declare(strict_types=1);

namespace Boquizo\FilamentLogViewer\UseCases;

use Boquizo\FilamentLogViewer\Utils\Parser;
use Illuminate\Support\Facades\Config;

class ExtractDatesUseCase
{
    /** @return array<string, string> */
    public static function execute(): array
    {
        return (new self())();
    }

    /** @return array<string, string> */
    public function __invoke(): array
    {
        $files = $this->files();

        // [date => file]
        return array_combine(
            $this->extractDates($files),
            $files,
        );
    }

    /**
     * @param list<string> $files
     *
     * @return array<string, string>
     */
    private function extractDates(array $files): array
    {
        return array_map(
            static fn (string $file): string => Parser::extractDate(basename($file)),
            $files,
        );
    }

    /** @return list<string> */
    private function files(): array
    {
        $storagePath = Config::string('filament-log-viewer.storage_path');
        $showAll = Config::get('filament-log-viewer.show_all_logs', false);

        $pattern = $showAll
            ? '*' . Config::string('filament-log-viewer.pattern.extension')
            : $this->pattern();

        $files = glob(
            $storagePath . DIRECTORY_SEPARATOR . $pattern,
            defined('GLOB_BRACE') ? GLOB_BRACE : 0
        );

        $files = array_map('realpath', $files);

        $ignore = Config::get('filament-log-viewer.ignore_files', []);

        $files = array_filter($files, function ($file) use ($ignore) {
            $basename = basename($file);
            foreach ($ignore as $word) {
                if (str_contains($basename, $word)) {
                    return false;
                }
            }
            return true;
        });

        return array_reverse($files);
    }


    private function pattern(): string
    {
        $patterns = (object) Config::array('filament-log-viewer.pattern');

        return $patterns->prefix.$patterns->date.$patterns->extension;
    }
}
