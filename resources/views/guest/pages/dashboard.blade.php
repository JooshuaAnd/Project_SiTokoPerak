@extends('guest.layouts.main')
@section('title', 'Dashboard Pelanggan')

@section('content')
    <div class="container py-5 mt-5">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            <!-- SIDEBAR PROFIL -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Profil Saya</h5>
                    </div>
                    <div class="card-body text-center">

<<<<<<< HEAD
                        <i class="fa fa-user-circle fa-5x text-muted mb-3"></i>
=======
                        @if (Auth::user()->profile_picture_path)
                            <img src="{{ asset('storage/' . Auth::user()->profile_picture_path) }}" alt="Foto Profil" class="rounded-circle mb-3" width="100" height="100" style="object-fit: cover;">
                        @else
                            <i class="fa fa-user-circle fa-5x text-muted mb-3"></i>
                        @endif
>>>>>>> hans

                        <h5 class="mb-0 fw-bold">{{ Auth::user()->name }}</h5>

                        <p class="text-muted mb-1">
                            <i class="fa fa-at"></i> {{ Auth::user()->username }}
                        </p>

                        <p class="text-muted mb-1">
                            <i class="fa fa-phone"></i> {{ Auth::user()->phone ?? '-' }}
                        </p>

                        <p class="text-muted mb-3">
                            <i class="fa fa-map-marker"></i> {{ Auth::user()->address ?? '-' }}
                        </p>

                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProfile">
                            Edit Profil
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIWAYAT PESANAN -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Riwayat Pesanan</h5>
                    </div>

                    <div class="card-body p-0">
                        @if($orders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No. Pesanan</th>
                                            <th>Tanggal</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($orders as $order)
                                            <tr>
                                                <td><strong>{{ $order->order_number }}</strong></td>
                                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                                <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge {{ $order->status_badge }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('customer.order.detail', $order->id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="p-3">
                                {{ $orders->links() }}
                            </div>

                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-box-open fa-4x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada pesanan</p>
                                <a href="{{ route('guest-katalog') }}" class="btn btn-primary">Belanja Sekarang</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT PROFIL -->
    <div class="modal fade" id="editProfile" tabindex="-1">
        <div class="modal-dialog">
<<<<<<< HEAD
            <form action="{{ route('customer.profile.update') }}" method="POST">
=======
            <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data">
>>>>>>> hans
                @csrf @method('PATCH')

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
<<<<<<< HEAD
=======
                        <div class="mb-3 text-center">
                            @if (Auth::user()->profile_picture_path)
                                <img src="{{ asset('storage/' . Auth::user()->profile_picture_path) }}" alt="Foto Profil" class="rounded-circle mb-2" width="80" height="80" style="object-fit: cover;">
                            @else
                                <i class="fa fa-user-circle fa-5x text-muted mb-2"></i>
                            @endif
                            <input type="file" name="profile_picture" class="form-control form-control-sm">
                            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah foto.</small>
                        </div>
>>>>>>> hans
                        <div class="mb-3">
                            <label>Nama Lengkap</label>
                            <input name="name" class="form-control" value="{{ Auth::user()->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label>No. HP</label>
                            <input name="phone" class="form-control" value="{{ Auth::user()->phone }}" required>
                        </div>

                        <div class="mb-3">
                            <label>Alamat</label>
                            <textarea name="address" rows="4" class="form-control"
                                required>{{ Auth::user()->address }}</textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<<<<<<< HEAD
@endsection
=======
@endsection
>>>>>>> hans
