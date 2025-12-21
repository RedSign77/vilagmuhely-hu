<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap for the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create()
            // Static pages
            ->add(Url::create('/')
                ->setLastModificationDate(now())
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/library')
                ->setLastModificationDate(now())
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/crystals')
                ->setLastModificationDate(now())
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/changelog')
                ->setLastModificationDate(now())
                ->setPriority(0.7)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))
            // Dynamic crystal pages using Sitemapable interface
            ->add(User::has('crystalMetric')->get());

        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        // Clear sitemap cache
        cache()->forget('sitemap');

        $this->info("Sitemap generated successfully at: {$path}");

        return Command::SUCCESS;
    }
}
