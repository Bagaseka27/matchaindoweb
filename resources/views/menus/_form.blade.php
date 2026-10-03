@csrf
<div class="mb-3">
    <label class="form-label">Nama Menu</label>
    <input type="text" name="name" value="{{ old('name', $menu->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Kategori</label>
        <input type="text" name="category" list="categories" value="{{ old('category', $menu->category ?? '') }}"
               class="form-control @error('category') is-invalid @enderror">
        <datalist id="categories">
            <option value="Coffee"><option value="Non-Coffee"><option value="Snack"><option value="Dessert">
        </datalist>
        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Harga (Rp)</label>
        <input type="number" name="price" min="0" value="{{ old('price', $menu->price ?? '') }}"
               class="form-control @error('price') is-invalid @enderror">
        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $menu->description ?? '') }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Gambar (maks 2 MB)</label>
    <input type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
    @if (!empty($menu?->image_url))
        <img src="{{ $menu->image_url }}" width="100" class="rounded mt-2" alt="">
    @endif
</div>

<div class="form-check form-switch mb-4">
    <input type="checkbox" name="is_available" value="1" class="form-check-input" id="avail"
           @checked(old('is_available', $menu->is_available ?? true))>
    <label for="avail" class="form-check-label">Tersedia</label>
</div>

<button class="btn btn-primary">Simpan</button>
<a href="{{ route('menus.index') }}" class="btn btn-link">Batal</a>
