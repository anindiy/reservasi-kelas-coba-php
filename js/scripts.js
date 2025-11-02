// Validasi form
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const jamAwal = document.querySelector('select[name="jam_awal"]');
            const jamAkhir = document.querySelector('select[name="jam_akhir"]');
            
            if (jamAwal && jamAkhir && jamAwal.value && jamAkhir.value) {
                if (parseInt(jamAkhir.value) <= parseInt(jamAwal.value)) {
                    e.preventDefault();
                    alert('Jam akhir harus lebih besar dari jam awal');
                    return false;
                }
            }
        });
    }

    // Validasi tanggal untuk menghindari hari Minggu
    const tanggalInput = document.getElementById('tanggal');
    if (tanggalInput) {
        tanggalInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            if (selectedDate.getDay() === 0) { // 0 adalah hari Minggu
                alert('Tidak bisa memesan hari Minggu');
                this.value = '';
            }
        });
    }
});