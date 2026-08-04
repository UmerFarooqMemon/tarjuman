<?php

namespace Database\Seeders;

use App\Cms\SchemaRegistry;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Support\CmsCache;
use Illuminate\Database\Seeder;

class CmsHomePageSeeder extends Seeder
{
    public function run(): void
    {
        $page = CmsPage::query()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home',
                'preview_path' => '/',
                'is_published' => true,
            ]
        );

        $order = 1;
        foreach (['stats_trust', 'supported_documents', 'why_choose_us'] as $type) {
            $schema = SchemaRegistry::get($type);

            CmsSection::query()->updateOrCreate(
                [
                    'cms_page_id' => $page->id,
                    'type' => $type,
                ],
                [
                    'sort_order' => $order++,
                    'is_enabled' => true,
                    'content' => $schema->defaults(),
                ]
            );
        }

        CmsCache::flushPage('home');
    }
}
