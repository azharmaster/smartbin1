<div>
    <button type="button" class="  {{ $id ? 'btn btn-default' : 'btn btn-primary' }}" data-toggle="modal"
        data-target="#formProdoct{{ $id ?? '' }}">
        {{ $id ? 'Edit' : 'Add' }}
    </button>

    <div class="modal fade" id="formProdoct{{ $id ?? '' }}">
        <form method="POST" action="{{ route('master-data.product.store') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $id ?? '' }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ $id ? 'Form Kemaskini Produk' : 'Form Tambah Produk' }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">


                        <div class="form-group">
                            <label for="">Nama Produk</label>
                            <input type="text" class="form-control" name="nama_produk" id="nama_produk"
                                value="{{ $id ? $nama_product : old('nama_produk') ?? '' }}">

                        </div>
                        <div class="form-group">
                            <label for="kategori_id">Kategori Produk</label>
                            <select class="form-control" name="kategori_id" id="kategori_id">
                                @foreach ($kategori as $item)
                                <option value="{{ $item->id }}"
                                    {{ $kategori_id || old('kategori_id') == $item->id ? 'selected' : ''}}>
                                    {{ $item->nama_kategori }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Harga Jual</label>
                            <input type="number" class="form-control" name="harga_jual" id="harga_jual"
                                value="{{ $id ? $harga_jual : old('harga_jual') ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="">Harga Beli Pokok</label>
                            <input type="number" class="form-control" name="harga_beli_pokok" id="harga_beli_pokok"
                                value="{{ $id ? $harga_jual : old('harga_beli_pokok') ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="">Stok Persiadaan</label>
                            <input type="number" class="form-control" name="stok" id="stok"
                                value="{{ $id ? $harga_jual : old('stok') ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="">Stok Minimal</label>
                            <input type="number" class="form-control" name="stok_minimal" id="stok_minimal"
                                value="{{ $id ? $stok_minimal : old('stok_minimal') ?? '' }}">
                        </div>
                        <div class="form-group">

                            <div class="d-flex">
                                <label for="">Produk Aktif?</label>
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    {{ old('is_active', $id ? $is_active :false) ? 'checked' : ''}}>
                                <span>*jika aktif akan keluar di paparan</span>
                            </div>
                        </div>




                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
    </div>
    </form>
</div>