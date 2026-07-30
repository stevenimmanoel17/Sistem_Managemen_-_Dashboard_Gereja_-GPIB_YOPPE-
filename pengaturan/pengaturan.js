// FUNGSI NAVIGASI TAB
function openTab(evt, tabName) {
    let i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) { 
        tabcontent[i].style.setProperty('display', 'none', 'important'); 
    }
    tablinks = document.getElementsByClassName("tab-btn");
    for (i = 0; i < tablinks.length; i++) { 
        tablinks[i].classList.remove("active"); 
    }
    document.getElementById(tabName).style.setProperty('display', 'block', 'important');
    evt.currentTarget.classList.add("active");
}

// --- LOGIKA TOMBOL REFRESH USER ASYNC ---
function jalankanPembaruanDataUser() {
    const btn = document.getElementById('btn-refresh-user');
    if (!btn) return;

    const icon = btn.querySelector('.icon-refresh-user');
    const tableBody = document.getElementById('userTableBody');
    
    // Jalankan animasi putar ikon & kunci klik tombol
    icon.style.animation = 'spinUser 1s linear infinite';
    btn.style.opacity = '0.7';
    btn.style.pointerEvents = 'none';

    // Beri efek buram loading dan pasang teks indikator permintaan data
    tableBody.style.opacity = '0.5';
    tableBody.innerHTML = `
        <tr>
            <td colspan="6" style="text-align: center; padding: 50px 0;">
                <div class="spinner-biru-user"></div>
                <p style="color: #666; font-weight: 600; font-size: 14px; margin: 0; font-family: 'Poppins', sans-serif;">Memperbarui Data User...</p>
            </td>
        </tr>
    `;

    // Panggil data real-time dengan query pengaman action bawaan pengaturan.php
    fetch('pengaturan.php?action=refresh_table')
        .then(response => response.text())
        .then(html => {
            setTimeout(() => {
                // Masukkan data baris user yang baru dari database
                tableBody.innerHTML = html;
                
                // Kembalikan tampilan ke keadaan normal semula
                tableBody.style.opacity = '1';
                icon.style.animation = 'none';
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
            }, 600);
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#e74c3c; font-weight:600; padding:20px;">Gagal memuat ulang data akun user.</td></tr>`;
            tableBody.style.opacity = '1';
            icon.style.animation = 'none';
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        });
}

// Daftarkan fungsi klik ke tombol HTML saat dokumen selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
    const btnRefreshUser = document.getElementById('btn-refresh-user');
    if (btnRefreshUser) {
        btnRefreshUser.addEventListener('click', jalankanPembaruanDataUser);
    }
});

// FUNGSI MODAL EDIT PENGGUNA
async function openEditModal(id, nama, user, email, role, status) {
    const { value: formValues } = await Swal.fire({
        title: '<span style="font-family: Poppins; font-weight: 700; color: #333;">Edit Pengguna</span>',
        html: `
            <input id="edit-id" type="hidden" value="${id}">
            <div style="text-align: left; padding: 10px 20px; font-family: Poppins;">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #777; margin-bottom: 8px;">Nama Lengkap</label>
                    <input id="edit-nama" class="swal2-input" value="${nama}" style="width: 100%; margin: 0; height: 48px; font-size: 14px; border-radius: 12px; border: 1px solid #ddd; background-color: #f8f9fa; color: #444; padding: 0 15px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #777; margin-bottom: 8px;">Email</label>
                    <input id="edit-email" class="swal2-input" value="${email}" style="width: 100%; margin: 0; height: 48px; font-size: 14px; border-radius: 12px; border: 1px solid #ddd; background-color: #f8f9fa; color: #444; padding: 0 15px; box-sizing: border-box;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #777; margin-bottom: 8px;">Role</label>
                        <div class="custom-dropdown-modern" id="dropdownRoleEdit">
                            <div class="dropdown-selected-modern" onclick="toggleEditDropdown('roleOptionsEdit', 'dropdownRoleEdit')">
                                <span id="roleTextEdit">${role}</span>
                                <i class="fa-solid fa-chevron-down arrow-icon"></i>
                            </div>
                            <ul class="dropdown-options-modern" id="roleOptionsEdit">
                                <li onclick="selectEditOption('role', 'Admin', 'dropdownRoleEdit')">Admin</li>
                                <li onclick="selectEditOption('role', 'Super Admin', 'dropdownRoleEdit')">Super Admin</li>
                            </ul>
                            <input type="hidden" id="edit-role" value="${role}">
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #777; margin-bottom: 8px;">Status</label>
                        <div class="custom-dropdown-modern" id="dropdownStatusEdit">
                            <div class="dropdown-selected-modern" onclick="toggleEditDropdown('statusOptionsEdit', 'dropdownStatusEdit')">
                                <span id="statusTextEdit">${status}</span>
                                <i class="fa-solid fa-chevron-down arrow-icon"></i>
                            </div>
                            <ul class="dropdown-options-modern" id="statusOptionsEdit">
                                <li onclick="selectEditOption('status', 'Aktif', 'dropdownStatusEdit')">Aktif</li>
                                <li onclick="selectEditOption('status', 'Nonaktif', 'dropdownStatusEdit')">Non-Aktif</li>
                            </ul>
                            <input type="hidden" id="edit-status" value="${status}">
                        </div>
                    </div>
                </div>
            </div>
        `,
        
        showCancelButton: true,
        confirmButtonColor: '#2392ED',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        width: '500px',
        preConfirm: () => {
            return {
                id: document.getElementById('edit-id').value,
                nama: document.getElementById('edit-nama').value,
                email: document.getElementById('edit-email').value,
                role: document.getElementById('edit-role').value,
                status: document.getElementById('edit-status').value
            }
        }
    });

    if (formValues) {
        const formData = new FormData();
        formData.append('id', formValues.id);
        formData.append('nama_lengkap', formValues.nama);
        formData.append('email', formValues.email);
        formData.append('role', formValues.role);
        formData.append('status', formValues.status);

        fetch('edit_user.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(data => {
        if (data.trim() === 'success') {
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data telah diperbarui.', timer: 2000, showConfirmButton: false, timerProgressBar: true,
        didOpen: (toast) => {
            const progressBar = toast.querySelector('.swal2-timer-progress-bar');
            if (progressBar) progressBar.style.backgroundColor = '#2ecc71';
            }
         });
            if (action === 'add_user.php') { 
                this.reset(); 
                refreshUserTable();
            }
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan pada server.' });
            }
        });
    }
}

function toggleDropdownModern(id, parentId) {
    document.querySelectorAll('.dropdown-options-modern').forEach(ul => ul.classList.remove('show'));
    document.getElementById(id).classList.toggle('show');
    document.getElementById(parentId).classList.toggle('active');
}

function selectOptionModern(type, val, mode) {
    document.getElementById(type + 'Text' + mode).innerText = val;
    document.getElementById(type + 'Input' + mode).value = val;
    document.querySelectorAll('.dropdown-options-modern').forEach(ul => ul.classList.remove('show'));
    document.getElementById('dropdown' + type.charAt(0).toUpperCase() + type.slice(1) + mode).classList.remove('active');
}

// Fungsi pembantu dropdown modal edit
function toggleEditDropdown(id, parentId) {
    document.querySelectorAll('.dropdown-options-modern').forEach(ul => ul.classList.remove('show'))
    document.querySelectorAll('.custom-dropdown-modern').forEach(div => {
        if (div.id !== parentId) div.classList.remove('active')
    })
    document.getElementById(id).classList.toggle('show')
    document.getElementById(parentId).classList.toggle('active')
}

function selectEditOption(type, val, parentId) {
    document.getElementById(type + 'TextEdit').innerText = val
    document.getElementById('edit-' + type).value = val
    document.querySelectorAll('.dropdown-options-modern').forEach(ul => ul.classList.remove('show'))
    document.getElementById(parentId).classList.remove('active')
}

// KONFIRMASI HAPUS
function confirmDelete(id) {
    Swal.fire({ 
        title: 'Hapus?', text: "User yang dihapus tidak dapat dikembalikan!", icon: 'warning', 
        showCancelButton: true, confirmButtonColor: '#2392ED', cancelButtonColor: '#d33', 
        confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal' 
    }).then((result) => { 
        if (result.isConfirmed) {
            fetch('delete_user.php?id=' + id)
            .then(() => {
                Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'User berhasil dihapus.', timer: 2000, showConfirmButton: false, timerProgressBar: true,
                didOpen: (toast) => {
                    const progressBar = toast.querySelector('.swal2-timer-progress-bar');
                    if (progressBar) progressBar.style.backgroundColor = '#2ecc71';
                    }
                 });
                refreshUserTable();
            });
        }
    });
}

// PREVIEW LOGO
function previewImage(input) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => { 
            document.getElementById('logo-preview').src = e.target.result;
            document.getElementById('file-name').textContent = file.name;
        };
        reader.readAsDataURL(file);
    }
}

// LOGIKA UTAMA (DOM LOADED)
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle
    const btnToggle = document.getElementById('sidebarToggle');
    if (btnToggle) {
        btnToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-closed');
            localStorage.setItem('sidebarStatus', document.body.classList.contains('sidebar-closed') ? 'closed' : 'open');
        });
    }
    
    if (localStorage.getItem('sidebarStatus') === 'closed') document.body.classList.add('sidebar-closed');

                // Otomatis scroll sidebar ke menu yang sedang aktif
            const activeLink = document.querySelector('.nav-link.active');
            const sidebarMenu = document.querySelector('.nav-menu');
            
            if (activeLink && sidebarMenu) {
                // Menggeser scrollbar tepat ke posisi menu aktif berada di tengah
                sidebarMenu.scrollTop = activeLink.offsetTop - (sidebarMenu.clientHeight / 2) + (activeLink.clientHeight / 2);
            }

// AJAX Form Submit (Profil, Struktur, Tambah User)
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const action = this.getAttribute('action');
        if (['update_profil.php', 'update_struktur.php', 'add_user.php'].includes(action)) {
            e.preventDefault();
            
            fetch(action, { method: 'POST', body: new FormData(this) })
            .then(res => res.text())
            .then(data => {
                // Bersihkan spasi berlebih pada teks balasan dari server
                const response = data.trim();

                if (response === 'success') {
                    // Tampilkan notifikasi sukses
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Berhasil!', 
                        text: 'Data telah diperbarui.', 
                        timer: 2000, 
                        showConfirmButton: false, 
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            const progressBar = toast.querySelector('.swal2-timer-progress-bar');
                            if (progressBar) progressBar.style.backgroundColor = '#2ecc71';
                        }
                    });

                    // Jika form yang dikirim adalah Tambah User
                    if (action === 'add_user.php') { 
                        this.reset(); 
                        document.getElementById('roleTextTambah').innerText = 'Admin';
                        document.getElementById('roleInputTambah').value = 'Admin';
                        if (typeof jalankanPembaruanDataUser === 'function') {
                            jalankanPembaruanDataUser(); // Refresh tabel secara otomatis
                        }
                    }
                } else {
                    // Tampilkan pesan gagal jika respons dari PHP bukan 'success'
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Gagal!', 
                        text: 'Terjadi kesalahan pada server saat menyimpan data.' 
                    });
                }
            })
            .catch(err => {
                console.error("Error submit form:", err);
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error!', 
                    text: 'Gagal terhubung ke server.' 
                });
            });
        }
    });
});

// --- LOGIKA MENUTUP DROPDOWN ---
window.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-dropdown-modern')) {
        
        document.querySelectorAll('.dropdown-options-modern').forEach(ul => {
            ul.classList.remove('show');
        });
        
        document.querySelectorAll('.custom-dropdown-modern').forEach(div => {
            div.classList.remove('active');
        });
    }
});

    // Loading Screen
    const loader = document.getElementById('loading-screen');
    if (loader) {
        setTimeout(() => { loader.classList.add('loader-hidden'); }, 500); 
    }

    // Logout Notification
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Saudara akan keluar dari sistem Dashboard",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2392ED',
                cancelButtonColor: '#d33',  
                confirmButtonText: 'Ya, Keluar!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil Keluar',
                        text: 'Tuhan Yesus Memberkati!',
                        showConfirmButton: false, timer: 1500, timerProgressBar: true
                    }).then(() => window.location.href = '../logout.php');
                }
            });
        });
    }
});

// Fungsi memicu munculnya kotak informasi
function toggleUserCard(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('userProfileDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Fungsi menutup kotak informasi
function closeUserCard(event) {
    if (event) event.stopPropagation();
    const dropdown = document.getElementById('userProfileDropdown');
    if (dropdown) {
        dropdown.classList.remove('show');
    }
}

// Tutup otomatis jika pengguna mengklik area luar bodi halaman manapun
window.addEventListener('click', function(e) {
    const dropdown = document.getElementById('userProfileDropdown');
    const infoMenu = document.getElementById('userInfoMenu');
    
    if (dropdown && dropdown.classList.contains('show') && infoMenu && !infoMenu.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});