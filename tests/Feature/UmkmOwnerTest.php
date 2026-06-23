<?php

namespace Tests\Feature;

use App\Models\MapLocation;
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
            'preferred_language' => 'en',
        ]);

        // Access profile page
        $response = $this->actingAs($owner)->get('/owner/profile');
        $response->assertStatus(200);

        // Post profile updates
        $response = $this->actingAs($owner)->put('/owner/profile', [
            'business_name' => ['en' => 'Wayan Coffee & Craft', 'id' => 'Wayan Coffee & Craft'],
            'description' => ['en' => 'Fine authentic Balinese coffee.', 'id' => 'Kopi asli Bali yang enak.'],
        ]);

        $response->assertRedirect('/owner/profile');
        $response->assertSessionHas('success', 'Your store information updated successfully.');

        $profile = UmkmProfile::where('user_id', $owner->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('Wayan Coffee & Craft', $profile->business_name);
    }

    /**
     * Test owner can update their store location coordinates.
     */
    public function test_owner_can_set_and_update_map_location(): void
    {
        $owner = User::factory()->create([
            'role' => 'umkm_owner',
            'preferred_language' => 'en',
        ]);

        // Create profile first
        $profile = UmkmProfile::create([
            'user_id' => $owner->id,
            'owner_name' => $owner->name,
            'business_name' => ['en' => 'Wayan Shop', 'id' => 'Wayan Shop'],
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
        $response->assertSessionHas('success', 'Your store location updated successfully.');

        $location = MapLocation::where('locationable_id', $profile->id)
            ->where('locationable_type', UmkmProfile::class)
            ->first();
        $this->assertNotNull($location);
        $this->assertEquals(-8.4217, $location->latitude);
        $this->assertEquals(115.3590, $location->longitude);
        $this->assertTrue((bool) $location->is_accessible);
        $this->assertEquals('Ramp available.', $location->accessibility_notes);
    }

    /**
     * Test owner can CRUD products in their store.
     */
    public function test_owner_can_perform_products_crud(): void
    {
        $owner = User::factory()->create([
            'role' => 'umkm_owner',
            'preferred_language' => 'en',
        ]);

        // Create profile
        $profile = UmkmProfile::create([
            'user_id' => $owner->id,
            'owner_name' => $owner->name,
            'business_name' => ['en' => 'Wayan Shop', 'id' => 'Wayan Shop'],
            'slug' => 'wayan-shop',
            'ar_marker_id' => 'UMKM_TEST01',
        ]);

        // Create category
        $category = UmkmProductCategory::create([
            'name' => ['en' => 'Typical Souvenir', 'id' => 'Souvenir Khas'],
            'slug' => 'souvenir-khas',
        ]);

        // Access product index
        $response = $this->actingAs($owner)->get('/owner/products');
        $response->assertStatus(200);

        // Store product
        $response = $this->actingAs($owner)->post('/owner/products', [
            'name' => ['en' => 'Premium Barong Shirt', 'id' => 'Baju Barong Premium'],
            'price' => 75000,
            'stock' => 10,
            'unit' => 'pcs',
            'description' => ['en' => 'A fine Balinese barong shirt.', 'id' => 'Kemeja barong Bali berkualitas.'],
            'umkm_product_category_id' => $category->id,
        ]);

        $response->assertRedirect('/owner/products');
        $response->assertSessionHas('success', 'Product added successfully.');

        $product = UmkmProduct::first();
        $this->assertNotNull($product);
        $this->assertEquals('Premium Barong Shirt', $product->name);
        $this->assertEquals(75000, $product->price);

        // Update product
        $response = $this->actingAs($owner)->put('/owner/products/'.$product->id, [
            'name' => ['en' => 'Special Edition Barong Shirt', 'id' => 'Baju Barong Edisi Spesial'],
            'price' => 85000,
            'stock' => 5,
            'unit' => 'pcs',
            'description' => ['en' => 'Special limited edition.', 'id' => 'Edisi terbatas spesial.'],
            'umkm_product_category_id' => $category->id,
            'is_active' => '1',
        ]);

        $response->assertRedirect('/owner/products');
        $response->assertSessionHas('success', 'Product updated successfully.');
        $this->assertEquals('Special Edition Barong Shirt', $product->fresh()->name);

        // Delete product
        $response = $this->actingAs($owner)->delete('/owner/products/'.$product->id);
        $response->assertRedirect('/owner/products');
        $response->assertSessionHas('success', 'Product deleted successfully.');
        $this->assertDatabaseMissing('umkm_products', ['id' => $product->id]);
    }

    /**
     * Test admin can CRUD product categories.
     */
    public function test_admin_can_manage_product_categories_crud(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'preferred_language' => 'en',
        ]);

        Storage::fake('public');

        // View categories index
        $response = $this->actingAs($admin)->get('/admin/umkm/categories');
        $response->assertStatus(200);

        // Store category
        $image = UploadedFile::fake()->image('category.jpg');
        $model3d = UploadedFile::fake()->create('model.glb', 1024, 'model/gltf-binary');
        $model3dUsdz = UploadedFile::fake()->create('model.usdz', 1024, 'model/vnd.usdz+zip');

        $response = $this->actingAs($admin)->post('/admin/umkm/categories', [
            'name' => ['en' => 'Light Snacks', 'id' => 'Makanan Ringan'],
            'description' => ['en' => 'Various Balinese snacks.', 'id' => 'Aneka camilan khas Bali.'],
            'image' => $image,
            'model_3d_file' => $model3d,
            'model_3d_usdz_file' => $model3dUsdz,
        ]);
        $response->assertRedirect('/admin/umkm/categories');
        $response->assertSessionHas('success', 'Product category added successfully.');

        $category = UmkmProductCategory::first();
        $this->assertNotNull($category);
        $this->assertEquals('Light Snacks', $category->name);
        $this->assertEquals('Various Balinese snacks.', $category->description);
        $this->assertNotNull($category->image_path);
        $this->assertNotNull($category->model_3d_path);
        $this->assertNotNull($category->model_3d_usdz_path);
        Storage::disk('public')->assertExists($category->image_path);
        Storage::disk('public')->assertExists($category->model_3d_path);
        Storage::disk('public')->assertExists($category->model_3d_usdz_path);

        // Update category
        $newImage = UploadedFile::fake()->image('new_category.jpg');
        $newModel3d = UploadedFile::fake()->create('new_model.glb', 1024, 'model/gltf-binary');
        $newModel3dUsdz = UploadedFile::fake()->create('new_model.usdz', 1024, 'model/vnd.usdz+zip');
        $oldImagePath = $category->image_path;
        $oldModelPath = $category->model_3d_path;
        $oldModelUsdzPath = $category->model_3d_usdz_path;

        $response = $this->actingAs($admin)->put('/admin/umkm/categories/'.$category->id, [
            'name' => ['en' => 'Balinese Traditional Snacks', 'id' => 'Jajanan Khas Bali'],
            'description' => ['en' => 'Traditional wet snacks.', 'id' => 'Jajanan basah tradisional.'],
            'image' => $newImage,
            'model_3d_file' => $newModel3d,
            'model_3d_usdz_file' => $newModel3dUsdz,
        ]);
        $response->assertRedirect('/admin/umkm/categories');
        $response->assertSessionHas('success', 'Product category updated successfully.');

        $category = $category->fresh();
        $this->assertEquals('Balinese Traditional Snacks', $category->name);
        $this->assertEquals('Traditional wet snacks.', $category->description);
        Storage::disk('public')->assertExists($category->image_path);
        Storage::disk('public')->assertExists($category->model_3d_path);
        Storage::disk('public')->assertExists($category->model_3d_usdz_path);
        Storage::disk('public')->assertMissing($oldImagePath);
        Storage::disk('public')->assertMissing($oldModelPath);
        Storage::disk('public')->assertMissing($oldModelUsdzPath);

        // Delete category
        $deletedImagePath = $category->image_path;
        $deletedModelPath = $category->model_3d_path;
        $deletedModelUsdzPath = $category->model_3d_usdz_path;
        $response = $this->actingAs($admin)->delete('/admin/umkm/categories/'.$category->id);
        $response->assertRedirect('/admin/umkm/categories');
        $response->assertSessionHas('success', 'Product category deleted successfully.');
        $this->assertDatabaseMissing('umkm_product_categories', ['id' => $category->id]);
        Storage::disk('public')->assertMissing($deletedImagePath);
        Storage::disk('public')->assertMissing($deletedModelPath);
        Storage::disk('public')->assertMissing($deletedModelUsdzPath);
    }

    /**
     * Test admin can manage owner accounts CRUD.
     */
    public function test_admin_can_manage_owner_accounts_crud(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'preferred_language' => 'en',
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
        $response->assertSessionHas('success', 'UMKM owner account created successfully.');

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
        $response->assertSessionHas('success', 'UMKM owner account updated successfully.');

        $owner = $owner->fresh();
        $this->assertEquals('Ketut Sukra Update', $owner->name);
        $this->assertEquals('ketut.new@example.com', $owner->email);
        $this->assertTrue(Hash::check('secret-owner-new-pw', $owner->password));

        // Delete owner
        $response = $this->actingAs($admin)->delete('/admin/umkm/owners/'.$owner->id);
        $response->assertRedirect('/admin/umkm/owners');
        $response->assertSessionHas('success', 'UMKM owner account deleted successfully.');
        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
    }
}
