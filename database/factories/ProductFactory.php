<?php

namespace Database\Factories;
use App\Models\Product;
use App\Models\Company;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    
protected $model = Product::class;

public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'product_name' => $this->faker->word,  // ダミーの商品名
            'price' => $this->faker->numberBetween(100, 10000),  // 100から10,000の範囲のダミー価格
            'stock' => $this->faker->randomDigit,  // 0から9のランダムな数字でダミーの在庫数
            'comment' => $this->faker->sentence,  // ダミーの説明文
            'img_path' => 'https://picsum.photos/200/300',  // 200x300のランダムな画像
        ];
    }
}
