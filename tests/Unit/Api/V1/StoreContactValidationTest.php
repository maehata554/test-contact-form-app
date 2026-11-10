<?php

namespace Tests\Unit\Api\V1;

use App\Http\Requests\Api\V1\StoreRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreContactValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function validate(array $data)
    {
        $request = new StoreRequest();
        return Validator::make($data, $request->rules());
    }

    public function test_バリデーション通過()
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル101',
            'gender' => 1,
            'category_id' => $category->id,
            'detail' => 'お問い合わせの内容です。',
        ];

        $validator = $this->validate($data);

        $this->assertFalse($validator->fails());
    }

    public function test_バリデーションを通らない()
    {
        $validator = $this->validate([]);

        $this->assertTrue($validator->fails());

        $errors = $validator->errors();
        $this->assertArrayHasKey('first_name', $errors->toArray());
        $this->assertArrayHasKey('last_name', $errors->toArray());
        $this->assertArrayHasKey('email', $errors->toArray());
        $this->assertArrayHasKey('tel', $errors->toArray());
        $this->assertArrayHasKey('address', $errors->toArray());
        $this->assertArrayHasKey('category_id', $errors->toArray());
        $this->assertArrayHasKey('detail', $errors->toArray());
    }

}
