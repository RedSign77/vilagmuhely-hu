<?php

namespace Webtechsolutions\UserManager\Filament\Resources\UserActivityLogResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Webtechsolutions\UserManager\Filament\Resources\UserActivityLogResource;
use Webtechsolutions\UserManager\Models\UserActivityLog;

class ListUserActivityLogs extends ListRecords
{
    protected static string $resource = UserActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cleanup')
                ->label('Clean Up Old Logs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Clean Up Old Activity Logs')
                ->modalDescription('This will delete activity logs older than 90 days. This action cannot be undone.')
                ->action(function () {
                    $count = UserActivityLog::deleteOlderThan(90);
                    $this->notify('success', "Successfully deleted {$count} old activity log(s).");
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Activities'),

            'login' => Tab::make('Logins')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('activity_type', UserActivityLog::TYPE_LOGIN))
                ->badge(UserActivityLog::ofType(UserActivityLog::TYPE_LOGIN)->count()),

            'logout' => Tab::make('Logouts')
                ->icon('heroicon-o-arrow-left-on-rectangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('activity_type', UserActivityLog::TYPE_LOGOUT))
                ->badge(UserActivityLog::ofType(UserActivityLog::TYPE_LOGOUT)->count()),

            'failed_login' => Tab::make('Failed Logins')
                ->icon('heroicon-o-shield-exclamation')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('activity_type', UserActivityLog::TYPE_FAILED_LOGIN))
                ->badge(UserActivityLog::ofType(UserActivityLog::TYPE_FAILED_LOGIN)->count())
                ->badgeColor('danger'),

            'profile_change' => Tab::make('Profile Changes')
                ->icon('heroicon-o-user')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('activity_type', UserActivityLog::TYPE_PROFILE_CHANGE))
                ->badge(UserActivityLog::ofType(UserActivityLog::TYPE_PROFILE_CHANGE)->count()),

            'password_change' => Tab::make('Password Changes')
                ->icon('heroicon-o-key')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('activity_type', UserActivityLog::TYPE_PASSWORD_CHANGE))
                ->badge(UserActivityLog::ofType(UserActivityLog::TYPE_PASSWORD_CHANGE)->count()),

            'role_change' => Tab::make('Role Changes')
                ->icon('heroicon-o-shield-check')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('activity_type', UserActivityLog::TYPE_ROLE_CHANGE))
                ->badge(UserActivityLog::ofType(UserActivityLog::TYPE_ROLE_CHANGE)->count()),
        ];
    }
}
