@extends('shop.layouts.app')
@section('title','Chi tiết Voucher')
@section('content')
<div class="container">
    <h1>Chi tiết Voucher: {{ $voucher->code }}</h1>
    <ul>
        <li>Loại: {{ $voucher->type }}</li>
        <li>Giá trị: {{ $voucher->value }}</li>
        <li>Người nhận: {{ $voucher->publisher?->name ?? 'Tất cả' }}</li>
    </ul>
    <a href="{{ route('shop.vouchers.index') }}">Quay lại</a>
</div>
@endsection
