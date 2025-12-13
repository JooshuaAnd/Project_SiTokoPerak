<?php
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\RoleCheck;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PengerajinController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\KategoriProdukController;
use App\Http\Controllers\Admin\FotoProdukController;
use App\Http\Controllers\Admin\UsahaController;
use App\Http\Controllers\Admin\JenisUsahaController;
use App\Http\Controllers\Admin\UsahaPengerajinController;
use App\Http\Controllers\Admin\UsahaJenisController;
use App\Http\Controllers\Admin\UsahaProdukController;
use App\Http\Controllers\Guest\PageController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Guest\CartController;
use App\Http\Controllers\Guest\CheckoutController;
use App\Http\Controllers\Guest\CustomerDashboardController;
use App\Http\Controllers\Guest\ReviewController;
use App\Http\Controllers\ProdukLikeController;
use App\Http\Controllers\ProdukViewController;
use App\Http\Controllers\Admin\LaporanUsahaController;
use App\Http\Controllers\Pengerajin\DashboardPengerajinController;
use App\Http\Controllers\Pengerajin\ProfilePengerajinController;
use App\Http\Controllers\Pengerajin\ProdukPengerajinController;



// Authentication Routes
Route::get("login", [AuthController::class, "showLoginForm"])->name(
    "loginForm",
);
Route::post("login", [AuthController::class, "login"])->name("login");

// Registrasi Customer
Route::get("/register", [AuthController::class, "showRegisterForm"])->name(
    "register",
);
Route::post("/register", [AuthController::class, "register"]);

//cart detail
Route::get("/cart", [CartController::class, "index"])->name("cart.index");
Route::post("/cart/add/{slug}", [CartController::class, "add"])->name(
    "cart.add",
);
Route::post("/cart/update", [CartController::class, "update"])->name(
    "cart.update",
);
Route::delete("/cart/remove/{id}", [CartController::class, "remove"])->name(
    "cart.remove",
);
Route::post("/cart/clear", [CartController::class, "clear"])->name(
    "cart.clear",
);

//Guest
Route::get("/", [PageController::class, "index"])->name("guest-index");
Route::get("about", [PageController::class, "about"])->name("guest-about");
Route::get("contact", [PageController::class, "contact"])->name(
    "guest-contact",
);
Route::get("products", [PageController::class, "products"])->name(
    "guest-products",
);
Route::get("single-product", [PageController::class, "singleProduct"])->name(
    "guest-single-product",
);
Route::get("katalog", [PageController::class, "katalog"])->name(
    "guest-katalog",
);
Route::get("detail-usaha/{usaha}", [
    PageController::class,
    "detailUsaha",
])->name("guest-detail-usaha");
Route::get("produk/kategori/{slug}", [
    PageController::class,
    "productsByCategory",
])->name("guest-productsByCategory");
Route::get("produk/{slug}", [PageController::class, "singleProduct"])->name(
    "guest-singleProduct",
);

Route::post("logout", [AuthController::class, "logout"])->name("logout");
Route::post('/produk/{produkId}/like', [ProdukLikeController::class, 'toggleLike']);
Route::post('/produk/{produkId}/view', [ProdukViewController::class, 'store']);
//Checkout
Route::middleware("auth")->group(function () {
    Route::post("/reviews", [ReviewController::class, "store"])->name(
        "reviews.store",
    );
    Route::get("/checkout", [CheckoutController::class, "index"])->name(
        "checkout.index",
    );
    Route::post("/checkout", [CheckoutController::class, "store"])->name(
        "checkout.store",
    );
    Route::get("/checkout/success/{orderNumber}", [
        CheckoutController::class,
        "success",
    ])->name("checkout.success");
});

//Checkout
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');
});

//buat Dashboard customer
Route::middleware('auth')->name('customer.')->group(function () {

    // Dashboard Home → /customer/dashboard
    Route::get('/customer/dashboard', [CustomerDashboardController::class, 'index'])
        ->name('dashboard');
    // Detail pesanan → /customer/orders/123
    Route::get('/customer/orders/{order}', [CustomerDashboardController::class, 'orderDetail'])
        ->name('order.detail');
    // Update profil
    Route::patch('/customer/profile', [CustomerDashboardController::class, 'updateProfile'])
        ->name('profile.update');
});

//buat Dashboard customer
Route::middleware("auth")
    ->name("customer.")
    ->group(function () {
        // Dashboard Home → /customer/dashboard
        Route::get("/customer/dashboard", [
            CustomerDashboardController::class,
            "index",
        ])->name("dashboard");
        // Detail pesanan → /customer/orders/123
        Route::get("/customer/orders/{order}", [
            CustomerDashboardController::class,
            "orderDetail",
        ])->name("order.detail");
        // Update profil
        Route::patch("/customer/profile", [
            CustomerDashboardController::class,
            "updateProfile",
        ])->name("profile.update");
    });

Route::group([
    'prefix' => 'pengerajin',
    'as' => 'pengerajin.',
    'middleware' => ['auth', 'role:pengerajin'],
], function () {

    Route::get('/dashboard', [DashboardPengerajinController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('/profile', [ProfilePengerajinController::class, 'profile'])
        ->name('profile');

    Route::get('/produk', [ProdukPengerajinController::class, 'produk'])
        ->name('produk');

    Route::get('/produk-all', [ProdukPengerajinController::class, 'produk_all'])
        ->name('produk-all');
});
//crud produk pengerajin
Route::get('pengerajin/produk/create', [ProdukPengerajinController::class, 'create'])->name('pengerajin.produk.create');
Route::post('pengerajin/produk/store', [ProdukPengerajinController::class, 'store'])->name('pengerajin.produk.store');
Route::get('pengerajin/produk/edit/{id}', [ProdukPengerajinController::class, 'edit'])->name('pengerajin.produk.edit');
Route::put('pengerajin/produk/update/{id}', [ProdukPengerajinController::class, 'update'])->name('pengerajin.produk.update');
Route::delete('pengerajin/produk/delete/{id}', [ProdukPengerajinController::class, 'delete'])->name('pengerajin.produk.delete');

Route::middleware(["role:admin"])->group(function () {
    Route::get("admin/profile", [AuthController::class, "profile"])->name(
        "profile",
    );
    Route::get("admin/change-password", [
        AuthController::class,
        "changePassword",
    ])->name("change-password");
    Route::post("update-password", [
        AuthController::class,
        "updatePassword",
    ])->name("update-password");

    // Dashboard
    Route::get("admin/dashboard", [DashboardController::class, "index"])->name(
        "admin.dashboard",
    );

    // Pengerajin
    Route::get("admin/pengerajin", [
        PengerajinController::class,
        "index",
    ])->name("admin.pengerajin-index");
    Route::get("admin/pengerajin/create", [
        PengerajinController::class,
        "create",
    ])->name("admin.pengerajin-create");
    Route::post("admin/pengerajin/store", [
        PengerajinController::class,
        "store",
    ])->name("admin.pengerajin-store");
    Route::get("admin/pengerajin/edit/{id}", [
        PengerajinController::class,
        "edit",
    ])->name("admin.pengerajin-edit");
    Route::put("admin/pengerajin/update/{id}", [
        PengerajinController::class,
        "update",
    ])->name("admin.pengerajin-update");
    Route::delete("admin/pengerajin/destroy/{id}", [
        PengerajinController::class,
        "destroy",
    ])->name("admin.pengerajin-destroy");

    // Usaha
    Route::get("admin/usaha", [UsahaController::class, "index"])->name(
        "admin.usaha-index",
    );
    Route::get("admin/usaha/create", [UsahaController::class, "create"])->name(
        "admin.usaha-create",
    );
    Route::post("admin/usaha/store", [UsahaController::class, "store"])->name(
        "admin.usaha-store",
    );
    Route::get("admin/usaha/edit/{id}", [UsahaController::class, "edit"])->name(
        "admin.usaha-edit",
    );
    Route::put("admin/usaha/update/{id}", [
        UsahaController::class,
        "update",
    ])->name("admin.usaha-update");
    Route::delete("admin/usaha/destroy/{id}", [
        UsahaController::class,
        "destroy",
    ])->name("admin.usaha-destroy");

    // Jenis Usaha
    Route::get("admin/jenis-usaha", [
        JenisUsahaController::class,
        "index",
    ])->name("admin.jenis_usaha-index");
    Route::get("admin/jenis-usaha/create", [
        JenisUsahaController::class,
        "create",
    ])->name("admin.jenis_usaha-create");
    Route::post("admin/jenis-usaha/store", [
        JenisUsahaController::class,
        "store",
    ])->name("admin.jenis_usaha-store");
    Route::get("admin/jenis-usaha/edit/{id}", [
        JenisUsahaController::class,
        "edit",
    ])->name("admin.jenis_usaha-edit");
    Route::put("admin/jenis-usaha/update/{id}", [
        JenisUsahaController::class,
        "update",
    ])->name("admin.jenis_usaha-update");
    Route::delete("admin/jenis-usaha/destroy/{id}", [
        JenisUsahaController::class,
        "destroy",
    ])->name("admin.jenis_usaha-destroy");

    // Kategori Produk
    Route::get("admin/kategori-produk", [
        KategoriProdukController::class,
        "index",
    ])->name("admin.kategori_produk-index");
    Route::get("admin/kategori-produk/create", [
        KategoriProdukController::class,
        "create",
    ])->name("admin.kategori_produk-create");
    Route::post("admin/kategori-produk/store", [
        KategoriProdukController::class,
        "store",
    ])->name("admin.kategori_produk-store");
    Route::get("admin/kategori-produk/edit/{id}", [
        KategoriProdukController::class,
        "edit",
    ])->name("admin.kategori_produk-edit");
    Route::put("admin/kategori-produk/update/{id}", [
        KategoriProdukController::class,
        "update",
    ])->name("admin.kategori_produk-update");
    Route::delete("admin/kategori-produk/destroy/{id}", [
        KategoriProdukController::class,
        "destroy",
    ])->name("admin.kategori_produk-destroy");

    // Produk
    Route::get("admin/produk", [ProdukController::class, "index"])->name(
        "admin.produk-index",
    );
    Route::get("admin/produk/create", [
        ProdukController::class,
        "create",
    ])->name("admin.produk-create");
    Route::get('admin/get-produk-by-usaha/{usahaId}', [UsahaProdukController::class, 'getProdukByUsaha'])->name('admin.get-produk-by-usaha');
    Route::post("admin/produk/store", [ProdukController::class, "store"])->name(
        "admin.produk-store",
    );
    Route::get("admin/produk/edit/{id}", [
        ProdukController::class,
        "edit",
    ])->name("admin.produk-edit");
    Route::put("admin/produk/update/{id}", [
        ProdukController::class,
        "update",
    ])->name("admin.produk-update");
    Route::delete("admin/produk/destroy/{id}", [
        ProdukController::class,
        "destroy",
    ])->name("admin.produk-destroy");

    // Foto Produk
    Route::get("admin/foto-produk", [
        FotoProdukController::class,
        "index",
    ])->name("admin.foto_produk-index");
    Route::get("admin/foto-produk/create", [
        FotoProdukController::class,
        "create",
    ])->name("admin.foto_produk-create");
    Route::post("admin/foto-produk/store", [
        FotoProdukController::class,
        "store",
    ])->name("admin.foto_produk-store");
    Route::get("admin/foto-produk/edit/{id}", [
        FotoProdukController::class,
        "edit",
    ])->name("admin.foto_produk-edit");
    Route::put("admin/foto-produk/update/{id}", [
        FotoProdukController::class,
        "update",
    ])->name("admin.foto_produk-update");
    Route::delete("admin/foto-produk/destroy/{id}", [
        FotoProdukController::class,
        "destroy",
    ])->name("admin.foto_produk-destroy");

    // Usaha Pengerajin
    Route::get("admin/usaha-pengerajin", [
        UsahaPengerajinController::class,
        "index",
    ])->name("admin.usaha_pengerajin-index");
    Route::get("admin/usaha-pengerajin/create", [
        UsahaPengerajinController::class,
        "create",
    ])->name("admin.usaha_pengerajin-create");
    Route::post("admin/usaha-pengerajin/store", [
        UsahaPengerajinController::class,
        "store",
    ])->name("admin.usaha_pengerajin-store");
    Route::get("admin/usaha-pengerajin/edit/{id}", [
        UsahaPengerajinController::class,
        "edit",
    ])->name("admin.usaha_pengerajin-edit");
    Route::put("admin/usaha-pengerajin/update/{id}", [
        UsahaPengerajinController::class,
        "update",
    ])->name("admin.usaha_pengerajin-update");
    Route::delete("admin/usaha-pengerajin/destroy/{id}", [
        UsahaPengerajinController::class,
        "destroy",
    ])->name("admin.usaha_pengerajin-destroy");

    // Usaha Jenis
    Route::get("admin/usaha-jenis", [
        UsahaJenisController::class,
        "index",
    ])->name("admin.usaha_jenis-index");
    Route::get("admin/usaha-jenis/create", [
        UsahaJenisController::class,
        "create",
    ])->name("admin.usaha_jenis-create");
    Route::post("admin/usaha-jenis/store", [
        UsahaJenisController::class,
        "store",
    ])->name("admin.usaha_jenis-store");
    Route::get("admin/usaha-jenis/edit/{id}", [
        UsahaJenisController::class,
        "edit",
    ])->name("admin.usaha_jenis-edit");
    Route::put("admin/usaha-jenis/update/{id}", [
        UsahaJenisController::class,
        "update",
    ])->name("admin.usaha_jenis-update");
    Route::delete("admin/usaha-jenis/destroy/{id}", [
        UsahaJenisController::class,
        "destroy",
    ])->name("admin.usaha_jenis-destroy");

    // Usaha Produk
    Route::get("admin/usaha-produk", [
        UsahaProdukController::class,
        "index",
    ])->name("admin.usaha_produk-index");
    Route::get("admin/usaha-produk/create", [
        UsahaProdukController::class,
        "create",
    ])->name("admin.usaha_produk-create");
    Route::post("admin/usaha-produk/store", [
        UsahaProdukController::class,
        "store",
    ])->name("admin.usaha_produk-store");
    Route::get("admin/usaha-produk/edit/{id}", [
        UsahaProdukController::class,
        "edit",
    ])->name("admin.usaha_produk-edit");
    Route::put("admin/usaha-produk/update/{id}", [
        UsahaProdukController::class,
        "update",
    ])->name("admin.usaha_produk-update");
    Route::delete("admin/usaha-produk/destroy/{id}", [
        UsahaProdukController::class,
        "destroy",
    ])->name("admin.usaha_produk-destroy");


    Route::prefix('admin/laporan-usaha')
        ->name('admin.laporan_usaha.')
        // ->middleware(['auth', 'is_admin']) // kalau kamu pakai middleware admin, tambahkan di sini
        ->group(function () {

            // Dashboard laporan utama
            Route::get('/', [LaporanUsahaController::class, 'index'])
                ->name('index');

            // Kategori Produk
            Route::get('/kategori-produk', [App\Http\Controllers\Export\LaporanController::class, 'kategoriProduk'])
                ->name('kategori-produk');
            Route::get('/kategori-produk/export', [App\Http\Controllers\Export\LaporanController::class, 'exportKategoriProduk'])
                ->name('kategori-produk.export');

            // Pendapatan Usaha
            Route::get('/pendapatan-usaha', [App\Http\Controllers\Export\LaporanController::class, 'pendapatanUsaha'])
                ->name('pendapatan-usaha');
            Route::get('/pendapatan-usaha/export', [App\Http\Controllers\Export\LaporanController::class, 'exportPendapatanUsaha'])
                ->name('pendapatan-usaha.export');

            // Produk Favorite
            Route::get('/produk-favorite', [App\Http\Controllers\Export\LaporanController::class, 'produkFavorite'])
                ->name('produk-favorite');
            Route::get('/produk-favorite/export', [App\Http\Controllers\Export\LaporanController::class, 'exportProdukFavorite'])
                ->name('produk-favorite.export');

            // Produk Slow Moving
            Route::get('/produk-slow-moving', [App\Http\Controllers\Export\LaporanController::class, 'produkSlowMoving'])
                ->name('produk-slow-moving');
            Route::get('/produk-slow-moving/export', [App\Http\Controllers\Export\LaporanController::class, 'exportProdukSlowMoving'])
                ->name('produk-slow-moving.export');

            // Produk Terlaris
            Route::get('/produk-terlaris', [App\Http\Controllers\Export\LaporanController::class, 'produkTerlaris'])
                ->name('produk_terlaris');
            Route::get('/produk-terlaris/export', [App\Http\Controllers\Export\LaporanController::class, 'exportProdukTerlaris'])
                ->name('produk-terlaris.export');

            // Produk Views
            Route::get('/produk-views', [App\Http\Controllers\Export\LaporanController::class, 'produkViews'])
                ->name('produk-views');
            Route::get('/produk-views/export', [App\Http\Controllers\Export\LaporanController::class, 'exportProdukViews'])
                ->name('produk-views.export');

            // Transaksi per User
            Route::get('/transaksi-user', [App\Http\Controllers\Export\LaporanController::class, 'transaksiUser'])
                ->name('transaksi-user');
            Route::get('/transaksi-user/export', [App\Http\Controllers\Export\LaporanController::class, 'exportTransaksiUser'])
                ->name('transaksi-user.export');

            // Semua Transaksi
            Route::get('/transaksi', [App\Http\Controllers\Export\LaporanController::class, 'transaksi'])
                ->name('transaksi');
            Route::get('/transaksi/export', [App\Http\Controllers\Export\LaporanController::class, 'exportTransaksi'])
                ->name('transaksi.export');

            // // Export Pengerajin
            // Route::get('/pengerajin/export', [LaporanUsahaController::class, 'exportPengerajin'])
            //     ->name('pengerajin.export');
        });
    Route::get("admin/export-data", [ExportController::class, "index"])->name(
        "admin.export-data",
    );

    // Route::get("admin/export-pengerajin", [
    //     ExportController::class,
    //     "exportPengerajin",
    // ])->name("admin.export-pengerajin");
    // Produk Pending Approval
    Route::get('admin/produk-pending', [ProdukController::class, 'pending'])->name('admin.produk-pending');
    Route::patch('admin/produk/{id}/approve', [ProdukController::class, 'approve'])->name('admin.produk-approve');
    Route::patch('admin/produk/{id}/reject', [ProdukController::class, 'reject'])->name('admin.produk-reject');

});


