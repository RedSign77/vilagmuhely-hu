<?php

namespace App\Filament\Admin\Pages;

use App\Models\WorldMapConfig;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MapConfiguration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'World Building';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.admin.pages.map-configuration';

    protected static ?string $title = 'Map Configuration';

    public ?array $data = [];

    public function mount(): void
    {
        $config = WorldMapConfig::getInstance();
        $this->form->fill($config->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Map Dimensions')
                    ->description('Configure the size of the world map')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('map_width')
                                    ->label('Map Width')
                                    ->numeric()
                                    ->required()
                                    ->minValue(50)
                                    ->maxValue(1000)
                                    ->default(200)
                                    ->suffix('units')
                                    ->helperText('Total width of the map in map units'),
                                TextInput::make('map_height')
                                    ->label('Map Height')
                                    ->numeric()
                                    ->required()
                                    ->minValue(50)
                                    ->maxValue(1000)
                                    ->default(200)
                                    ->suffix('units')
                                    ->helperText('Total height of the map in map units'),
                                TextInput::make('tile_size')
                                    ->label('Tile Size')
                                    ->numeric()
                                    ->required()
                                    ->minValue(32)
                                    ->maxValue(128)
                                    ->default(64)
                                    ->suffix('pixels')
                                    ->helperText('Size of each map unit in pixels'),
                            ]),
                    ]),

                Section::make('Generation Settings')
                    ->description('Default settings for world generation')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('default_biome')
                                    ->label('Default Biome')
                                    ->required()
                                    ->default('meadow')
                                    ->helperText('Primary biome type for the map'),
                                TextInput::make('generation_seed')
                                    ->label('Generation Seed')
                                    ->disabled()
                                    ->helperText('Current random seed (set during generation)'),
                            ]),
                    ]),

                Section::make('Statistics')
                    ->description('Current map information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_area')
                                    ->label('Total Map Area')
                                    ->disabled()
                                    ->formatStateUsing(fn () =>
                                        WorldMapConfig::getInstance()->map_width * WorldMapConfig::getInstance()->map_height . ' units²'
                                    ),
                                TextInput::make('last_regenerated_at')
                                    ->label('Last Regenerated')
                                    ->disabled()
                                    ->formatStateUsing(fn ($state) =>
                                        $state ? $state->format('Y-m-d H:i:s') : 'Never'
                                    ),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Configuration')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $config = WorldMapConfig::getInstance();
        $config->update([
            'map_width' => $data['map_width'],
            'map_height' => $data['map_height'],
            'tile_size' => $data['tile_size'],
            'default_biome' => $data['default_biome'],
        ]);

        Notification::make()
            ->success()
            ->title('Configuration saved')
            ->body('Map configuration has been updated successfully.')
            ->send();
    }
}
