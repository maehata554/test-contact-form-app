<?php

namespace Tests\Feature;
use App\Models\User;
use App\Models\Category;
use App\Models\Contact;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;/**
      * A basic feature test example.
      */
    public function test_認証されたユーザーは管理ダッシュボードを表示できる(): void
    {

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_未認証ユーザーはログイン画面にリダイレクトされる(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }
    public function test_管理者画面でキーワード性別カテゴリ日付フィルタが機能し7件ごとにページネーションされる(): void
    {
        $admin = User::factory()->create();

        $categoryA = Category::create(['content' => 'システムについて']);
        $categoryB = Category::create(['content' => '教材について']);

        for ($i = 1; $i <= 9; $i++) {
            Contact::create([
                'category_id' => $categoryA->id,
                'first_name'  => '山田',
                'last_name'   => '次郎' . $i,
                'gender'      => 1,
                'email'       => "taro{$i}@example.com",
                'telephone'   => '090111122' . $i,
                'address'     => '大阪府',
                'detail'      => 'テストに関するお問い合わせです ' . $i,
                'created_at'  => now(),
            ]);
        }

        //検索時
        $today = now()->toDateString();
        $response = $this->actingAs($admin)->get('/admin?' . http_build_query([
            'keyword'     => '山田',
            'gender'      => '1',
            'category_id' => $categoryA->id,
            'date'        => $today,
        ]));

        $response->assertStatus(200);
        $response->assertSee('山田 次郎1');

        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 7 && $contacts->total() === 9;
        });
    }

    public function test_指定したお問い合わせがカテゴリ情報付きで詳細ページに表示される(): void
    {
        $admin = User::factory()->create();

        $category = Category::create(['content' => 'システムについて']);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name'  => '山田',
            'last_name'   => '太郎',
            'gender'      => 1,
            'email'       => 'taro@example.com',
            'telephone'   => '09011112222',
            'address'     => '大阪府',
            'detail'      => '詳細確認用のテストお問い合わせです。',
        ]);

        $response = $this->actingAs($admin)->get('/admin/contacts/' . $contact->id);

        $response->assertStatus(200);
        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('taro@example.com');
        $response->assertSee('システムについて');

        $response->assertViewHas('contact', function ($viewContact) use ($contact) {
            return $viewContact->id === $contact->id
                && $viewContact->category->content === 'システムについて';
        });
    }

    public function test_お問い合わせが削除され管理者画面にリダイレクトされること(): void
    {
        $admin = User::factory()->create();

        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name'  => '山田',
            'last_name'   => '太郎',
            'gender'      => 1,
            'email'       => 'delete_test@example.com',
            'telephone'   => '09011112222',
            'address'     => '大阪府',
            'detail'      => '削除テスト用のお問い合わせです。',
        ]);

        $response = $this->actingAs($admin)->delete('/admin/contacts/' . $contact->id);

        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    
}