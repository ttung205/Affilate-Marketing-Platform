@extends('shop.layouts.app')

@section('title', 'Nhập Sản Phẩm')

@section('content')
<div class="products-container">
    <h2>Xác Nhận Thêm Sản Phẩm</h2>
    <form action="{{ route('shop.products.import-excel') }}" method="POST">
        @csrf
        <input type="hidden" name="file_path" value="{{ $filePath }}">
        <table class="products-table">
      <thead>
       <tr>
         <th>Tên sản phẩm</th>
         <th>Mô tả</th>
         <th>Giá</th>
         <th>Tồn kho</th>
         <th>Danh mục</th>
         <th>Link Aff</th>
         <th>Hoa hồng (%)</th>
       </tr>
      </thead>
   <tbody>
    @foreach($rows as $row)
        <tr>
            <tr>
              <td>{{ $row['name'] ?? '' }}</td>
              <td>{{ $row['description'] ?? '' }}</td>
              <td>{{ $row['price'] ?? '' }}</td>
              <td>{{ $row['stock'] ?? '' }}</td>
              <td>{{ $row['category_id'] ?? '' }}</td>
              <td>{{ $row['affiliate_link'] ?? '' }}</td>
              <td>{{ $row['commission_rate'] ?? 0 }}</td>
            </tr>
        </tr>
    @endforeach
   </tbody>

        </table>

        <button type="submit" class="btn btn-success mt-3">Xác nhận nhập vào kho</button>
        <a href="{{ route('shop.products.index') }}" class="btn btn-secondary mt-3">Hủy bỏ</a>
    </form>
</div>
@endsection
