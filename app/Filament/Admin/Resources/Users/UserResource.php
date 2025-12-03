<?php

namespace App\Filament\Admin\Resources\Users;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Filament\Admin\Resources\Users\Pages\CreateUser;
use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static string | \UnitEnum | null $navigationGroup = 'Administration';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = -2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'roles.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Role' => $record->roles->pluck('name')->implode(', '),
            'Email' => $record->email,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->minLength(2)
                            ->maxLength(255)
                            ->columnSpan('full')
                            ->required(),
                        FileUpload::make('avatar_url')
                            ->label('Avatar')
                            ->image()
                            ->optimize('webp')
                            ->imageEditor()
                            ->imagePreviewHeight('250')
                            ->panelAspectRatio('7:2')
                            ->panelLayout('integrated')
                            ->columnSpan('full'),
                        TextInput::make('email')
                            ->required()
                            ->prefixIcon('heroicon-m-envelope')
                            ->columnSpan('full')
                            ->email(),

                        TextInput::make('password')
                            ->password()
                            ->confirmed()
                            ->columnSpan(1)
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        TextInput::make('password_confirmation')
                            ->required(fn (string $context): bool => $context === 'create')
                            ->columnSpan(1)
                            ->password(),
                    ]),

                Section::make('Roles')
                    ->schema([
                        Select::make('roles')
                            ->required()
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->label('Roles'),
                    ])
                    ->columns(1),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('avatar_url')
                    ->defaultImageUrl(url('https://www.gravatar.com/avatar/64e1b8d34f425d19e1ee2ea7236d3028?d=mp&r=g&s=250'))
                    ->label('Avatar')
                    ->circular(),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->searchable(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
