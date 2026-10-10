@extends('layouts.app')

@section('title', 'Edit Bahan')

@section('content')
@php
    $input = 'w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2';
    $ok    = 'border-slate-200 focus:border-teal-500 focus:ring-teal-500/20';
    $bad   = 'border-red-400 focus:border-red-500 focus:ring-red-500/20';
@endphp

<div class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('stok.update', [$stok->id_cabang, $stok->id_bahan]) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Cabang</label>
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-600">
                {{ $stok->cabang->nama_cabang }}
            </div>
        </div>

        <div>
            <label for="nama_bahan" class="mb-1.5 block text-sm font-semibold text-slate-700">Nama Bahan</label>
            <input id="nama_bahan" type="text" name="nama_bahan" value="{{ old('nama_bahan', $stok->bahan->nama_bahan) }}" maxlength="100"
                   class="{{ $input }} {{ $errors->has('nama_bahan') ? $bad : $ok }}">
            @error('nama_bahan') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="satuan" class="mb-1.5 block text-sm font-semibold text-slate-700">Satuan</label>
            <select id="satuan" name="satuan" class="{{ $input }} {{ $errors->has('satuan') ? $bad : $ok }}">
                @foreach ($daftarSatuan as $satuan)
                    <option value="{{ $satuan }}" @selected(old('satuan', $stok->bahan->satuan) === $satuan)>{{ $satuan }}</option>
                @endforeach
            </select>
            @error('satuan') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-slate-400">Perubahan nama dan satuan berlaku untuk bahan ini di semua cabang.</p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="jumlah_stok" class="mb-1.5 block text-sm font-semibold text-slate-700">Stok</label>
                <input id="jumlah_stok" type="number" name="jumlah_stok"
                        value="{{ old('jumlah_stok', (float) $stok->jumlah_stok) }}"
                        min="0" step="0.01" inputmode="decimal"
                        class="{{ $input }} {{ $errors->has('jumlah_stok') ? $bad : $ok }}">
                @error('jumlah_stok') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="stok_minimum" class="mb-1.5 block text-sm font-semibold text-slate-700">Batas Minimal</label>
                <input id="stok_minimum" type="number" name="stok_minimum"
                        value="{{ old('stok_minimum', (float) $stok->stok_minimum) }}"
                        min="0" step="0.01" inputmode="decimal"
                        class="{{ $input }} {{ $errors->has('stok_minimum') ? $bad : $ok }}">
                @error('stok_minimum') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <p class="text-xs text-slate-400">
            Terakhir diperbarui: {{ $stok->diperbarui_pada?->format('d M Y, H:i') ?? '-' }}
        </p>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('stok.index', ['cabang' => $stok->id_cabang]) }}"
               class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Batal</a>
            <button type="submit"
                    class="bg-[#2F593E] text-white px-5 py-2 rounded-lg hover:bg-green-800 transition hover:bg-teal-700">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection