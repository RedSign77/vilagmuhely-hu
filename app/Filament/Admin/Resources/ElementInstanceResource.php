<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ElementInstanceResource\Pages;
use App\Models\WorldElementInstance;
use App\Models\WorldElementType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ElementInstanceResource extends Resource
{
    protected static ?string $model = WorldElementInstance::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'World Building';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Element Instance';

    protected static ?string $pluralModelLabel = 'Element Instances';

    public static function form(Form $form): Form
    {
        // Read-only view form
        return $form
            ->schema([
                Forms\Components\Section::make('Element Information')
                    ->schema([
                        Forms\Components\TextInput::make('type.name')
                            ->label('Element Type')
                            ->disabled(),
                        Forms\Components\TextInput::make('type.category')
                            ->label('Category')
                            ->disabled(),
                        Forms\Components\TextInput::make('biome')
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Position & Appearance')
                    ->schema([
                        Forms\Components\TextInput::make('position_x')
                            ->label('Position X')
                            ->disabled(),
                        Forms\Components\TextInput::make('position_y')
                            ->label('Position Y')
                            ->disabled(),
                        Forms\Components\TextInput::make('rotation')
                            ->label('Rotation (degrees)')
                            ->disabled(),
                        Forms\Components\TextInput::make('scale')
                            ->disabled(),
                        Forms\Components\TextInput::make('variant')
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Interaction Stats')
                    ->schema([
                        Forms\Components\Toggle::make('is_interactable')
                            ->label('Interactable')
                            ->disabled(),
                        Forms\Components\TextInput::make('interaction_count')
                            ->label('Interactions')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('type.image_path')
                    ->label('Image')
                    ->size(50)
                    ->defaultImageUrl(url('/images/placeholder.png')),
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Element Type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type.category')
                    ->label('Category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vegetation' => 'success',
                        'water' => 'info',
                        'terrain' => 'warning',
                        'structure' => 'danger',
                        'decoration' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->formatStateUsing(fn (WorldElementInstance $record): string =>
                        "({$record->position_x}, {$record->position_y})"
                    )
                    ->searchable(query: function ($query, $search) {
                        $coords = explode(',', str_replace(['(', ')', ' '], '', $search));
                        if (count($coords) === 2) {
                            $query->where('position_x', (int)$coords[0])
                                  ->where('position_y', (int)$coords[1]);
                        }
                    }),
                Tables\Columns\TextColumn::make('biome')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'forest' => 'success',
                        'meadow' => 'warning',
                        'desert' => 'danger',
                        'tundra' => 'info',
                        'swamp' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'Unknown')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_interactable')
                    ->label('Interactable')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('interaction_count')
                    ->label('Interactions')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Placed At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('world_element_type_id')
                    ->label('Element Type')
                    ->relationship('type', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('biome')
                    ->options([
                        'forest' => 'Forest',
                        'meadow' => 'Meadow',
                        'desert' => 'Desert',
                        'tundra' => 'Tundra',
                        'swamp' => 'Swamp',
                    ]),
                Tables\Filters\TernaryFilter::make('is_interactable')
                    ->label('Interactable')
                    ->placeholder('All')
                    ->trueLabel('Interactable only')
                    ->falseLabel('Non-interactable only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // No edit action - instances are generated, not manually edited
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s'); // Auto-refresh every 10 seconds
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
            'index' => Pages\ListElementInstances::route('/'),
            'view' => Pages\ViewElementInstance::route('/{record}'),
            // No create/edit pages - instances are generated programmatically
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Instances are created via generation, not manually
    }
}
