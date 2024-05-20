<?php

namespace App\Http\Controllers;

use App\Models\Product; 
use App\Models\Company;  
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');


class ProductController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->company_id;
        $search = $request->search;
        $sort = $request->sort;
        $direction = $request->direction;

        $products = Product::searchProducts($companyId, $search, $sort, $direction);

        $companies = Company::pluck('company_name', 'id');

        return view('products.index', ['products' => $products, 'companies' => $companies]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required',
            'company_id' => 'required',
            'price' => 'required',
            'stock' => 'required',
            'comment' => 'nullable',
            'img_path' => 'nullable|image|max:2048',
        ]);

        try {
            $product = Product::createProduct($request->all());

            return redirect('products')->with('success', '商品が正常に登録されました');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '商品登録中にエラーが発生しました: ' . $e->getMessage()]);
        }
    }

    public function show(Product $product)
    {
        return view('products.show', ['product' => $product]);
    }

    public function edit(Product $product)
    {
        $companies = Company::all();
        return view('products.edit', compact('product', 'companies'));
    }

    public function create()
{
    $companies = Company::all();
    return view('products.create', compact('companies'));
}

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'product_name' => 'required',
            'company_id' => 'required',
            'price' => 'required',
            'stock' => 'required',
            'comment' => 'nullable',
            'img_path' => 'nullable|image|max:2048',
        ]);

        try {
            $product->updateProduct($request->all());

            return redirect()->route('products.index')->with('success', '商品情報が更新されました');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '商品更新中にエラーが発生しました: ' . $e->getMessage()]);
        }
    }

    public function destroy(Product $product)
    {
        try {
            $product->deleteProduct();

            return redirect('/products')->with('success', '商品が削除されました');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '商品削除中にエラーが発生しました: ' . $e->getMessage()]);
        }
    }
}