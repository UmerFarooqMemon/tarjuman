<?php

namespace Tests\Feature\Vendor;

use App\Models\Vendor;
use App\Models\VendorUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VendorAuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_page_is_reachable(): void
    {
        $this->get(route('vendor.auth.login'))
            ->assertOk();
    }

    #[Test]
    public function vendor_can_login_and_reach_dashboard(): void
    {
        $vendor = $this->makeVendor([
            'slug' => 'acme-translations',
            'email' => 'vendor@example.com',
            'is_approved' => true,
            'approved_at' => now(),
        ], 'Acme Translations', 'Acme');

        VendorUser::create([
            'vendor_id' => $vendor->id,
            'first_name' => 'Owner',
            'last_name' => 'User',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_owner' => true,
        ]);

        $this->post(route('vendor.auth.login'), [
            'email' => 'owner@example.com',
            'password' => 'password',
        ])->assertRedirect(route('vendor.dashboard.index'));

        $this->get(route('vendor.dashboard.index'))
            ->assertOk()
            ->assertSee('Welcome, Owner User');
    }

    #[Test]
    public function unapproved_vendor_cannot_login(): void
    {
        $vendor = $this->makeVendor([
            'slug' => 'pending-vendor',
            'email' => 'pending@example.com',
            'is_approved' => false,
            'approved_at' => null,
        ], 'Pending Co', 'Pending');

        VendorUser::create([
            'vendor_id' => $vendor->id,
            'first_name' => 'Pending',
            'last_name' => 'Owner',
            'email' => 'pending-owner@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_owner' => true,
        ]);

        $this->from(route('vendor.auth.login'))
            ->post(route('vendor.auth.login'), [
                'email' => 'pending-owner@example.com',
                'password' => 'password',
            ])
            ->assertRedirect(route('vendor.auth.login'))
            ->assertSessionHasErrors('email');
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    protected function makeVendor(array $attrs, string $legalName, string $businessName): Vendor
    {
        $vendor = Vendor::create(array_merge([
            'trn' => '100'.random_int(100000000, 999999999),
            'trade_license_no' => 'TL-'.random_int(1000, 9999),
            'moj_registration_no' => 'MOJ-'.random_int(1000, 9999),
            'phone' => '0500000000',
            'is_active' => true,
        ], $attrs));

        $vendor->translateOrNew('en')->legal_name = $legalName;
        $vendor->translateOrNew('en')->business_name = $businessName;
        $vendor->translateOrNew('ar')->legal_name = $legalName;
        $vendor->translateOrNew('ar')->business_name = $businessName;
        $vendor->save();

        return $vendor;
    }
}
