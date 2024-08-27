@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">商品情報一覧</h1>

    <a href="{{ route('products.create') }}" class="btn btn-primary mb-3">商品新規登録</a>

    <div class="search mt-5">
        <h2>検索条件で絞り込み</h2>

        <form method="GET" class="row g-3" id="searchForm">
        <div class="col-sm-12 col-md-3">
                <input type="text" id="search" name="search" class="form-control" placeholder="商品名" value="{{ request('search') }}">
            </div>

            <div class="col-sm-12 col-md-3">
                <select id="company_id" name="company_id" class="form-control">
                    <option value="">すべてのメーカー</option>
                    @foreach($companies as $id => $company)
                        <option value="{{ $id }}" {{ request('company_id') == $id ? 'selected' : '' }}>{{ $company }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-12 col-md-2">
                <input type="number" id="price_min" name="price_min" class="form-control" placeholder="最低価格" value="{{ request('price_min') }}">
            </div>

            <div class="col-sm-12 col-md-2">
                <input type="number" id="price_max" name="price_max" class="form-control" placeholder="最高価格" value="{{ request('price_max') }}">
            </div>

            <div class="col-sm-12 col-md-2">
                <input type="number" id="stock_min" name="stock_min" class="form-control" placeholder="最低在庫数" value="{{ request('stock_min') }}">
            </div>

            <div class="col-sm-12 col-md-2">
                <input type="number" id="stock_max" name="stock_max" class="form-control" placeholder="最高在庫数" value="{{ request('stock_max') }}">
            </div>

            <div class="col-sm-12 col-md-1">
                <button id="searchButton" class="btn btn-outline-secondary" type="submit">絞り込み</button>
            </div>
        </form>

        <button id="resetButton" class="btn btn-success mt-3" onclick="window.location.href='{{ route('products.index') }}'">検索条件を元に戻す</button>
    </div>

    <div class="products mt-5">
        <h2>商品情報</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => request('sort') === 'id' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">ID</a></th>
                    <th><a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'product_name', 'direction' => request('sort') === 'product_name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">商品名</a></th>
                    <th><a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'company_name', 'direction' => request('sort') === 'company_name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">メーカー</a></th>
                    <th><a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'price', 'direction' => request('sort') === 'price' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">価格</a></th>
                    <th><a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'stock', 'direction' => request('sort') === 'stock' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">在庫数</a></th>

                    <th>コメント</th>
                    <th>商品画像</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
                <tr id="productRow{{ $product->id }}">
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->company->company_name }}</td>
                    <td>{{ $product->price }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>{{ $product->comment ? $product->comment : '初期値' }}</td>
                    <td><img src="{{ asset($product->img_path) }}" alt="商品画像" width="100"></td>
                    <td>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-info btn-sm mx-1">詳細表示</a>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm mx-1">編集</a>
                        <button type="button" class="btn btn-danger btn-sm mx-1 delete-button" data-id="{{ $product->id }}">削除</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    
    {{ $products->appends(request()->query())->links() }}
</div>
@endsection

@push('scripts')
<script>
    console.log(typeof jQuery);
$(document).on('click', '.delete-button', function() {
    let id = $(this).data('id');
    console.log("削除ボタンがクリックされました: ", id); // デバッグ用

    if (confirm('本当に削除しますか？')) {
        $.ajax({
            url: `{{ url('products') }}/${id}`,
            type: 'POST',
            data: {
                '_method': 'DELETE',
                '_token': '{{ csrf_token() }}'
            },
            success: function(result) {
                console.log("サーバーからの応答: ", result); // デバッグ用
                if (result.success) {
                    $(`#productRow${id}`).remove();
                    alert(result.message);
                } else {
                    alert('削除に失敗しました');
                }
            },
            error: function(result) {
                console.error("エラーが発生しました: ", result); // デバッグ用
                alert('削除に失敗しました');
            }
        });
    }
});
</script>
@endpush
