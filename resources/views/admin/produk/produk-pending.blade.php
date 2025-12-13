@extends('adminlte::page')

@section('title', 'Produk Pending')

@section('content_header')
    <h1>Produk Menunggu ACC</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($produks->isEmpty())
        <div class="alert alert-info">Tidak ada produk pending.</div>
    @else
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Kode</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produks as $p)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $p->nama_produk }}</td>
                                <td>{{ $p->kode_produk }}</td>
                                <td>{{ optional($p->kategoriProduk)->nama_kategori ?? '-' }}</td>
                                <td>{{ $p->harga }}</td>
                                <td>{{ $p->stok }}</td>
                                <td class="d-flex gap-2">
                                    <form action="{{ route('admin.produk-approve', $p->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-success btn-sm" type="submit">ACC</button>
                                    </form>

                                    <form action="{{ route('admin.produk-reject', $p->id) }}" method="POST"
                                          onsubmit="return confirm('Yakin tolak produk ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-danger btn-sm" type="submit">Tolak</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@stop
