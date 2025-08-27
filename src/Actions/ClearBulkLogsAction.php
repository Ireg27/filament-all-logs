<?php

namespace Boquizo\FilamentLogViewer\Actions;

use Filament\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class ClearBulkLogsAction
{
    public static function make(): Action
    {
        return Action::make('clear-bulk-logs')
            ->label(__('filament-log-viewer::log.table.actions.clear_bulk.label'))
            ->color('danger')
            ->icon('fas-broom')
            ->requiresConfirmation()
            ->accessSelectedRecords()
            ->modalHeading(__('filament-log-viewer::log.table.actions.clear_bulk.confirm'))
            ->action(function (Collection $records): void {
                self::clearSelectedLogs($records);
            })
            ->bulk();
    }

    /**
     * @param \Illuminate\Support\Collection<int, mixed> $records
     */
    private static function clearSelectedLogs(Collection $records): void
    {
        $storagePath = Config::string('filament-log-viewer.storage_path', storage_path('logs'));

        foreach ($records as $record) {
            $dateOrFile = $record['date'] ?? $record->date ?? null;

            if (! $dateOrFile) {
                continue;
            }

            $filePath = $storagePath . DIRECTORY_SEPARATOR . $dateOrFile;

            if (file_exists($filePath)) {
                file_put_contents($filePath, '');
            }
        }
    }
}
