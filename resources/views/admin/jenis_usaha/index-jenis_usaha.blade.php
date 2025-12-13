@extends('adminlte::page')

@section('title', 'Jenis Usaha')

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
        <i class="fas fa-store"></i> Jenis Usaha
    </h1>
@stop

@section('content')
    <a href="{{ route('admin.jenis_usaha-create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-add"></i> Tambah Jenis Usaha</a>
    {{-- tambahkan jarak dan garis --}}
    <br>
    <hr color="#ccc">
    {{-- tambahkan garis lurus --}}
    <table id="jenis_usaha-table" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Kode Jenis Usaha</th>
                <th>Nama Jenis Usaha</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jenisUsahas as $jenisUsaha)
                <tr>
                    <td>{{ $jenisUsaha->kode_jenis_usaha }}</td>
                    <td>{{ $jenisUsaha->nama_jenis_usaha }}</td>
                    <td>
                        <a href="{{ route('admin.jenis_usaha-edit', $jenisUsaha->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.jenis_usaha-destroy', $jenisUsaha->id) }}" method="POST"
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
            $('#jenis_usaha-table').DataTable({
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
                        targets: 2
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
