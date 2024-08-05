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
                <button id="searchButton" class="btn btn-outline-secondary" type="button">絞り込み</button>
            </div>
        </form>

        <button id="resetButton" class="btn btn-success mt-3">検索条件を元に戻す</button>
    </div>

    <div class="products mt-5">
        <h2>商品情報</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><a href="#" class="sort-link" data-sort="id">ID</a></th>
                    <th><a href="#" class="sort-link" data-sort="product_name">商品名</a></th>
                    <th><a href="#" class="sort-link" data-sort="company_name">メーカー</a></th>
                    <th><a href="#" class="sort-link" data-sort="price">価格</a></th>
                    <th><a href="#" class="sort-link" data-sort="stock">在庫数</a></th>
                    <th>コメント</th>
                    <th>商品画像</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="productsTableBody">
            @foreach ($products as $product)
                <tr id="productRow{{ $product->id }}">
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->company->company_name }}</td>
                    <td>{{ $product->price }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>{{ $product->comment ?? '初期値' }}</td>
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

<script>
function confirmDelete(id) {
    if (confirm('本当に削除しますか？')) {
        $.ajax({
            url: `/products/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(result) {
                $(`#productRow${id}`).remove();
                alert('商品を削除しました');
            },
            error: function(result) {
                alert('削除に失敗しました');
            }
        });
    }
}

$(document).ready(function () {
    $('.sort-link').on('click', function (e) {
        e.preventDefault();
        let sort = $(this).data('sort');
        let currentDirection = '{{ request('direction', 'asc') }}';
        let currentSort = '{{ request('sort', 'id') }}';
        let direction = currentSort === sort && currentDirection === 'asc' ? 'desc' : 'asc';

        let url = new URL(window.location.href);
        url.searchParams.set('sort', sort);
        url.searchParams.set('direction', direction);
        window.location.href = url.toString();
    });

    $('#searchButton').on('click', function () {
        let search = $('#search').val();
        let company_id = $('#company_id').val();
        let price_min = $('#price_min').val();
        let price_max = $('#price_max').val();
        let stock_min = $('#stock_min').val();
        let stock_max = $('#stock_max').val();
        let sort = '{{ request('sort', 'id') }}';
        let direction = '{{ request('direction', 'asc') }}';

        $.ajax({
            url: "{{ route('products.search') }}",
            type: "GET",
            data: {
                search: search,
                company_id: company_id,
                price_min: price_min,
                price_max: price_max,
                stock_min: stock_min,
                stock_max: stock_max,
                sort: sort,
                direction: direction
            },
            success: function (response) {
                let tbody = $('#productsTableBody');
                tbody.empty();

                response.products.forEach(product => {
                    let img_path = product.img_path ? "{{ asset('') }}" + product.img_path : '';
                    tbody.append(`
                        <tr id="productRow${product.id}">
                            <td>${product.id}</td>
                            <td>${product.product_name}</td>
                            <td>${product.company ? product.company.company_name : ''}</td>
                            <td>${product.price}</td>
                            <td>${product.stock}</td>
                            <td>${product.comment ?? '初期値'}</td>
                            <td><img src="${img_path}" alt="商品画像" width="100"></td>
                            <td>
                                <a href="/products/${product.id}" class="btn btn-info btn-sm mx-1">詳細表示</a>
                                <a href="/products/${product.id}/edit" class="btn btn-primary btn-sm mx-1">編集</a>
                                <button type="button" class="btn btn-danger btn-sm mx-1 delete-button" data-id="${product.id}">削除</button>
                            </td>
                        </tr>
                    `);
                });
            },
            error: function (error) {
                alert('検索に失敗しました');
                console.log(error);
            }
        });
    });

    $(document).on('click', '.delete-button', function() {
        let id = $(this).data('id');
        confirmDelete(id);
    });

    $('#resetButton').on('click', function () {
        $('#searchForm')[0].reset();
        window.location.href = "{{ route('products.index') }}";
    });
});
</script>

@endsection

