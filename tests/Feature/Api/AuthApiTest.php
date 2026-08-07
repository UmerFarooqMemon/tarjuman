<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['api.token' => 'test-api-token']);
    }

    #[Test]
    public function it_registers_an_individual_user(): void
    {
        $response = $this->withHeader('X-API-Token', 'test-api-token')
            ->postJson('/api/auth/register/individual', [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane@example.com',
                'phone' => '+971500000000',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.type', User::TYPE_INDIVIDUAL)
            ->assertJsonPath('data.user.email', 'jane@example.com')
            ->assertJsonPath('data.user.first_name', 'Jane')
            ->assertJsonPath('data.user.last_name', 'Doe')
            ->assertJsonPath('data.user.phone', '+971500000000')
            ->assertJsonMissingPath('data.user.company_name')
            ->assertJsonMissingPath('data.user.expected_volume')
            ->assertJsonStructure([
                'data' => ['token', 'token_type', 'expires_in', 'user'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'type' => User::TYPE_INDIVIDUAL,
        ]);
    }

    #[Test]
    public function it_rejects_duplicate_phone_on_register(): void
    {
        User::factory()->create([
            'email' => 'taken@example.com',
            'phone' => '+971500000000',
        ]);

        $this->withHeader('X-API-Token', 'test-api-token')
            ->postJson('/api/auth/register/individual', [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane@example.com',
                'phone' => '+971500000000',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);

        $this->withHeader('X-API-Token', 'test-api-token')
            ->postJson('/api/auth/register/enterprise', [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john@company.com',
                'phone' => '+971500000000',
                'company_name' => 'Acme Co',
                'expected_volume' => '1-50',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    #[Test]
    public function it_registers_an_enterprise_user(): void
    {
        $response = $this->withHeader('X-API-Token', 'test-api-token')
            ->postJson('/api/auth/register/enterprise', [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john@company.com',
                'phone' => '+971511111111',
                'company_name' => 'Acme Co',
                'expected_volume' => '1-50',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.type', User::TYPE_ENTERPRISE)
            ->assertJsonPath('data.user.company_name', 'Acme Co')
            ->assertJsonPath('data.user.expected_volume', '1-50');
    }

    #[Test]
    public function it_registers_enterprise_with_camel_case_volume(): void
    {
        $response = $this->withHeader('X-API-Token', 'test-api-token')
            ->postJson('/api/auth/register/enterprise', [
                'firstName' => 'John',
                'lastName' => 'Smith',
                'email' => 'camel@company.com',
                'phone' => '+971522222222',
                'companyName' => 'Acme Co',
                'expectedVolume' => '1-50',
                'password' => 'Password1!',
                'passwordConfirmation' => 'Password1!',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.type', User::TYPE_ENTERPRISE)
            ->assertJsonPath('data.user.expected_volume', '1-50')
            ->assertJsonPath('data.user.company_name', 'Acme Co');
    }

    #[Test]
    public function shared_login_returns_user_type(): void
    {
        User::factory()->create([
            'type' => User::TYPE_INDIVIDUAL,
            'email' => 'jane@example.com',
            'password' => 'Password1!',
            'is_active' => true,
        ]);

        $individual = $this->withHeader('X-API-Token', 'test-api-token')
            ->postJson('/api/auth/login', [
                'email' => 'jane@example.com',
                'password' => 'Password1!',
            ]);

        $individual->assertOk()
            ->assertJsonPath('data.user.type', User::TYPE_INDIVIDUAL)
            ->assertJsonMissingPath('data.user.company_name');

        User::factory()->enterprise()->create([
            'email' => 'corp@example.com',
            'password' => 'Password1!',
            'company_name' => 'Corp Ltd',
            'expected_volume' => '51-100',
            'is_active' => true,
        ]);

        $enterprise = $this->withHeader('X-API-Token', 'test-api-token')
            ->postJson('/api/auth/login', [
                'email' => 'corp@example.com',
                'password' => 'Password1!',
            ]);

        $enterprise->assertOk()
            ->assertJsonPath('data.user.type', User::TYPE_ENTERPRISE)
            ->assertJsonPath('data.user.company_name', 'Corp Ltd')
            ->assertJsonPath('data.user.expected_volume', '51-100');
    }
}
