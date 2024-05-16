<?php

namespace App\Http\Controllers;

use App\Models\Product; 
use App\Models\Company; 
use Illuminate\Http\Request; 


class ProductController extends Controller 
{
    
    public function index(Request $request)
{
    
    $query = Product::query();

    $companies = Company::pluck('company_name', 'id');
    
    if ($companyId = $request->company_id) {
        $query->where('company_id', $companyId);
    }

    if($search = $request->search){
        $query->where('product_name', 'LIKE', "%{$search}%");
    }

    if($sort = $request->sort){
        $direction = $request->direction == 'desc' ? 'desc' : 'asc'; 
        $query->orderBy($sort, $direction);
    }
    
    $products = $query->paginate(10);

    
    return view('products.index', ['products' => $products, 'companies' => $companies]);
}

    public function create()
    {
        
        $companies = Company::all();
        return view('products.create', compact('companies'));
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
       
        $product = new Product([
            'product_name' => $request->get('product_name'),
            'company_id' => $request->get('company_id'),
            'price' => $request->get('price'),
            'stock' => $request->get('stock'),
            'comment' => $request->get('comment'),
        ]);
       

        if($request->hasFile('img_path')){ 
            $filename = $request->img_path->getClientOriginalName();
            $filePath = $request->img_path->storeAs('products', $filename, 'public');
            $product->img_path = '/storage/' . $filePath;
        }
        
        $product->save();

        return redirect('products');
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

    public function update(Request $request, Product $product)
    {
       
        $request->validate([
            'product_name' => 'required',
            'price' => 'required',
            'stock' => 'required',
        ]);
        
        $product->product_name = $request->product_name;
        
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->comment = $request->comment;
        $product->save();
       
        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully');
        
    }

    public function destroy(Product $product)

    {
        
        $product->delete();

        
        return redirect('/products');
        
    }
}
