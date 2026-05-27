// Animasi modern untuk semua halaman
document.addEventListener('DOMContentLoaded', function() {
    // Efek fade-in untuk konten
    const content = document.querySelector('.content-wrapper');
    if(content) content.style.opacity = '0';
    setTimeout(() => {
        if(content) content.style.transition = 'opacity 0.5s';
        if(content) content.style.opacity = '1';
    }, 100);

    // Tooltip otomatis (bootstrap)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Konfirmasi hapus dengan modal modern
    const deleteButtons = document.querySelectorAll('.btn-delete-confirm');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            if(!confirm('Apakah Anda yakin? Data akan dihapus permanen.')) {
                e.preventDefault();
            }
        });
    });
});

// Fungsi notifikasi modern (panggil dengan showNotification('Pesan', 'success'))
function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = 'toast-notify';
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
}