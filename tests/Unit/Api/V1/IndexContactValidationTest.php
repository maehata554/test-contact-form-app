<?php

namespace Tests\Unit\Api\V1;
use App\Models\Category;
use App\Http\Requests\Api\Vi\IndexContactRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function validate(array $data)
    {
        $request = new IndexContactRequest();
        return Validator::make($data, $request->rules());
    }/**
     * A basic unit test example.
     */
    public function test_API検索(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $data = [
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => 1,
            'per_page' => 15,
        ];

        $validator = $this->validate($data);

        if ($validator->fails()) {
            throw new \Exception(json_encode($validator->errors()->toArray(), JSON_UNESCAPED_UNICODE));
        }

        $this->assertFalse($validator->fails());
    }

    public function test_gender_must_be_in_1_2_3()
    {
        $validator = $this->validate(['gender' => 4]);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }

    public function test_per_page_must_be_integer()
    {
        $validator = $this->validate(['per_page' => 'abc']);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());
    }
}
