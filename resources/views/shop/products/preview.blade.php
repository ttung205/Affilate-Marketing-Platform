@extends('shop.layouts.app')

@section('title', 'Xem trước Import Sản phẩm')

@section('content')
<div class="products-container">
    <h1>Xem trước sản phẩm từ Excel</h1>
    <form action="{{ route('shop.products.import-excel') }}" method="POST">
        @csrf
        <input type="hidden" name="file_path" value="{{ $filePath }}">
        <table class="products-table">
            <thead>
                <tr>
                    <th>Tên</th>
                    <th>Mô tả</th>
                    <th>Giá</th>
                    <th>Danh mục</th>
                    <th>Tồn kho</th>
                    <th>Hoa hồng</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row['Tên'] ?? '' }}</td>
                        <td>{{ $row['Mô tả'] ?? '' }}</td>
                        <td>{{ $row['Gía'] ?? '' }}</td>
                        <td>{{ $row['Danh mục'] ?? '' }}</td>
                        <td>{{ $row['Tồn Kho'] ?? '' }}</td>
                        <td>{{ $row['Hoa hồng'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-success mt-3">Xác nhận nhập vào kho</button>
        <a href="{{ route('shop.products.index') }}" class="btn btn-secondary mt-3">Hủy bỏ</a>
    </form>
</div>
@endsection
