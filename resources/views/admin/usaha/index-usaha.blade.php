@extends('adminlte::page')

@section('title', 'Usaha')

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
    gap: 10px;
">
        <i class="fas fa-store"></i> Data Usaha
    </h1>

@stop

@section('content')
    <a href="{{ route('admin.usaha-create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-add"></i> Tambah Usaha
    </a>
    {{-- tambahkan jarak --}}
    <br>
    {{-- tambahkan garis lurus --}}
    <hr color="#ccc">

    {{-- Tabel --}}
    <table id="usaha-table" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Kode Usaha</th>
                <th>Nama Usaha</th>
                <th>No Telepon</th>
                <th>Email</th>
                <th>Deskripsi</th>
                <th>Foto</th>
                <th>Link Gmap</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usahas as $usaha)
                <tr>
                    <td>{{ $usaha->kode_usaha }}</td>
                    <td>{{ $usaha->nama_usaha }}</td>
                    <td>{{ $usaha->telp_usaha }}</td>
                    <td>{{ $usaha->email_usaha }}</td>
                    <td>{{ $usaha->deskripsi_usaha }}</td>
                    <td>
                        @if ($usaha->foto_usaha)
                            <img src="{{ asset('storage/' . $usaha->foto_usaha) }}" alt="Foto Usaha"
                                style="width: 50px; height: auto;">
                        @else
                            No Image
                        @endif
                    </td>
                    <td>
                        <a href="{{ $usaha->link_gmap_usaha }}" class="gmap-link" target="_blank">
                            {{ $usaha->link_gmap_usaha }}
                        </a>
                    </td>
                    <td>
                        @if ($usaha->status_usaha == 'aktif')
                            <span class="badge bg-success">Aktif</span>
                        @elseif($usaha->status_usaha == 'nonaktif')
                            <span class="badge bg-secondary">Tidak Aktif</span>
                        @elseif($usaha->status_usaha == 'tutup')
                            <span class="badge bg-danger">Tutup</span>
                        @elseif($usaha->status_usaha == 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @elseif($usaha->status_usaha == 'dibekukan')
                            <span class="badge bg-info">Dibekukan</span>
                        @else
                            <span class="badge bg-dark">Unknown</span> <!-- kalau status tidak dikenali -->
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.usaha-edit', $usaha->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.usaha-destroy', $usaha->id) }}" method="POST"
                            style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Usaha"
                                onclick="return confirm('Anda yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i>
                            </button>
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


    {{-- ini buat styling link gmap --}}
    <style>
        .gmap-link {
            display: inline-block;
            max-width: 200px;
            /* Bebas, mau 150px, 200px, dll */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }
    </style>

    {{-- ini buat styling badge --}}
    <style>
        .badge {
            font-size: 0.8rem;
            padding: 0.5em 0.75em;
            border-radius: 0.5rem;
        }

        .badge.bg-success {
            background-color: #3bb357;
        }

        .badge.bg-secondary {
            background-color: #6c757d;
        }

        .badge.bg-danger {
            background-color: #dc3545;
        }

        .badge.bg-warning {
            background-color: #ffc107;
        }

        .badge.bg-info {
            background-color: #17a2b8;
        }

        .badge.bg-dark {
            background-color: #343a40;
        }
    </style>
@stop

@section('js')
    {{-- <script src="/js/custom.js"></script> --}}

    {{-- ini buat datatbales --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#usaha-table').DataTable({
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
                        targets: [5, 8]
                    }, // 👉 Kolom Foto dan Actions tidak bisa sort
                    {
                        width: '100px',
                        targets: 5
                    } // 👉 Kolom Foto kecil
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
