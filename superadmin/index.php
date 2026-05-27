    <?php
    require_once '../includes/auth.php';
    redirectIfNotLoggedIn();
    if(!hasRole('superadmin')) { header('Location: ../dashboard.php'); exit; }
    $currentUser = getCurrentUser();
    $role = $currentUser['nama_role'];
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Superadmin - Manajemen RT</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <style>
            body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
            .sidebar { background: #1e293b; min-height: 100vh; }
            .sidebar .nav-link { color: #cbd5e1; padding: 12px 20px; border-radius: 10px; margin: 4px 8px; }
.sidebar .nav-link:hover {
    background: #334155;
    color: white;
}
.sidebar .nav-link.active {
    background: linear-gradient(90deg, #4f46e5, #7c3aed);
    color: white;
    box-shadow: 0 4px 10px rgba(79,70,229,0.3);
}
            .sidebar .nav-link i { width: 24px; margin-right: 10px; }
            .navbar-top { background: white; padding: 12px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 20px; border-radius: 0 0 12px 12px; }
            .content-wrapper { padding: 0 24px 24px 24px; }
            .footer { background: white; border-top: 1px solid #e2e8f0; padding: 15px 0; text-align: center; font-size: 14px; color: #64748b; margin-top: 30px; }
            .loading { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center; display: none; }
        </style>
    </head>
    <body>
    <div class="loading" id="loading"><div class="spinner-border text-light"></div></div>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-auto sidebar" style="width: 280px;">
                <div class="p-3">
                    <h4 class="text-white mb-4"><i class="fas fa-building"></i> Manajemen RT</h4>
                    <hr class="text-secondary">
                    <nav class="nav flex-column" id="sidebar-nav">
                        <a href="#" data-view="dashboard" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        <a href="#" data-view="kelola_akun" class="nav-link"><i class="fas fa-users"></i> Kelola Akun</a>
                        <a href="#" data-view="approve_pembayaran" class="nav-link"><i class="fas fa-check-double"></i> Approve Pembayaran</a>
                        <a href="#" data-view="data_rt" class="nav-link"><i class="fas fa-home"></i> Data RT</a>
                        <a href="#" data-view="kelola_iuran" class="nav-link"><i class="fas fa-money-bill"></i> Iuran</a>
                        <a href="#" data-view="periode" class="nav-link"><i class="fas fa-calendar-alt"></i> Periode</a>
                        <a href="#" data-view="transaksi" class="nav-link"><i class="fas fa-exchange-alt"></i> Transaksi</a>
                        <a href="#" data-view="pengeluaran" class="nav-link"><i class="fas fa-chart-line"></i> Pengeluaran</a>
                        <a href="#" data-view="laporan" class="nav-link"><i class="fas fa-print"></i> Laporan</a>
                        <a href="#" data-view="backup" class="nav-link"><i class="fas fa-database"></i> Backup</a>
                        <a href="#" data-view="log_aktivitas" class="nav-link"><i class="fas fa-history"></i> Log Aktivitas</a>
                        <a href="#" data-view="pengaturan" class="nav-link"><i class="fas fa-cog"></i> Pengaturan</a>
                        <a href="#" data-view="generate_tagihan" class="nav-link"><i class="fas fa-file-invoice"></i> Generate Tagihan</a>
                        <hr class="text-secondary">
                        <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </nav>
                </div>
            </div>
            <div class="col">
                <div class="navbar-top d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" id="pageTitle">Dashboard</h5>
                    <div><i class="fas fa-user-circle"></i> <?= htmlspecialchars($currentUser['fullname']) ?> <span class="badge bg-secondary ms-2"><?= ucfirst($role) ?></span></div>
                </div>
                <div class="content-wrapper" id="mainContent"><div class="text-center p-5">Memuat...</div></div>
                <div class="footer">&copy; <?= date('Y') ?> Manajemen RT - All rights reserved.</div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
    $(document).ready(function() {
        function loadView(view) {
            $('#loading').show();
            $.ajax({
                url: 'views/' + view + '.php',
                type: 'GET',
                success: function(response) {
                    $('#mainContent').html(response);
                    // Inisialisasi DataTables dengan bahasa inline (tanpa CORS)
                    $('.datatable').DataTable({
                        language: {
                            "decimal": "",
                            "emptyTable": "Tidak ada data",
                            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                            "infoFiltered": "(disaring dari _MAX_ total entri)",
                            "lengthMenu": "Tampilkan _MENU_ entri",
                            "loadingRecords": "Memuat...",
                            "processing": "Memproses...",
                            "search": "Cari:",
                            "zeroRecords": "Tidak ada data yang cocok",
                            "paginate": {
                                "first": "Pertama",
                                "last": "Terakhir",
                                "next": "Selanjutnya",
                                "previous": "Sebelumnya"
                            }
                        },
                        pageLength: 10,
                        destroy: true
                    });
                    let title = $('.card-header:first').text() || view.replace(/_/g, ' ');
                    $('#pageTitle').text(title.charAt(0).toUpperCase() + title.slice(1));
                },
                error: function() {
                    $('#mainContent').html('<div class="alert alert-danger">Gagal memuat halaman. Pastikan file views/' + view + '.php ada.</div>');
                },
                complete: function() {
                    $('#loading').hide();
                }
            });
        }
        $('#sidebar-nav a[data-view]').click(function(e) {
            e.preventDefault();
            let view = $(this).data('view');
            $('#sidebar-nav a').removeClass('active');
            $(this).addClass('active');
            loadView(view);
            history.pushState({ view: view }, '', '?view=' + view);
        });
        window.onpopstate = function(event) {
            if (event.state && event.state.view) {
                loadView(event.state.view);
                $('#sidebar-nav a[data-view="'+event.state.view+'"]').addClass('active').siblings().removeClass('active');
            } else {
                loadView('dashboard');
                $('#sidebar-nav a[data-view="dashboard"]').addClass('active').siblings().removeClass('active');
            }
        };
        let initialView = new URLSearchParams(window.location.search).get('view') || 'dashboard';
        loadView(initialView);
        $('#sidebar-nav a[data-view="'+initialView+'"]').addClass('active').siblings().removeClass('active');
    });
    </script>
    </body>
    </html>