@extends('adminlte::page')

@section('title', 'Pengrajin')

@section('content_header')
    <h1
        style="
    font-size: 2rem;
    font-weight: bold;
    color: #343a40;
    text-shadow: 2px 2px 5px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 1px;
">
        <i class="fas fa-user"></i>Data Pengrajin
    </h1>
@stop

@section('content')
    <a href="{{ route('admin.pengerajin-create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-add"></i> Tambah Pengrajin</a>
    {{-- tambahkan jarak dan garis --}}
    <br>
    <hr color="#ccc">
    {{-- tambahkan garis lurus --}}
    <table id="pengerajin-table" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Jenis Kelamin</th>
                <th>Usia</th>
                <th>No Telepon</th>
                <th>Email</th>
                <th>Alamat</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pengerajins as $pengerajin)
                <tr>
                    <td>{{ $pengerajin->kode_pengerajin }}</td>
                    <td>{{ $pengerajin->nama_pengerajin }}</td>
                    <td>
                        @if ($pengerajin->jk_pengerajin == 'P')
                            Pria
                        @elseif($pengerajin->jk_pengerajin == 'W')
                            Wanita
                        @else
                            Tidak Diketahui
                        @endif
                    </td>
                    <td>{{ $pengerajin->usia_pengerajin }}</td>
                    <td>{{ $pengerajin->telp_pengerajin }}</td>
                    <td>{{ $pengerajin->email_pengerajin }}</td>
                    <td>{{ $pengerajin->alamat_pengerajin }}</td>
                    <td>
                        <a href="{{ route('admin.pengerajin-edit', $pengerajin->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.pengerajin-destroy', $pengerajin->id) }}" method="POST"
                            style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Usaha"
                                onclick="return confirm('Anda yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/custom.css"> --}}

    {{-- ini buat datatbales --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    {{-- ini soruce icon button --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

@stop

@section('js')
    {{-- <script src="/js/custom.js"></script> --}}

    {{-- ini buat datatbales --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#pengerajin-table').DataTable({
                scrollX: true, // 👉 Aktifkan horizontal scroll
                paging: true, // 👉 Aktifkan paging
                searching: true, // 👉 Aktifkan search box
                info: true, // 👉 Aktifkan info "Showing 1 to 10 of 50 entries"
                stateSave: true, // 👉 Aktifkan state saving (ingat posisi sort/page)
                order: [
                    [1, 'asc']
                ], // 👉 Default sorting berdasarkan Nama Usaha ASC
                columnDefs: [{
                        orderable: false,
                        targets: 7
                    }, // 👉 Kolom Foto dan Actions tidak bisa sort
                ]
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const logoutBtn = document.getElementById('logout-button');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById('logout-form').submit();
                });
            }
        });
    </script>
@stop
