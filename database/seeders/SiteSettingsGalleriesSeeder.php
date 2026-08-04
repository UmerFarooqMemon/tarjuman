<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Support\CatalogCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SiteSettingsGalleriesSeeder extends Seeder
{
    /**
     * @var array<string, array{source: string, column: string, prefix: string}>
     */
    protected array $galleries = [
        'accepted_by' => [
            'source' => 'assets/img/accepted-by',
            'column' => 'accepted_by_images',
            'prefix' => 'accepted-by-',
        ],
        'certified_by' => [
            'source' => 'assets/img/certified-by',
            'column' => 'certified_by_images',
            'prefix' => 'certified-by-',
        ],
        'regulated_by' => [
            'source' => 'assets/img/regulated-by',
            'column' => 'regulated_by_images',
            'prefix' => 'regulated-by-',
        ],
    ];

    /**
     * Copy default Accepted / Certified / Regulated images into uploads/front
     * and store filenames on the site_settings singleton.
     */
    public function run(): void
    {
        $settings = SiteSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'site_title' => config('app.name', 'Tarjuman'),
                'contact_email' => 'support@admin.com',
                'currency' => 'AED',
            ]
        );

        SiteSettingsTableSeeder::seedFooterLogosIfMissing($settings);
        SiteSettingsTableSeeder::seedFontsIfMissing($settings);
        $settings->refresh();

        $payload = [];

        foreach ($this->galleries as $gallery) {
            $this->deleteGalleryFiles($settings->galleryFilenames($gallery['column']));
            $payload[$gallery['column']] = $this->copyGalleryAssets(
                $gallery['source'],
                $gallery['prefix']
            );
        }

        $settings->update($payload);
        CatalogCache::flushSiteSettings();
    }

    /**
     * @param  list<string>  $filenames
     */
    protected function deleteGalleryFiles(array $filenames): void
    {
        $dir = uploadsDir('front');

        foreach ($filenames as $filename) {
            $path = $dir.$filename;
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * @return list<string>
     */
    protected function copyGalleryAssets(string $sourceRelative, string $prefix): array
    {
        $sourceAbsolute = public_path($sourceRelative);

        if (! File::isDirectory($sourceAbsolute)) {
            throw new \RuntimeException(
                "Default gallery assets folder is missing: public/{$sourceRelative}"
            );
        }

        $destinationRelative = uploadsDir('front');
        $destinationAbsolute = public_path($destinationRelative);
        File::ensureDirectoryExists($destinationAbsolute);

        $allowed = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'gif'];
        $copied = [];
        $stamp = time();

        $files = collect(File::files($sourceAbsolute))
            ->filter(function (\SplFileInfo $file) use ($allowed) {
                return in_array(strtolower($file->getExtension()), $allowed, true);
            })
            ->sortBy(fn (\SplFileInfo $file) => Str::lower($file->getFilename()))
            ->values();

        foreach ($files as $index => $file) {
            $extension = strtolower($file->getExtension());
            $slug = Str::slug(pathinfo($file->getFilename(), PATHINFO_FILENAME));
            $slug = $slug !== '' ? $slug : 'image';
            $filename = $prefix.$stamp.'-'.$index.'-'.$slug.'.'.$extension;

            File::copy($file->getPathname(), $destinationAbsolute.$filename);
            $copied[] = $filename;
        }

        return $copied;
    }
}
