<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ElementTypeResource\Pages;
use App\Models\WorldElementType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ElementTypeResource extends Resource
{
    protected static ?string $model = WorldElementType::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'World Building';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Element Type';

    protected static ?string $pluralModelLabel = 'Element Types';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            ),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash']),
                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'vegetation' => 'Vegetation',
                                'water' => 'Water',
                                'terrain' => 'Terrain',
                                'structure' => 'Structure',
                                'decoration' => 'Decoration',
                            ])
                            ->native(false),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Visual Settings')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Element Image')
                            ->image()
                            ->imageEditor()
                            ->maxSize(512)
                            ->directory('world-elements')
                            ->visibility('public')
                            ->helperText('Upload an image (max 128×128 pixels recommended)')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('max_width')
                            ->label('Max Width (px)')
                            ->numeric()
                            ->default(128)
                            ->minValue(16)
                            ->maxValue(256)
                            ->required(),
                        Forms\Components\TextInput::make('max_height')
                            ->label('Max Height (px)')
                            ->numeric()
                            ->default(128)
                            ->minValue(16)
                            ->maxValue(256)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Generation Settings')
                    ->schema([
                        Forms\Components\TextInput::make('density_weight')
                            ->label('Density Weight')
                            ->numeric()
                            ->default(1.00)
                            ->step(0.01)
                            ->minValue(0.01)
                            ->maxValue(10.00)
                            ->required()
                            ->helperText('Higher values = more frequent spawning (0.01-10.00)'),
                        Forms\Components\Select::make('rarity')
                            ->required()
                            ->options([
                                'common' => 'Common',
                                'uncommon' => 'Uncommon',
                                'rare' => 'Rare',
                                'epic' => 'Epic',
                                'legendary' => 'Legendary',
                            ])
                            ->default('common')
                            ->native(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive elements will not spawn during generation'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Resource Bonuses')
                    ->schema([
                        Forms\Components\KeyValue::make('resource_bonus.resources')
                            ->label('Resource Rewards')
                            ->keyLabel('Resource Type')
                            ->valueLabel('Amount')
                            ->addButtonLabel('Add Resource')
                            ->reorderable(false)
                            ->helperText('Add resource rewards for interacting with this element')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('resource_bonus.bonus_type')
                            ->label('Bonus Type')
                            ->options([
                                'one_time' => 'One Time',
                                'repeating' => 'Repeating',
                            ])
                            ->default('one_time')
                            ->native(false)
                            ->helperText('One time bonuses can only be claimed once, repeating bonuses have a cooldown'),
                        Forms\Components\TextInput::make('resource_bonus.cooldown_hours')
                            ->label('Cooldown (Hours)')
                            ->numeric()
                            ->default(24)
                            ->minValue(1)
                            ->visible(fn (Forms\Get $get) => $get('resource_bonus.bonus_type') === 'repeating')
                            ->helperText('Hours between bonus claims for repeating bonuses'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->size(60)
                    ->defaultImageUrl(url('/images/placeholder.png')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
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
                Tables\Columns\TextColumn::make('rarity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'common' => 'gray',
                        'uncommon' => 'success',
                        'rare' => 'info',
                        'epic' => 'warning',
                        'legendary' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('density_weight')
                    ->label('Density')
                    ->numeric(2)
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
                Tables\Columns\TextColumn::make('instances_count')
                    ->label('Instances')
                    ->counts('instances')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'vegetation' => 'Vegetation',
                        'water' => 'Water',
                        'terrain' => 'Terrain',
                        'structure' => 'Structure',
                        'decoration' => 'Decoration',
                    ]),
                Tables\Filters\SelectFilter::make('rarity')
                    ->options([
                        'common' => 'Common',
                        'uncommon' => 'Uncommon',
                        'rare' => 'Rare',
                        'epic' => 'Epic',
                        'legendary' => 'Legendary',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->excludeAttributes(['slug'])
                    ->beforeReplicaSaved(function (WorldElementType $replica): void {
                        $replica->slug = $replica->slug . '-copy';
                        $replica->name = $replica->name . ' (Copy)';
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
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
            'index' => Pages\ListElementTypes::route('/'),
            'create' => Pages\CreateElementType::route('/create'),
            'view' => Pages\ViewElementType::route('/{record}'),
            'edit' => Pages\EditElementType::route('/{record}/edit'),
        ];
    }
}
