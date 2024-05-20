<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'product_name',
        'price',
        'stock',
        'company_id',
        'comment',
        'img_path',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function searchProducts($companyId, $search, $sort, $direction)
    {
        $query = self::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($search) {
            $query->where('product_name', 'LIKE', "%{$search}%");
        }

        if ($sort) {
            $direction = $direction == 'desc' ? 'desc' : 'asc';
            $query->orderBy($sort, $direction);
        }

        return $query->paginate(10);
    }

    public static function createProduct($requestData)
    {
        $product = new self([
            'product_name' => $requestData['product_name'],
            'company_id' => $requestData['company_id'],
            'price' => $requestData['price'],
            'stock' => $requestData['stock'],
            'comment' => $requestData['comment'] ?? null,
        ]);

        if (isset($requestData['img_path'])) {
            $filename = $requestData['img_path']->getClientOriginalName();
            $filePath = $requestData['img_path']->storeAs('products', $filename, 'public');
            $product->img_path = '/storage/' . $filePath;
        }

        $product->save();

        return $product;
    }

    public function updateProduct($requestData)
    {
        $this->product_name = $requestData['product_name'];
        $this->company_id = $requestData['company_id'];
        $this->price = $requestData['price'];
        $this->stock = $requestData['stock'];
        $this->comment = $requestData['comment'];
    
        if (isset($requestData['img_path'])) {
            if ($this->img_path) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $this->img_path));
            }
    
            $filename = $requestData['img_path']->getClientOriginalName();
            $filePath = $requestData['img_path']->storeAs('products', $filename, 'public');
            $this->img_path = '/storage/' . $filePath;
        }
    
        $this->save();
    }
    
    public function deleteProduct()
    {
        if ($this->img_path) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $this->img_path));
        }
    
        $this->delete();
    }
}
