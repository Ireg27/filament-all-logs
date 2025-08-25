<?php
namespace Boquizo\FilamentLogViewer\Actions;

use Boquizo\FilamentLogViewer\FilamentLogViewerPlugin;
use Boquizo\FilamentLogViewer\Pages\ListLogs;
use Boquizo\FilamentLogViewer\Pages\ViewLog;
use Filament\Actions\Action;
use http\Exception\RuntimeException;
use Illuminate\Support\Facades\Config;
use Exception;

class ClearLogsAction
{
    public static function make(bool $withTooltip = false): Action
    {
        $action = Action::make('clear-logs')
            ->hiddenLabel()
            ->button()
            ->label(__('filament-log-viewer::log.table.actions.clear.label'))
            ->modalHeading(__('filament-log-viewer::log.table.actions.clear.confirm'))
            ->color('danger')
            ->icon('fas-broom')
            ->requiresConfirmation()
            ->action(fn (ViewLog $livewire) => self::clearCurrentLog($livewire));

        if ($withTooltip) {
            $action->tooltip(__('filament-log-viewer::log.table.actions.clear.label'));
        }

        return $action;
    }

    private static function clearCurrentLog(ViewLog $livewire): void
    {
        $record = $livewire->record;
        $dateOrFile = $record?->date ?? $record['date'] ?? null;

        if (! $dateOrFile) {
            throw new RuntimeException('No log file selected to clear.');
        }

        $storagePath = Config::string('filament-log-viewer.storage_path', storage_path('logs'));

        $filePath = $storagePath . DIRECTORY_SEPARATOR . $dateOrFile;

        if (! file_exists($filePath)) {
            throw new RuntimeException("The log file could not be located at: {$filePath}");
        }

        // Truncate the file
        file_put_contents($filePath, '');
    }
}
