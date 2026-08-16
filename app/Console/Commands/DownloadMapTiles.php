<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Fills public/tiles/{z}/{x}/{y}.png so the Leaflet map works with no internet.
 *
 *   php artisan giya:tiles --dry-run     see the count and rough size first
 *   php artisan giya:tiles               download Metro Cebu, zoom 11-16
 *
 * Keep the box small. Bulk downloading is against the OpenStreetMap tile usage
 * policy; Metro Cebu at these zooms is fine, a whole region is not.
 */
class DownloadMapTiles extends Command
{
    protected $signature = 'giya:tiles
        {--min-zoom=11} {--max-zoom=16}
        {--south=10.10} {--west=123.70} {--north=10.55} {--east=124.10}
        {--force : Re-download tiles that already exist}
        {--dry-run : Only report how many tiles would be fetched}';

    protected $description = 'Download map tiles for Metro Cebu into public/tiles for offline use';

    public function handle(): int
    {
        $south = (float) $this->option('south');
        $west  = (float) $this->option('west');
        $north = (float) $this->option('north');
        $east  = (float) $this->option('east');
        $min   = (int) $this->option('min-zoom');
        $max   = (int) $this->option('max-zoom');

        $root = public_path('tiles');
        File::ensureDirectoryExists($root);

        $jobs = [];
        for ($z = $min; $z <= $max; $z++) {
            [$x1, $y2] = $this->toTile($south, $west, $z);
            [$x2, $y1] = $this->toTile($north, $east, $z);

            for ($x = min($x1, $x2); $x <= max($x1, $x2); $x++) {
                for ($y = min($y1, $y2); $y <= max($y1, $y2); $y++) {
                    $jobs[] = [$z, $x, $y];
                }
            }
        }

        $this->info(sprintf('Zoom %d-%d over Metro Cebu: %s tiles, roughly %s MB.',
            $min, $max, number_format(count($jobs)), number_format(count($jobs) * 18 / 1024, 1)));

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        if (count($jobs) > 20000 && ! $this->confirm('That is a lot of tiles. Continue?')) {
            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar(count($jobs));
        $bar->start();

        $got = $skip = $fail = 0;

        foreach ($jobs as [$z, $x, $y]) {
            $path = "{$root}/{$z}/{$x}/{$y}.png";

            if (! $this->option('force') && File::exists($path)) {
                $skip++;
                $bar->advance();
                continue;
            }

            try {
                $res = Http::withHeaders([
                    'User-Agent' => 'GIYA-Capstone/1.0 (University of Cebu; offline pilgrimage map)',
                ])->timeout(20)->get("https://tile.openstreetmap.org/{$z}/{$x}/{$y}.png");

                if ($res->successful()) {
                    File::ensureDirectoryExists(dirname($path));
                    File::put($path, $res->body());
                    $got++;
                } else {
                    $fail++;
                }
            } catch (\Throwable $e) {
                $fail++;
            }

            $bar->advance();
            usleep(120000);   // ~8 per second, polite to the tile server
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Downloaded {$got}, already present {$skip}, failed {$fail}.");
        $this->line('The map now works with the network unplugged.');

        return self::SUCCESS;
    }

    /** Slippy-map tile numbering. */
    protected function toTile(float $lat, float $lng, int $zoom): array
    {
        $n = 2 ** $zoom;
        $x = (int) floor((($lng + 180) / 360) * $n);
        $r = deg2rad($lat);

        return [$x, (int) floor((1 - log(tan($r) + 1 / cos($r)) / M_PI) / 2 * $n)];
    }
}
