@extends('pengerajin.layouts.pengerajin')

@section('content')
<div class="content-wrapper">

    <h2 class="mb-4">Profil Anda</h2>

    <div class="container-box">

        <div class="row mb-3">
            <div class="col-sm-3 fw-bold">Kode Pengerajin</div>
            <div class="col-sm-9">{{ $pengerajin->kode_pengerajin }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-3 fw-bold">Nama</div>
            <div class="col-sm-9">{{ $pengerajin->nama_pengerajin }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-3 fw-bold">Jenis Kelamin</div>
            <div class="col-sm-9">{{ $pengerajin->jk_pengerajin }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-3 fw-bold">Usia</div>
            <div class="col-sm-9">{{ $pengerajin->usia_pengerajin }} tahun</div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-3 fw-bold">Telepon</div>
            <div class="col-sm-9">{{ $pengerajin->telp_pengerajin }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-3 fw-bold">Email</div>
            <div class="col-sm-9">{{ $pengerajin->email_pengerajin }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-3 fw-bold">Alamat</div>
            <div class="col-sm-9">{{ $pengerajin->alamat_pengerajin }}</div>
        </div>

    </div>

</div>
@endsection
