<?php

namespace Tests\Feature;

use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UmkmOwnerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest cannot access owner dashboard.
     */
    public function test_guests_cannot_access_owner_dashboard(): void
    {
        $response = $this->get('/owner/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test a standard user cannot access owner dashboard.
     */
    public function test_non_owner_users_cannot_access_owner_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'tourist',
        ]);

        $response = $this->actingAs($user)->get('/owner/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Test an owner user can view the owner dashboard.
     */
    public function test_owner_can_access_owner_dashboard(): void
    {
        $owner = User::factory()->create([
            'role' => 'umkm_owner',
        ]);

        $response = $this->actingAs($owner)->get('/owner/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Toko Anda');
    }

    /**
     * Test owner can edit and update their profile.
     */
    public function test_owner_can_create_and_update_profile(): void
    {
        $owner = User::factory()->create([
            'role' => 'umkm_owner',
        ]);

        // Access profile page
        $response = $this->actingAs($owner)->get('/owner/profile');
        $response->assertStatus(200);

        // Post profile updates
        $response = $this->actingAs($owner)->put('/owner/profile', [
            'business_name' => 'Wayan Coffee & Craft',
            'description' => 'Fine authentic Balinese coffee.',
            'category' => 'culinary',
            'accepts_in_app_payment' => 1,
        ]);

        $response->assertRedirect('/owner/profile');
        $response->assertSessionHas('success', 'Informasi toko Anda berhasil diperbarui.');

        $this->assertDatabaseHas('umkm_profiles', [
            'user_id' => $owner->id,
            'business_name' => 'Wayan Coffee & Craft',
            'category' => 'culinary',
            'accepts_in_app_payment' => true,
        ]);
    }

    /**
     * Test owner can update their store location coordinates.
     */
    public function test_owner_can_set_and_update_map_location(): void
    {
        $owner = User::factory()->create([
            'role' => 'umkm_owner',
        ]);

        // Create profile first
        $profile = UmkmProfile::create([
            'user_id' => $owner->id,
            'owner_name' => $owner->name,
            'business_name' => 'Wayan Shop',
            'category' => 'craft',
            'slug' => 'wayan-shop',
            'ar_marker_id' => 'UMKM_TEST01',
        ]);

        // View location map page
        $response = $this->actingAs($owner)->get('/owner/location');
        $response->assertStatus(200);

        // Put coordinates updates
        $response = $this->actingAs($owner)->put('/owner/location', [
            'latitude' => -8.4217,
            'longitude' => 115.3590,
            'is_accessible' => 1,
            'accessibility_notes' => 'Ramp available.',
        ]);

        $response->assertRedirect('/owner/location');
        $response->assertSessionHas('success', 'Lokasi toko Anda berhasil diperbarui.');

        $this->assertDatabaseHas('map_locations', [
            'locationable_id' => $profile->id,
            'locationable_type' => UmkmProfile::class,
            'latitude' => -8.4217,
            'longitude' => 115.3590,
            'is_accessible' => true,
            'accessibility_notes' => 'Ramp available.',
        ]);
    }

    /**
     * Test owner can CRUD products in their store.
     */
    public function test_owner_can_perform_products_crud(): void
    {
        $owner = User::factory()->create([
            'role' => 'umkm_owner',
        ]);

        // Create profile
        $profile = UmkmProfile::create([
            'user_id' => $owner->id,
            'owner_name' => $owner->name,
            'business_name' => 'Wayan Shop',
            'category' => 'craft',
            'slug' => 'wayan-shop',
            'ar_marker_id' => 'UMKM_TEST01',
        ]);

        // Create category
        $category = UmkmProductCategory::create([
            'name' => 'Souvenir Khas',
            'slug' => 'souvenir-khas',
        ]);

        // Access product index
        $response = $this->actingAs($owner)->get('/owner/products');
        $response->assertStatus(200);

        // Store product
        $response = $this->actingAs($owner)->post('/owner/products', [
            'name' => 'Baju Barong Premium',
            'price' => 75000,
            'stock' => 10,
            'unit' => 'pcs',
            'description' => 'A fine Balinese barong shirt.',
            'umkm_product_category_id' => $category->id,
        ]);

        $response->assertRedirect('/owner/products');
        $response->assertSessionHas('success', 'Produk berhasil ditambahkan.');

        $product = UmkmProduct::first();
        $this->assertNotNull($product);
        $this->assertEquals('Baju Barong Premium', $product->name);
        $this->assertEquals(75000, $product->price);

        // Update product
        $response = $this->actingAs($owner)->put('/owner/products/'.$product->id, [
            'name' => 'Baju Barong Edisi Spesial',
            'price' => 85000,
            'stock' => 5,
            'unit' => 'pcs',
            'description' => 'Special limited edition.',
            'umkm_product_category_id' => $category->id,
            'is_active' => '1',
        ]);

        $response->assertRedirect('/owner/products');
        $response->assertSessionHas('success', 'Produk berhasil diperbarui.');
        $this->assertEquals('Baju Barong Edisi Spesial', $product->fresh()->name);

        // Delete product
        $response = $this->actingAs($owner)->delete('/owner/products/'.$product->id);
        $response->assertRedirect('/owner/products');
        $response->assertSessionHas('success', 'Produk berhasil dihapus.');
        $this->assertDatabaseMissing('umkm_products', ['id' => $product->id]);
    }

    /**
     * Test admin can CRUD product categories.
     */
    public function test_admin_can_manage_product_categories_crud(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Storage::fake('public');

        // View categories index
        $response = $this->actingAs($admin)->get('/admin/umkm/categories');
        $response->assertStatus(200);

        // Store category
        $image = UploadedFile::fake()->image('category.jpg');
        $response = $this->actingAs($admin)->post('/admin/umkm/categories', [
            'name' => 'Makanan Ringan',
            'description' => 'Aneka camilan khas Bali.',
            'icon' => 'fas fa-cookie',
            'image' => $image,
        ]);
        $response->assertRedirect('/admin/umkm/categories');
        $response->assertSessionHas('success', 'Kategori produk berhasil ditambahkan.');

        $category = UmkmProductCategory::first();
        $this->assertNotNull($category);
        $this->assertEquals('Makanan Ringan', $category->name);
        $this->assertEquals('Aneka camilan khas Bali.', $category->description);
        $this->assertEquals('fas fa-cookie', $category->icon);
        $this->assertNotNull($category->image_path);
        Storage::disk('public')->assertExists($category->image_path);

        // Update category
        $newImage = UploadedFile::fake()->image('new_category.jpg');
        $oldImagePath = $category->image_path;

        $response = $this->actingAs($admin)->put('/admin/umkm/categories/'.$category->id, [
            'name' => 'Jajanan Khas Bali',
            'description' => 'Jajanan basah tradisional.',
            'icon' => 'fas fa-cookie-bite',
            'image' => $newImage,
        ]);
        $response->assertRedirect('/admin/umkm/categories');
        $response->assertSessionHas('success', 'Kategori produk berhasil diperbarui.');

        $category = $category->fresh();
        $this->assertEquals('Jajanan Khas Bali', $category->name);
        $this->assertEquals('Jajanan basah tradisional.', $category->description);
        $this->assertEquals('fas fa-cookie-bite', $category->icon);
        Storage::disk('public')->assertExists($category->image_path);
        Storage::disk('public')->assertMissing($oldImagePath);

        // Delete category
        $deletedImagePath = $category->image_path;
        $response = $this->actingAs($admin)->delete('/admin/umkm/categories/'.$category->id);
        $response->assertRedirect('/admin/umkm/categories');
        $response->assertSessionHas('success', 'Kategori produk berhasil dihapus.');
        $this->assertDatabaseMissing('umkm_product_categories', ['id' => $category->id]);
        Storage::disk('public')->assertMissing($deletedImagePath);
    }

    /**
     * Test admin can manage owner accounts CRUD.
     */
    public function test_admin_can_manage_owner_accounts_crud(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // View owners index
        $response = $this->actingAs($admin)->get('/admin/umkm/owners');
        $response->assertStatus(200);

        // Store owner
        $response = $this->actingAs($admin)->post('/admin/umkm/owners', [
            'name' => 'Ketut Sukra',
            'email' => 'ketut@example.com',
            'phone' => '08123',
            'password' => 'secret-owner-pw',
        ]);

        $response->assertRedirect('/admin/umkm/owners');
        $response->assertSessionHas('success', 'Akun pemilik UMKM berhasil dibuat.');

        $owner = User::where('email', 'ketut@example.com')->first();
        $this->assertNotNull($owner);
        $this->assertEquals('umkm_owner', $owner->role);
        $this->assertTrue(Hash::check('secret-owner-pw', $owner->password));

        // Update owner
        $response = $this->actingAs($admin)->put('/admin/umkm/owners/'.$owner->id, [
            'name' => 'Ketut Sukra Update',
            'email' => 'ketut.new@example.com',
            'phone' => '08777',
            'password' => 'secret-owner-new-pw',
        ]);

        $response->assertRedirect('/admin/umkm/owners');
        $response->assertSessionHas('success', 'Akun pemilik UMKM berhasil diperbarui.');

        $owner = $owner->fresh();
        $this->assertEquals('Ketut Sukra Update', $owner->name);
        $this->assertEquals('ketut.new@example.com', $owner->email);
        $this->assertTrue(Hash::check('secret-owner-new-pw', $owner->password));

        // Delete owner
        $response = $this->actingAs($admin)->delete('/admin/umkm/owners/'.$owner->id);
        $response->assertRedirect('/admin/umkm/owners');
        $response->assertSessionHas('success', 'Akun pemilik UMKM berhasil dihapus.');
        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
    }
}
