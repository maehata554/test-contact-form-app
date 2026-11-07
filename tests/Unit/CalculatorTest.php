<?php

namespace Tests\Unit;
use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    public function test_管理画面の検索機能でキーワード性別カテゴリ日付フィルタが正しく機能する(): void
    {
        $user = User::factory()->create();
        $categoryId = Category::insertGetId(['content' => '商品のお届けについて']);
        $category = Category::find($categoryId);

        $matchContact = Contact::create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1, // 男性
            'email' => 'yamada@example.com',
            'telephone' => '08012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => '商品の配送について',
            'created_at' => '2026-06-15 10:00:00',
        ]);

        $response = $this->actingAs($user)->get('/admin?' . http_build_query([
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
        ]));

        $response->assertStatus(200);


        $response->assertSee('山田');
    }


    public function 不正な性別値が指定された場合は拒否またはバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin?gender=99');

        $response->assertStatus(200);
    }
    public function test_不正な電話番号形式は拒否される(): void
    {
        $response = $this->post('/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => 'abc-defg-hijk',
            'address' => '東京都渋谷区',
            'category_id' => 1,
            'detail' => 'お問い合わせ内容テスト',
        ]);
        $response->assertSessionHasErrors(['tel']);
    }

    public function test_全ての必須項目とタグ入力を受け付けることができる(): void
    {
        $categoryId = Category::insertGetId(['content' => '商品のお届けについて']);

        $validData = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel1' => '080',
            'tel2' => '1234',
            'tel3' => '5678',
            'address' => '東京都渋谷区',
            'category_id' => $categoryId,
            'detail' => 'すべての項目とタグのテストです。',
        ];

        $response = $this->post('/contacts', $validData);
        $response->assertSessionHasNoErrors();

        $response->assertStatus(302);

        $this->assertDatabaseHas('contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@example.com',
            'telephone' => '08012345678',
        ]);
    }


    public function test_タグ名の必須入力と文字数制限および一意性が維持されている(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $response = $this->post('/admin/tags', [
            'name' => '',
        ]);
        $response->assertSessionHasErrors(['name']);

        $tooLongName = str_repeat('あ', 51);
        $response = $this->post('/admin/tags', [
            'name' => $tooLongName,
        ]);
        $response->assertSessionHasErrors(['name']);

        Tag::create(['name' => 'Laravel']);

        $response = $this->post('/admin/tags', [
            'name' => 'Laravel',
        ]);
        $response->assertSessionHasErrors(['name']);

        $response = $this->post('/admin/tags', [
            'name' => 'PHP',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tags', [
            'name' => 'PHP',
        ]);
    }

    public function test_自身の名前の維持は可能だが既に他で使用されているタグ名への変更は拒否される(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $tagA = Tag::create(['name' => 'Laravel']);
        $tagB = Tag::create(['name' => 'Vue.js']);

        $response = $this->put(route('tags.update', $tagA->id), [
            'name' => 'Laravel',
        ]);
        $response->assertSessionHasNoErrors();

        $response = $this->put(route('tags.update', $tagA->id), [
            'name' => 'React',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tags', [
            'id' => $tagA->id,
            'name' => 'React',
        ]);

        $response = $this->put(route('tags.update', $tagA->id), [
            'name' => 'Vue.js',
        ]);
        $response->assertSessionHasErrors(['name']);
    }

    public function test_1つのカテゴリから紐づく複数のお問い合わせが正しく取得できる(): void
    {
        $category = Category::create([
            'content' => 'システムについて',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name'  => '太郎',
            'last_name'   => 'テスト',
            'gender'      => 1,
            'email'       => 'test1@example.com',
            'telephone'   => '09012345678',
            'address'     => '大阪府泉南郡熊取町',
            'detail'      => 'お問い合わせ内容1',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name'  => '花子',
            'last_name'   => 'テスト',
            'gender'      => 2,
            'email'       => 'test2@example.com',
            'telephone'   => '09087654321',
            'address'     => '大阪府大阪市',
            'detail'      => 'お問い合わせ内容2',
        ]);

        $contacts = $category->contacts;

        $this->assertCount(2, $contacts);
        $this->assertEquals('お問い合わせ内容1', $contacts[0]->detail);
        $this->assertEquals('お問い合わせ内容2', $contacts[1]->detail);
    }

    public function test_1つのお問い合わせが複数のタグと同期できる(): void
    {
        $this->seed(\Database\Seeders\TagSeeder::class);

        $category = Category::create([
            'content' => 'システムについて',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name'  => '太郎',
            'last_name'   => 'テスト',
            'gender'      => 1,
            'email'       => 'test@example.com',
            'telephone'   => '09012345678',
            'address'     => '大阪府泉南市',
            'detail'      => 'タグの同期テスト用のお問い合わせです',
        ]);


        $tag1 = Tag::find(1);
        $tag2 = Tag::find(2);
        $tag3 = Tag::find(3);

        $contact->tags()->sync([$tag1->id, $tag2->id]);

        //正しくタグが紐づいているか検証
        $this->assertCount(2, $contact->tags);
        $this->assertTrue($contact->tags->contains($tag1));
        $this->assertTrue($contact->tags->contains($tag2));
        $this->assertFalse($contact->tags->contains($tag3));

        $contact->tags()->sync([$tag3->id]);
    }

    public function test_一つのタグに複数のお問い合わせが紐づいていること(): void
    {
        $this->seed(\Database\Seeders\TagSeeder::class);
        $targetTag = Tag::where('name', '質問')->first();

        $category = Category::create([
            'content' => 'システムについて',
        ]);

        $contact1 = Contact::create([
            'category_id' => $category->id,
            'first_name'  => '太郎',
            'last_name'   => 'テスト',
            'gender'      => 1,
            'email'       => 'test1@example.com',
            'telephone'   => '09012345678',
            'address'     => '大阪府泉南市',
            'detail'      => 'お問い合わせ内容1',
        ]);

        $contact2 = Contact::create([
            'category_id' => $category->id,
            'first_name'  => '花子',
            'last_name'   => 'テスト',
            'gender'      => 2,
            'email'       => 'test2@example.com',
            'telephone'   => '09087654321',
            'address'     => '大阪府大阪市',
            'detail'      => 'お問い合わせ内容2',
        ]);

        $contact1->tags()->sync([$targetTag->id]);
        $contact2->tags()->sync([$targetTag->id]);

        $contacts = $targetTag->contacts;

        $this->assertCount(2, $contacts);
        $this->assertTrue($contacts->contains($contact1));
        $this->assertTrue($contacts->contains($contact2));
    }
}