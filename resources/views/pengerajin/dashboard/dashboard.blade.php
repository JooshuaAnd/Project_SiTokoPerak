@extends('pengerajin.layouts.pengerajin')

@section('content')
<h2>Dashboard</h2>
<p>Selamat datang di dashboard pengerajin.</p>

<div class="row">
    @foreach($produk as $item)
        {{-- card produk --}}
    @endforeach
</div>
@endsection
