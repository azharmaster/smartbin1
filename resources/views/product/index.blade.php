@extends('layouts.app')
@section('content_title', 'Data Produk')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Produk</h4>
    </div>
    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger d-flex flex-column">
            @foreach ($errors->all() as $error)
            <small class="text-white my-2">{{ $error }}</small>
            @endforeach
        </div>
        @endif

        <div class="d-flex justify-content-end mb-2">
            <x-product.form-product />
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-responsive" id="table1">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>SKKU</th>
                        <th>Kategori</th>
                        <th>Nama produk</th>
                        <th>Harga Jual</th>
                        <th>Harga beli</th>
                        <th>Stok</th>
                        <th>Aktif</th>
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product->sku }}</td>
                        <td>
                            {{ $product->kategori ? $product->kategori->nama_kategori : 'Tidak ada kategori' }}
                        </td>
                        <td>{{ $product->nama_produk }}</td>
                        <td>RM {{ number_format($product->harga_jual) }}</td>
                        <td>RM {{ number_format($product->harga_beli_pokok) }}</td>
                        <td>{{ number_format($product->stok) }}</td>
                        <td>
                            <p class="badge {{ $product->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $product->is_active ? 'Aktif' : 'Tidak aktif' }}
                            </p>
                        </td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center">
                                <x-product.form-product :id="$product->id" />
                                <a href="{{ route('master-data.product.destroy', $product->id) }}" data-confirm-delete="true" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash-alt text-white"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection