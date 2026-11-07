<?php

namespace Tests\Feature;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;/**
     * A basic feature test example.
     */
    public function test_問い合わせ入力フォームとサンクスページが表示される(): void
    {
        //Arrange
        Category::insert([
            ['content' => '商品のお届けについて'],
            ['content' => '商品の交換について'],
            ['content' => '商品トラブル'],
            ['content' => 'ショップへのお問い合せ'],
            ['content' => 'その他'],
        ]);

        Tag::insert([
            ['name' => '質問'],
            ['name' => '要望'],
            ['name' => '不具合報告'],
            ['name' => 'その他'],
        ]);
        //Act
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee('商品のお届けについて');
        $response->assertSee('商品の交換について');
        $response->assertSee('商品トラブル');
        $response->assertSee('ショップへのお問い合せ');
        $response->assertSee('その他');

        // すべてのタグ名がチェックボックスのラベルとして画面に表示されているか
        $response->assertSee('質問');
        $response->assertSee('要望');
        $response->assertSee('不具合報告');
        $response->assertSee('その他');

        $thanksResponse = $this->get('/thanks');
        $thanksResponse->assertStatus(200);
    }

    public function バリデーション通過時に確認画面に正しい入力内容が表示される(): void
    {
        $category = Category::create(['content' => '商品のお届けについて']);
        $tag = Tag::create(['name' => '質問']);

        // フォームに入力するテストデータ
        $formData = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel1' => '080',
            'tel2' => '1234',
            'tel3' => '5678',
            'tel' => '08012345678', // コントローラーの仕様に合わせて必要に応じて
            'address' => '東京都渋谷区千駄ヶ谷1-2-3',
            'building' => '千駄ヶ谷マンション305',
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
            'detail' => '商品の配送状況について教えてください。',
        ];

        // POST /contacts/confirm にリクエストを送信
        $response = $this->post('/contacts/confirm', $formData);

        // ステータス200（確認画面の表示成功）
        $response->assertStatus(200);

        // 確認画面ビューにデータが渡されていること、画面に入力値が表示されていることを確認
        $response->assertViewHas('validated');
        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('test@example.com');
        $response->assertSee('商品のお届けについて'); // 選択したカテゴリ名
        $response->assertSee('質問'); // 選択したタグ名
    }

    /** @test */
    public function 必須項目が未入力の場合バリデーションエラーとなりリダイレクトされる(): void
    {
        // わざと空のデータ（または必須項目が欠けたデータ）を送信
        $response = $this->post('/contacts/confirm', []);

        // バリデーションエラーにより元のフォーム画面へリダイレクトされること
        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }
}
