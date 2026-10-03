@extends('layouts.app')
@section('title', 'Edit Menu')

@section('content')
<h4 class="mb-3">Edit Menu</h4>
<div class="card shadow-sm"><div class="card-body">
    <form action="{{ route('menus.update', $menu) }}" method="POST" enctype="multipart/form-data">
        @method('PUT')
        @include('menus._form')
    </form>
</div></div>
@endsection
