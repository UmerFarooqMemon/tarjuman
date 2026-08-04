<?php

namespace Tests\Feature\Api;

use App\Models\CmsPage;
use App\Models\CmsSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPagesApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['api.token' => 'test-api-token']);
    }

    public function test_published_home_page_is_returned(): void
    {
        $page = CmsPage::query()->create([
            'slug' => 'home',
            'title' => 'Home',
            'preview_path' => '/',
            'is_published' => true,
        ]);

        CmsSection::query()->create([
            'cms_page_id' => $page->id,
            'type' => 'stats_trust',
            'sort_order' => 1,
            'is_enabled' => true,
            'content' => [
                'heading' => ['en' => 'Trusted', 'ar' => 'موثوق'],
                'stats' => [],
            ],
        ]);

        $response = $this->withHeader('Authorization', 'Bearer test-api-token')
            ->getJson('/api/cms/pages/home');

        $response->assertOk()
            ->assertJsonPath('slug', 'home')
            ->assertJsonPath('sections.0.type', 'stats_trust')
            ->assertJsonPath('sections.0.content.heading.en', 'Trusted');
    }

    public function test_unpublished_page_returns_404(): void
    {
        CmsPage::query()->create([
            'slug' => 'home',
            'title' => 'Home',
            'preview_path' => '/',
            'is_published' => false,
        ]);

        $this->withHeader('Authorization', 'Bearer test-api-token')
            ->getJson('/api/cms/pages/home')
            ->assertNotFound();
    }
}
