<?php

namespace App\Filament\Pages\Auth;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;

class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    public static function getSessions(): array
    {
        if (config(key: 'session.driver') !== 'database') {
            return [];
        }

        return collect(
            value: DB::connection(config(key: 'session.connection'))->table(table: config(key: 'session.table', default: 'sessions'))
                ->where(column: 'user_id', operator: Auth::user()->getAuthIdentifier())
                ->latest(column: 'last_activity')
                ->get()
        )->map(callback: function ($session): object {
            $agent = self::createAgent($session);

            return (object) [
                'device' => [
                    'browser' => $agent->browser(),
                    'desktop' => $agent->isDesktop(),
                    'mobile' => $agent->isMobile(),
                    'tablet' => $agent->isTablet(),
                    'platform' => $agent->platform(),
                ],
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === request()->session()->getId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        })->toArray();
    }

    public static function logoutOtherBrowserSessions($password): void
    {
        if (! Hash::check($password, Auth::user()->password)) {
            Notification::make()
                ->danger()
                ->title(__('filament-edit-profile::default.incorrect_password'))
                ->send();

            return;
        }

        Auth::guard()->logoutOtherDevices($password);

        request()->session()->put([
            'password_hash_' . Auth::getDefaultDriver() => Auth::user()->getAuthPassword(),
        ]);

        self::deleteOtherSessionRecords();

        Notification::make()
            ->success()
            ->title(__('filament-edit-profile::default.browser_sessions_logout_success_notification'))
            ->send();
    }

    protected static function createAgent(mixed $session)
    {
        return tap(
            value: new Agent,
            callback: fn ($agent) => $agent->setUserAgent(userAgent: $session->user_agent)
        );
    }

    protected static function deleteOtherSessionRecords()
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
            ->where('user_id', Auth::user()->getAuthIdentifier())
            ->where('id', '!=', request()->session()->getId())
            ->delete();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make('')
                    ->schema([
                        FileUpload::make('avatar_url')
                            ->label('Avatar')
                            ->directory('avatars')
                            ->image()
                            ->panelAspectRatio('5:4')
                            ->panelLayout('integrated')
                            ->inlineLabel(false)
                            ->optimize('webp'),
                    ])
                    ->columnSpan(2)
                    ->columns(1),
                Fieldset::make('Details')
                    ->schema([
                        $this->getNameFormComponent()
                            ->inlineLabel(false)
                            ->columnSpan(2),
                        $this->getEmailFormComponent()
                            ->inlineLabel(false)
                            ->columnSpan(2),
                        TextInput::make('Current password')
                            ->label('Current Password')
                            ->password()
                            ->required()
                            ->currentPassword()
                            ->revealable()
                            ->inlineLabel(false)
                            ->columnSpan(2),
                        $this->getPasswordFormComponent()
                            ->inlineLabel(false),
                        $this->getPasswordConfirmationFormComponent()
                            ->inlineLabel(false)
                            ->visible(true),
                    ])
                    ->columnSpan(4)
                    ->columns(2),
                Section::make(__('Browser Sessions '))
                    ->description(__('Manage and log out your active sessions on other browsers and devices. '))
                    ->schema([
                        ViewField::make('browserSessions')
                            ->hiddenLabel()
                            ->view('filament-edit-profile::forms.components.browser-sessions')
                            ->viewData(['data' => self::getSessions()])
                            ->columnSpan(1),
                        Actions::make([
                            Action::make('deleteBrowserSessions')
                                ->label(__('filament-edit-profile::default.browser_sessions_log_out'))
                                ->requiresConfirmation()
                                ->modalHeading(__('filament-edit-profile::default.browser_sessions_log_out'))
                                ->modalDescription(__('filament-edit-profile::default.browser_sessions_confirm_pass'))
                                ->modalSubmitActionLabel(__('filament-edit-profile::default.browser_sessions_log_out'))
                                ->schema([
                                    TextInput::make('password')
                                        ->password()
                                        ->revealable()
                                        ->label(__('filament-edit-profile::default.password'))
                                        ->required(),
                                ])
                                ->action(function (array $data) {
                                    self::logoutOtherBrowserSessions($data['password']);
                                })
                                ->modalWidth('2xl'),
                        ]),
                    ]),
            ])->columns(6);
    }

    public function getFormActionsAlignment(): string|Alignment
    {
        return Alignment::End;
    }
}
