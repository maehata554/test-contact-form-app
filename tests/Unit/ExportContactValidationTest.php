<?php

namespace Tests\Unit;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ExportContactValidationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */

    protected function validate(array $data)
    {
        return Validator::make($data, [
            'gender' => ['nullable', 'in:1,2,3'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ]);
    }
    public function test_test_正しいフィルタ条件の場合はバリデーションを通過する()
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $data = [
            'gender' => 1,
            'category_id' => $category->id,
        ];

        $validator = $this->validate($data);
        $this->assertFalse($validator->fails());
    }

    public function test_未指定の場合もフィルタなしとして通過する()
    {
        $data = [];

        $validator = $this->validate($data);
        $this->assertFalse($validator->fails());
    }

    public function test_不正な性別や存在しないカテゴリIDの場合は拒否される()
    {
        $data = [
            'gender' => 99,
            'category_id' => 99999,
        ];

        $validator = $this->validate($data);
        $this->assertTrue($validator->fails());

        $errors = $validator->errors();
        $this->assertArrayHasKey('gender', $errors->toArray());
        $this->assertArrayHasKey('category_id', $errors->toArray());
    }
}
