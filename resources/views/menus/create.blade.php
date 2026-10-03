@extends('layouts.app')
@section('title', 'Tambah Menu')

@section('content')
<h4 class="mb-3">Tambah Menu</h4>
<div class="card shadow-sm"><div class="card-body">
    <form action="{{ route('menus.store') }}" method="POST" enctype="multipart/form-data">
        @include('menus._form')
    </form>
</div></div>
@endsection
