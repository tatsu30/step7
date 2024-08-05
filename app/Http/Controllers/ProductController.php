<?php

namespace App\Http\Controllers;

use App\Models\Product; 
use App\Models\Company;  
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request)
    {
        $companyId = $request->company_id;
        $search = $request->search;
        $priceMin = $request->price_min;
        $priceMax = $request->price_max;
        $stockMin = $request->stock_min;
        $stockMax = $request->stock_max;
        $sort = $request->sort ?? 'id';
        $direction = $request->direction ?? 'desc';

        $query = Product::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($search) {
            $query->where('product_name', 'like', '%' . $search . '%');
        }
        if ($priceMin) {
            $query->where('price', '>=', $priceMin);
        }
        if ($priceMax) {
            $query->where('price', '<=', $priceMax);
        }
        if ($stockMin) {
            $query->where('stock', '>=', $stockMin);
        }
        if ($stockMax) {
            $query->where('stock', '<=', $stockMax);
        }
        if ($sort) {
            $query->orderBy($sort, $direction);
        }

        $products = $query->paginate(10);
        $companies = Company::pluck('company_name', 'id');

        return view('products.index', compact('products', 'companies'));
    }

    public function search(Request $request)
    {
        $companyId = $request->company_id;
        $search = $request->search;
        $priceMin = $request->price_min;
        $priceMax = $request->price_max;
        $stockMin = $request->stock_min;
        $stockMax = $request->stock_max;

        Log::info('Search Request:', 
        [
            'company_id' => $companyId,
            'search' => $search,
            'price_min' => $priceMin, 
            'price_max' => $priceMax, 
            'stock_min' => $stockMin, 
            'stock_max' => $stockMax
        ]);

        try {
            $products = Product::query()
                ->when($companyId, function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                })
                ->when($search, function ($query) use ($search) {
                    $query->where('product_name', 'like', "%$search%");
                })
                ->when($priceMin, function ($query) use ($priceMin) {
                    $query->where('price', '>=', $priceMin);
                })
                ->when($priceMax, function ($query) use ($priceMax) {
                    $query->where('price', '<=', $priceMax);
                })
                ->when($stockMin, function ($query) use ($stockMin) {
                    $query->where('stock', '>=', $stockMin);
                })
                ->when($stockMax, function ($query) use ($stockMax) {
                    $query->where('stock', '<=', $stockMax);
                })
                ->get();

            Log::info('Search Results:', ['products' => $products]);

            return response()->json(['products' => $products]);
        } catch (\Exception $e) {
            Log::error('Search Error:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Internal Server Error'], 500);
        }
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
            $product->delete();
            return response()->json(['success' => true, 'message' => '商品を削除しました']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '削除に失敗しました: ' . $e->getMessage()]);
        }
    }

    public function showList(Request $request)
    {
        $keyword = $request->input('keyword');
        $search = $request->input('search');
        $jougenprice = $request->input('jougenprice');
        $kagenprice = $request->input('kagenprice');
        $jougenstock = $request->input('jougenstock');
        $kagenstock = $request->input('kagenstock');

        Log::info('Show List Request:', compact('keyword', 'search', 'jougenprice', 'kagenprice', 'jougenstock', 'kagenstock'));

        try {
            $nonon = new Product();
            $products = $nonon->search($keyword, $search, $jougenprice, $kagenprice, $jougenstock, $kagenstock);
            Log::info('Products data', ['products' => $products]);

            $companies = Company::pluck('company_name', 'id');
            Log::info('Companies data', ['companies' => $companies]);

            return view('product', compact('products', 'keyword', 'companies', 'search', 'jougenprice', 'kagenprice', 'jougenstock', 'kagenstock'));
        } catch (\Exception $e) {
            Log::error('Error in showList method', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Internal Server Error'], 500);
        }
    }
    
}