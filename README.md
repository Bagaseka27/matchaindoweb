<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## API POS dan Stok Cup

- `GET /api/menus` menyertakan daftar harga per ukuran cup (`harga`) untuk katalog kasir mobile.
- Dashboard menyediakan `/stok-cup` untuk monitoring seluruh cabang, restock, dan rekap mutasi; `/transaksi` menampilkan transaksi penjualan Barista.

Endpoint berikut membutuhkan token Sanctum melalui header `Authorization: Bearer <token>`.

- `GET /api/stok/cup` menampilkan stok cup cabang pengguna. Pemilik tanpa cabang dapat memilih cabang melalui `?cabang=<id_cabang>`. Stok cup yang belum dicatat ditampilkan sebagai `0`.
- `GET /api/transaksi/riwayat` menampilkan maksimal 100 transaksi terbaru beserta detail item pada cabang pengguna. Barista hanya dapat melihat cabangnya.
- `POST /api/transaksi/checkout` menyimpan transaksi, detail item, mutasi pemakaian cup, dan pengurangan stok dalam satu transaksi database.

Contoh checkout:

```json
{
  "items": [
    { "id_menu": 1, "id_cup": 2, "jumlah": 2 }
  ],
  "metode_bayar": "tunai",
  "uang_diterima": 50000
}
```

Gunakan `metode_bayar: "qris"` untuk pembayaran QRIS; `uang_diterima` tidak diperlukan. Harga dihitung dari `harga_menu`, bukan dari input client. Item dengan cup berukuran `0 ml` tidak mengurangi stok cup. Checkout mengembalikan `201` saat berhasil dan `422` jika pilihan harga tidak tersedia, pembayaran tunai kurang, atau stok cup tidak mencukupi. Stok cup disimpan per cabang dan jenis cup pada tabel `stok_cup`, sedangkan perubahannya dicatat pada `mutasi_cup`.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
