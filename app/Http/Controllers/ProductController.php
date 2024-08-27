<?php

namespace App\Http\Controllers;

use App\Models\Product; 
use App\Models\Company;  
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        if ($search = $request->input('search')) {
            $query->where('product_name', 'like', "%{$search}%");
        }
    
        if ($companyId = $request->input('company_id')) {
            $query->where('company_id', $companyId);
        }
    
        if ($priceMin = $request->input('price_min')) {
            $query->where('price', '>=', $priceMin);
        }
    
        if ($priceMax = $request->input('price_max')) {
            $query->where('price', '<=', $priceMax);
        }
    
        if ($stockMin = $request->input('stock_min')) {
            $query->where('stock', '>=', $stockMin);
        }
    
        if ($stockMax = $request->input('stock_max')) {
            $query->where('stock', '<=', $stockMax);
        }

        if ($sort = $request->input('sort')) {
            $direction = $request->input('direction', 'asc');
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('id', 'asc'); 
        }
    
        $products = $query->paginate(10); 
    
        return view('products.index', [
            'products' => $products,
            'companies' => Company::pluck('company_name', 'id'),
        ]);
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