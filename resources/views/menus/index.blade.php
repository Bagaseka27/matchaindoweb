@extends('layouts.app')
@section('title', 'Daftar Menu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Daftar Menu</h4>
    <a href="{{ route('menus.create') }}" class="btn btn-primary">+ Tambah Menu</a>
</div>

<form method="GET" class="mb-3">
    <div class="input-group">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama menu...">
        <button class="btn btn-outline-secondary">Cari</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($menus as $menu)
                <tr>
                    <td>
                        @if ($menu->image_url)
                            <img src="{{ $menu->image_url }}" width="56" height="56" class="rounded object-fit-cover" alt="{{ $menu->name }}">
                        @else
                            <div class="bg-secondary-subtle rounded" style="width:56px;height:56px"></div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $menu->name }}</strong><br>
                        <small class="text-muted">{{ Str::limit($menu->description, 50) }}</small>
                    </td>
                    <td>{{ $menu->category }}</td>
                    <td>Rp {{ number_format($menu->price, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge {{ $menu->is_available ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $menu->is_available ? 'Tersedia' : 'Habis' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('menus.edit', $menu) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('menus.destroy', $menu) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus menu {{ $menu->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada menu.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $menus->links() }}</div>
@endsection
