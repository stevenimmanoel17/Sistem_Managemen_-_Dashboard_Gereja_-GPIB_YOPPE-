// =========================================================================
// DEKLARASI VARIABEL GLOBAL & FUNGSI BANTU FORMAT
// =========================================================================

let chartArusKas = null;
let chartSurplus = null;
let chartDistribusiSektor = null;
let chartJiwaKk = null;

const labelBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

function formatRupiah(val) {
    return 'Rp ' + Number(val).toLocaleString('id-ID');
}

function hitungSurplusKumulatif(inArr, outArr) {
    let kumulatif = [];
    let berjalan = 0;
    for (let i = 0; i < 12; i++) {
        let selisih = (inArr[i] || 0) - (outArr[i] || 0);
        berjalan += selisih;
        kumulatif.push(berjalan);
    }
    return kumulatif;
}


// =========================================================================
// EVENT LISTENER UTAMA KETIKA DOM SIAP DIJALANKAN
// =========================================================================

document.addEventListener('DOMContentLoaded', function() {
    
    // --- KOMPONEN PENGATUR SIDEBAR & RESPONSIF GRAFIK ---
    const btnToggle = document.getElementById('sidebarToggle');
    if (btnToggle) {
        btnToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-closed');
            const isClosed = document.body.classList.contains('sidebar-closed');
            localStorage.setItem('sidebarStatus', isClosed ? 'closed' : 'open');

            setTimeout(() => {
                const charts = [chartArusKas, chartSurplus, chartDistribusiSektor, chartJiwaKk];
                charts.forEach(c => { if (c) c.resize(); });
            }, 320);
        });
    }

    if (localStorage.getItem('sidebarStatus') === 'closed') {
        document.body.classList.add('sidebar-closed');
    }

    // --- KOMPONEN KONFIRMASI LOGOUT DENGAN SWEETALERT2 ---
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Sesi Anda akan segera berakhir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2392ED',
                cancelButtonColor: '#ff4d4d',
                confirmButtonText: 'Ya, Keluar!',
                cancelButtonText: 'Batal'
            }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Keluar',
                    text: 'Tuhan Yesus Memberkati!',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                    const progressBar = toast.querySelector('.swal2-timer-progress-bar');
                    if (progressBar) {
                        progressBar.style.backgroundColor = '#2ecc71';
                    }
                }
                }).then(() => { window.location.href = 'logout.php'; });
            }
            });
        });
    }

    // --- PEMANGGILAN INISIALISASI SELURUH GRAFIK ---
    initAllCharts();
});


// =========================================================================
// KOMPONEN PEMBUATAN GRAFIK CHART.JS (DASHBOARD)
// =========================================================================

function initAllCharts() {
    const dataObj = window.DASHBOARD_DATA;
    if (!dataObj) return;

    let tahun = dataObj.tahunAktif;
    let dataKeuanganThn = dataObj.keuangan[tahun] || { in: Array(12).fill(0), out: Array(12).fill(0) };

    // --- GRAFIK TREN ARUS KAS BULANAN (GROUPED BAR CHART) ---
    const ctxArusKas = document.getElementById('arusKasChart');
    if (ctxArusKas) {
        chartArusKas = new Chart(ctxArusKas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelBulan,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: dataKeuanganThn.in,
                        backgroundColor: '#2392ED',
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Pengeluaran',
                        data: dataKeuanganThn.out,
                        backgroundColor: '#FF4D4D',
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { family: 'Poppins', size: 12, weight: 600 }, usePointStyle: true }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatRupiah(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(v) { return formatRupiah(v); },
                            font: { family: 'Poppins', size: 11 }
                        }
                    }
                }
            }
        });
    }

    // --- GRAFIK TREN SALDO BERSIH ATAU SURPLUS-DEFISIT (AREA CHART) ---
    const ctxSurplus = document.getElementById('surplusDefisitChart');
    if (ctxSurplus) {
        let netData = hitungSurplusKumulatif(dataKeuanganThn.in, dataKeuanganThn.out);

        chartSurplus = new Chart(ctxSurplus.getContext('2d'), {
            type: 'line',
            data: {
                labels: labelBulan,
                datasets: [{
                    label: 'Saldo Bersih Kumulatif',
                    data: netData,
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.15)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: function(context) {
                        const val = context.raw;
                        return val >= 0 ? '#2ecc71' : '#FF4D4D';
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1400,
                    easing: 'easeOutCubic'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const status = ctx.raw >= 0 ? 'Surplus' : 'Defisit';
                                return status + ': ' + formatRupiah(ctx.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        grid: {
                            color: function(ctx) {
                                return ctx.tick.value === 0 ? '#333' : '#eee';
                            },
                            lineWidth: function(ctx) {
                                return ctx.tick.value === 0 ? 1.5 : 1;
                            }
                        },
                        ticks: {
                            callback: function(v) { return formatRupiah(v); },
                            font: { family: 'Poppins', size: 11 }
                        }
                    }
                }
            }
        });
    }

    // --- GRAFIK DISTRIBUSI PERSENTASE WILAYAH SEKTOR (DONUT CHART) ---
    const ctxDistribusi = document.getElementById('distribusiSektorChart');
    if (ctxDistribusi) {
        const rawSektor = dataObj.kategoriSektor['Semua'];
        const values = [rawSektor['Galatia'] || 0, rawSektor['Filipi'] || 0, rawSektor['Yudea'] || 0];
        
        chartDistribusiSektor = new Chart(ctxDistribusi.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: dataObj.sektorList,
                datasets: [{
                    data: values,
                    backgroundColor: ['#2392ED', '#9B51E0', '#F2994A'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1200,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { font: { family: 'Poppins', size: 12 }, padding: 15, usePointStyle: true }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const dataset = ctx.dataset;
                                const total = dataset.data.reduce((a, b) => a + b, 0);
                                const current = ctx.raw;
                                const pct = total > 0 ? ((current / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${current} Jiwa (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // --- GRAFIK PERBANDINGAN TOTAL JIWA VS KEPALA KELUARGA (CLUSTERED COLUMN CHART) ---
    const ctxJiwaKk = document.getElementById('jiwaVsKkChart');
    if (ctxJiwaKk) {
        chartJiwaKk = new Chart(ctxJiwaKk.getContext('2d'), {
            type: 'bar',
            data: {
                labels: dataObj.sektorList,
                datasets: [
                    {
                        label: 'Total Jiwa',
                        data: dataObj.jiwaPerSektor,
                        backgroundColor: '#2392ED',
                        borderRadius: 6,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    },
                    {
                        label: 'Kepala Keluarga (KK)',
                        data: dataObj.kkPerSektor,
                        backgroundColor: '#2ecc71',
                        borderRadius: 6,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { family: 'Poppins', size: 12, weight: 600 }, usePointStyle: true }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ` ${ctx.dataset.label}: ${ctx.raw} orang`;
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { family: 'Poppins', size: 11 } }
                    }
                }
            }
        });
    }
}


// =========================================================================
// KOMPONEN PEMBARUAN DATA GRAFIK (FILTER INTERAKTIF)
// =========================================================================

function gantiTahunKeuangan(thn) {
    const dataObj = window.DASHBOARD_DATA;
    if (!dataObj || !dataObj.keuangan[thn]) return;

    const dataBaru = dataObj.keuangan[thn];

    // Update Bar Chart Arus Kas
    if (chartArusKas) {
        chartArusKas.data.datasets[0].data = dataBaru.in;
        chartArusKas.data.datasets[1].data = dataBaru.out;
        chartArusKas.update('active');
    }

    // Update Line Chart Surplus
    if (chartSurplus) {
        const netBaru = hitungSurplusKumulatif(dataBaru.in, dataBaru.out);
        chartSurplus.data.datasets[0].data = netBaru;
        chartSurplus.update('active');
    }

    const lbl = document.getElementById('labelTahunSurplus');
    if (lbl) lbl.innerText = thn;
}

function gantiKategoriSektor(kat) {
    const dataObj = window.DASHBOARD_DATA;
    if (!dataObj || !dataObj.kategoriSektor[kat]) return;

    const raw = dataObj.kategoriSektor[kat];
    const newVals = [raw['Galatia'] || 0, raw['Filipi'] || 0, raw['Yudea'] || 0];

    if (chartDistribusiSektor) {
        chartDistribusiSektor.data.datasets[0].data = newVals;
        chartDistribusiSektor.update();
    }
}


// =========================================================================
// KOMPONEN LOADING SCREEN & AUTO-SCROLL SIDEBAR
// =========================================================================

window.addEventListener('load', function() {
    const loader = document.getElementById('loading-screen');
    if (loader) {
        setTimeout(() => {
            loader.classList.add('loader-hidden');
        }, 500); 
    }

    const activeLink = document.querySelector('.nav-menu .nav-link.active'); 
    const sidebarMenu = document.querySelector('.nav-menu');

    if (activeLink && sidebarMenu) {
        sidebarMenu.scrollTop = activeLink.offsetTop - (sidebarMenu.clientHeight / 1) + (activeLink.clientHeight / 1);
    }
});


// =========================================================================
// KOMPONEN MODAL & KARTU INFORMASI PROFIL PENGGUNA
// =========================================================================

function toggleUserCard(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('userProfileDropdown');
    if (dropdown) dropdown.classList.toggle('show');
}

function closeUserCard(event) {
    if (event) event.stopPropagation();
    const dropdown = document.getElementById('userProfileDropdown');
    if (dropdown) dropdown.classList.remove('show');
}

window.addEventListener('click', function(e) {
    const dropdown = document.getElementById('userProfileDropdown');
    const infoMenu = document.getElementById('userInfoMenu');
    if (dropdown && dropdown.classList.contains('show') && infoMenu && !infoMenu.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});