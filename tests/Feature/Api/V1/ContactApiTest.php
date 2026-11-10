<?php

namespace Tests\Feature\Api\V1;
use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_お問合せ一覧を取得できる()
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@example.com',
            'telephone' => '09012345678',
            'address' => '東京都渋谷区',
            'gender' => 1,
            'category_id' => $category->id,
            'detail' => 'テスト内容です',
        ]);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);
    }

    public function test_お問合せ一覧のバリデーションエラー時に422が返る()
    {
        $response = $this->getJson('/api/v1/contacts?per_page=invalid_number');

        $response->assertStatus(422);
    }

    public function test_お問合せ詳細を取得できる()
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@example.com',
            'telephone' => '09012345678',
            'address' => '東京都渋谷区',
            'gender' => 1,
            'category_id' => $category->id,
            'detail' => 'テスト内容です',
        ]);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $contact->id,
                'email' => 'yamada@example.com',
            ]);
    }

    public function test_存在しないお問合せ詳細を指定した場合は404が返る()
    {
        $response = $this->getJson('/api/v1/contacts/99999');

        $response->assertStatus(404);
    }

    public function test_お問合せを作成できる()
    {

        $category = Category::create(['content' => 'テストカテゴリ']);

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル101',
            'gender' => 1,
            'category_id' => $category->id,
            'detail' => '新規お問い合わせの内容です。',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('contacts', [
            'email' => 'yamada@example.com',
            'first_name' => '太郎',
        ]);
    }

    public function test_お問合せ作成のバリデーションエラー時に422が返る()
    {
        $response = $this->postJson('/api/v1/contacts', []);

        $response->assertStatus(422);
    }

    //更新できる

    public function test_お問合せを更新できる()
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@example.com',
            'telephone' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル101',
            'gender' => 1,
            'category_id' => $category->id,
            'detail' => '更新前の内容です。',
        ]);
        //更新用
        $updateData = [
            'first_name' => '次郎',
            'last_name' => '鈴木',
            'email' => 'suzuki@example.com',
            'tel' => '09098765432',
            'address' => '大阪府大阪市',
            'building' => '大阪ビル202',
            'gender' => 2,
            'category_id' => $category->id,
            'detail' => '更新後の内容です。',
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '次郎',
            'email' => 'suzuki@example.com',
        ]);
    }

    public function test_存在しないお問合せを更新しようとした場合は404が返る()
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $response = $this->putJson('/api/v1/contacts/99999', [
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'gender' => 1,
            'category_id' => 1,
            'detail' => 'テスト',
        ]);

        $response->assertStatus(404);
    }

    public function test_お問合せ更新のバリデーションエラー時に422が返る()
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@example.com',
            'telephone' => '09012345678',
            'address' => '東京都渋谷区',
            'gender' => 1,
            'category_id' => $category->id,
            'detail' => '内容です。',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", []);

        $response->assertStatus(422);
    }

    public function test_お問合せを削除できる()
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $contact = Contact::create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@example.com',
            'telephone' => '09012345678',
            'address' => '東京都渋谷区',
            'gender' => 1,
            'category_id' => $category->id,
            'detail' => '削除テストの内容です。',
        ]);

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_存在しないお問合せを削除しようとした場合は404が返る()
    {
        $response = $this->deleteJson('/api/v1/contacts/99999');

        $response->assertStatus(404);
    }
}