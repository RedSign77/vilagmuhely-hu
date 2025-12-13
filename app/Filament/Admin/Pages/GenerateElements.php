<?php

namespace App\Filament\Admin\Pages;

use App\Models\WorldElementInstance;
use App\Models\WorldMapConfig;
use App\Services\WorldGenerationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;

class GenerateElements extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'World Building';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.admin.pages.generate-elements';

    protected static ?string $title = 'Generate Elements';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'regenerate' => false,
            'density' => 'medium',
            'biome' => null,
            'seed' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Generation Options')
                    ->description('Configure how elements should be generated')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('density')
                                    ->label('Density Level')
                                    ->options([
                                        'low' => 'Low (50% fewer elements)',
                                        'medium' => 'Medium (Normal)',
                                        'high' => 'High (50% more elements)',
                                    ])
                                    ->default('medium')
                                    ->required()
                                    ->native(false)
                                    ->helperText('Controls how many elements are placed on the map'),
                                Select::make('biome')
                                    ->label('Specific Biome')
                                    ->options([
                                        'forest' => 'Forest',
                                        'meadow' => 'Meadow',
                                        'desert' => 'Desert',
                                        'tundra' => 'Tundra',
                                        'swamp' => 'Swamp',
                                    ])
                                    ->placeholder('All Biomes')
                                    ->native(false)
                                    ->helperText('Generate elements only in a specific biome (leave empty for all)'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('seed')
                                    ->label('Random Seed')
                                    ->placeholder('Auto-generated')
                                    ->helperText('Use a specific seed for reproducible generation'),
                                Checkbox::make('regenerate')
                                    ->label('Clear existing elements')
                                    ->helperText('WARNING: This will delete all current elements!')
                                    ->default(false),
                            ]),
                    ]),

                Section::make('Current Statistics')
                    ->description('Overview of currently placed elements')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('total_elements')
                                    ->label('Total Elements')
                                    ->default(fn () => WorldElementInstance::count())
                                    ->disabled(),
                                TextInput::make('map_size')
                                    ->label('Map Size')
                                    ->default(function () {
                                        $config = WorldMapConfig::getInstance();
                                        return "{$config->map_width} × {$config->map_height}";
                                    })
                                    ->disabled(),
                                TextInput::make('last_generated')
                                    ->label('Last Generated')
                                    ->default(fn () =>
                                        WorldMapConfig::getInstance()->last_regenerated_at?->diffForHumans() ?? 'Never'
                                    )
                                    ->disabled(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate Elements')
                ->icon('heroicon-o-sparkles')
                ->requiresConfirmation(fn (array $data) => $data['regenerate'] ?? false)
                ->modalHeading('Confirm Generation')
                ->modalDescription(function (array $data) {
                    if ($data['regenerate'] ?? false) {
                        $count = WorldElementInstance::count();
                        return "This will DELETE {$count} existing elements and generate new ones. Are you sure?";
                    }
                    return 'This will add new elements to the map. Continue?';
                })
                ->modalSubmitActionLabel('Generate')
                ->action('generate'),
        ];
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        try {
            $generationService = app(WorldGenerationService::class);

            $result = $generationService->generateElements([
                'regenerate' => $data['regenerate'] ?? false,
                'density' => $data['density'] ?? 'medium',
                'biome' => $data['biome'] ?? null,
                'seed' => $data['seed'] ?? null,
            ]);

            if ($result['success']) {
                Notification::make()
                    ->success()
                    ->title('Elements Generated')
                    ->body($result['message'])
                    ->send();

                // Refresh statistics
                $this->form->fill([
                    'regenerate' => false,
                    'density' => 'medium',
                    'biome' => null,
                    'seed' => null,
                ]);
            } else {
                Notification::make()
                    ->danger()
                    ->title('Generation Failed')
                    ->body($result['message'] ?? 'An error occurred during generation')
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Generation Error')
                ->body($e->getMessage())
                ->send();

            throw new Halt();
        }
    }
}
