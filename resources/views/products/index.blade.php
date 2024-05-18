@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">商品情報一覧</h1>

    <a href="{{ route('products.create') }}" class="btn btn-primary mb-3">商品新規登録</a>



    <div class="search mt-5">
    
    <h2>検索条件で絞り込み</h2>
    
    
    <form action="{{ route('products.index') }}" method="GET" class="row g-3">

        
        <div class="col-sm-12 col-md-3">
            <input type="text" name="search" class="form-control" placeholder="商品名" value="{{ request('search') }}">
        </div>


        
        <div class="col-sm-12 col-md-1">
            <button class="btn btn-outline-secondary" type="submit">絞り込み</button>
        </div>
        
    </form>
    <div class="form-group">
       <label for="company_id">メーカー：</label>
       <select name="company_id" id="company_id" class="form-control">
           <option value="">すべてのメーカー</option>
           @if(is_array($companies))
               @foreach($companies as $id => $company)
                  <option value="{{ $id }}" {{ request('company_id') == $id ? 'selected' : '' }}>{{ $company }}</option>
               @endforeach
           @endif
       </select>
   </div>
</div>

<a href="{{ route('products.index') }}" class="btn btn-success mt-3">検索条件を元に戻す</a>



    <div class="products mt-5">
        <h2>商品情報</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="position: sticky; top: 0; background-color: white;">ID</th>
                    <th style="position: sticky; top: 0; background-color: white;">商品名</th>
                    <th style="position: sticky; top: 0; background-color: white;">メーカー</th>
                    <th style="position: sticky; top: 0; background-color: white;">価格
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'price', 'direction' => 'asc']) }}">↑</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'price', 'direction' => 'desc']) }}">↓</a>
                    </th>
                    <th style="position: sticky; top: 0; background-color: white;">在庫数
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'stock', 'direction' => 'asc']) }}">↑</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'stock', 'direction' => 'desc']) }}">↓</a>
                    </th>
                    <th style="position: sticky; top: 0; background-color: white;">コメント</th>
                    <th style="position: sticky; top: 0; background-color: white;">商品画像</th>
                    <th style="position: sticky; top: 0; background-color: white;">操作</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product->id }}</td> 
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->company->name }}</td>
                    <td>{{ $product->price }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>{{ $product->comment ? $product->comment : '初期値' }}</td>
                    <td><img src="{{ asset($product->img_path) }}" alt="商品画像" width="100"></td>
                    </td>
                    <td>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-info btn-sm mx-1">詳細表示</a>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm mx-1">編集</a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline" id="deleteForm">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger btn-sm mx-1" onclick="confirmDelete()">削除</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            <script>
            function confirmDelete() {
                if (confirm('本当に削除しますか？')) {
                    document.getElementById('deleteForm').submit();
                }
            }
            </script>
            </tbody>
        </table>
    </div>
    
    {{ $products->appends(request()->query())->links() }}
</div>
@endsection
