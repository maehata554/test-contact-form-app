<?php

namespace Tests\Feature;
use App\Models\User;
use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactCsvExportTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_ログイン済み管理者がフィルタ条件付きでCSVをダウンロードできる()
    {
        $admin = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact1 = Contact::create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@example.com',
            'telephone' => '09012345678',
            'address' => '東京都',
            'gender' => 1,
            'category_id' => $category->id,
            'detail' => '詳細1',
        ]);

        $contact2 = Contact::create([
            'first_name' => '花子',
            'last_name' => '鈴木',
            'email' => 'suzuki@example.com',
            'telephone' => '09087654321',
            'address' => '大阪府',
            'gender' => 2,
            'category_id' => $category->id,
            'detail' => '詳細2',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.contacts.export', ['gender' => 1]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('yamada@example.com', $content);
        $this->assertStringNotContainsString('suzuki@example.com', $content);
    }

    public function test_フィルタ条件無指定時は新着順で出力される()
    {
        $admin = User::factory()->create();
        $category = Category::create(['content' => 'テストカテゴリ']);

        $oldContact = Contact::create([
            'first_name' => '古い太郎',
            'last_name' => '山田',
            'email' => 'old@example.com',
            'telephone' => '09011111111',
            'address' => '東京都',
            'gender' => 1,
            'category_id' => $category->id,
            'detail' => '古いデータ',
            'created_at' => now()->subDay(),
        ]);

        $newContact = Contact::create([
            'first_name' => '新しい次郎',
            'last_name' => '鈴木',
            'email' => 'new@example.com',
            'telephone' => '09022222222',
            'address' => '大阪府',
            'gender' => 1,
            'category_id' => $category->id,
            'detail' => '新しいデータ',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.contacts.export'));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $newPosition = strpos($content, 'new@example.com');
        $oldPosition = strpos($content, 'old@example.com');

        $this->assertNotFalse($newPosition, '新しいデータがCSVに見つかりません');
        $this->assertNotFalse($oldPosition, '古いデータがCSVに見つかりません');
        $this->assertLessThan($oldPosition, $newPosition, '新着データが出力されていません');
    }
}
