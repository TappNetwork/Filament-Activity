<?php

declare(strict_types=1);

namespace Tapp\FilamentActivity\Filament\Resources;

use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Tapp\FilamentActivity\Filament\Resources\ActivityResource\Pages\ListActivities;
use Tapp\FilamentActivity\Filament\Resources\ActivityResource\Pages\ViewActivity;
use Tapp\FilamentActivity\Models\Activity;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

    protected static ?int $navigationSort = 40;

    public static function getNavigationLabel(): string
    {
        return __('Activity');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Community');
    }

    public static function isScopedToTenant(): bool
    {
        return config('filament-activity.tenant.enabled', false);
    }

    public static function getTenantOwnershipRelationshipName(): string
    {
        return 'tenant';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('event')
                    ->label(__('Event'))
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->limit(60),
                TextColumn::make('actor.name')
                    ->label(__('Actor'))
                    ->placeholder(__('System'))
                    ->sortable(),
                TextColumn::make('subject_type')
                    ->label(__('Subject'))
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '')
                    ->sortable(),
                TextColumn::make('occurred_at')
                    ->label(__('Occurred'))
                    ->since()
                    ->sortable(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'md' => 3,
                ])
                    ->columnSpanFull()
                    ->schema([
                        Section::make(__('Activity'))
                            ->columnSpan(2)
                            ->schema([
                                TextEntry::make('event')->label(__('Event'))->badge(),
                                TextEntry::make('title')->label(__('Title'))->placeholder('-'),
                                TextEntry::make('summary')->label(__('Summary'))->placeholder('-')->columnSpanFull(),
                                TextEntry::make('metadata')
                                    ->label(__('Metadata'))
                                    ->formatStateUsing(fn (?array $state): string => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) : '-')
                                    ->columnSpanFull(),
                            ]),
                        Section::make(__('Context'))
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('actor.name')->label(__('Actor'))->placeholder(__('System')),
                                TextEntry::make('subject_type')->label(__('Subject'))->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-'),
                                TextEntry::make('parent_type')->label(__('Parent'))->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-'),
                                TextEntry::make('occurred_at')->label(__('Occurred'))->dateTime(),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'view' => ViewActivity::route('/{record}'),
        ];
    }
}
