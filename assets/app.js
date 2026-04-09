let cart = JSON.parse(localStorage.getItem("cart")) || [];
let lastTransaction = null; // Menyimpan data sementara untuk keperluan cetak struk

// ==========================================
// LOGIKA: HALAMAN KASIR
// ==========================================
function changeQty(id, nama, harga, delta) {
    let item = cart.find(i => i.id === id);
    if (item) {
        item.qty += delta;
        if (item.qty <= 0) cart = cart.filter(i => i.id !== id);
    } else if (delta > 0) {
        cart.push({ id: id, nama: nama, harga: harga, qty: 1 });
    }
    if ('vibrate' in navigator) navigator.vibrate(50);
    localStorage.setItem("cart", JSON.stringify(cart));
    renderKasirUI();
}

function renderKasirUI() {
    let totalQty = 0, totalPrice = 0;
    document.querySelectorAll('.qty-val').forEach(input => input.value = 0);
    cart.forEach(item => {
        totalQty += item.qty; totalPrice += (item.harga * item.qty);
        let inputQty = document.getElementById('qty-' + item.id);
        if (inputQty) inputQty.value = item.qty;
    });
    let cartCountEl = document.getElementById('cart-count');
    let totalEl = document.getElementById('total');
    if (cartCountEl) cartCountEl.innerText = totalQty;
    if (totalEl) totalEl.innerText = formatRupiah(totalPrice);
}

function goCheckout() {
    if (cart.length === 0) return alert('Keranjang masih kosong!');
    localStorage.setItem("cart", JSON.stringify(cart));
    window.location.href = "checkout.php";
}

// ==========================================
// LOGIKA: HALAMAN CHECKOUT & POP-UP
// ==========================================
function renderCheckoutUI() {
    let cartListEl = document.getElementById('cart-list');
    if (!cartListEl) return;
    if (cart.length === 0) return cartListEl.innerHTML = '<p style="color:#ef4444; font-weight:600;">Keranjang kosong. Kembali ke kasir.</p>';
    
    let html = '', grandTotal = 0;
    cart.forEach(item => {
        let subtotal = item.harga * item.qty; grandTotal += subtotal;
        html += `<div style="display:flex; justify-content:space-between; margin-bottom:10px; border-bottom:1px dashed #e5e7eb; padding-bottom:10px;">
                    <div><div style="font-weight:600; color:#1f2937;">${item.nama}</div><div style="font-size:0.85rem; color:#6b7280;">${item.qty} x ${formatRupiah(item.harga)}</div></div>
                    <div style="font-weight:700; color:#1f2937;">${formatRupiah(subtotal)}</div>
                 </div>`;
    });
    html += `<div style="display:flex; justify-content:space-between; margin-top:15px; font-size:1.1rem; font-weight:700; color:#10b981;">
                <div>TOTAL TAGIHAN</div><div>${formatRupiah(grandTotal)}</div>
             </div><input type="hidden" id="grand-total-val" value="${grandTotal}">`;
    cartListEl.innerHTML = html;
}

// Fungsi kontrol Modal Error
function showError(msg) {
    document.getElementById('errorMessage').innerHTML = `
        <b style="color:#ef4444">${msg}</b>
    `;
    document.getElementById('errorModal').style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function prosesBayar() {
    if (cart.length === 0) return showError('Tidak ada pesanan di keranjang!');
    new Audio('https://actions.google.com/sounds/v1/cartoon/clang_and_wobble.ogg').play();
    let bayarInput = document.getElementById("bayar").value;
    let metode = document.getElementById("metode").value;
    let grandTotal = parseInt(document.getElementById('grand-total-val').value);
    let bayar = parseInt(bayarInput);

    // Validasi Uang
    if (!bayarInput || isNaN(bayar)) return showError('Masukkan nominal uang yang diterima dari pelanggan!');
    if (bayar < grandTotal) return showError(`Uang kurang! Total tagihan adalah ${formatRupiah(grandTotal)}`);

    // Simpan data transaksi ke variabel untuk keperluan struk (Sebelum cart dikosongkan)
    lastTransaction = {
        cart: [...cart], 
        total: grandTotal, 
        bayar: bayar, 
        kembali: bayar - grandTotal,
        metode: metode,
        tanggal: new Date().toLocaleString('id-ID')
    };

    fetch('../api/tambah_transaksi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cart, bayar, metode })
    }).then(res => res.json()).then(res => {
        // Tampilkan Modal Sukses (Bukan Alert)
        document.getElementById('successMessage').innerHTML = `
            Total Belanja: <b>${formatRupiah(grandTotal)}</b><br>
            Uang Diterima: <b>${formatRupiah(bayar)}</b><br>
            <div style="margin-top:10px; font-size:1.1rem;">Kembalian: <b style="color:#10b981;">${formatRupiah(bayar - grandTotal)}</b></div>
        `;
        document.getElementById('successModal').style.display = 'flex';
        
        // Hapus memori cart agar kasir bersih saat kembali
        localStorage.removeItem("cart");
        cart = []; 
    }).catch(err => showError("Gagal terhubung ke API Transaksi. Pastikan server aktif."));
}

// Fungsi navigasi setelah sukses
function selesaiTransaksi() {
    window.location.href = "kasir.php";
}

function cetakStruk() {
    if(!lastTransaction) return;

    let t = lastTransaction;

    let trxId = "TRX-" + Date.now().toString().slice(-8);
    let kasir = "KSR01";

    let total = 0; // ✅ FIX WAJIB

    let strukHtml = `
    <html>
    <head>
        <title>Struk</title>
        <style>
            body{
                font-family: "Courier New", monospace;
                width: 280px;
                margin:0 auto;
                padding:10px;
                font-size:12px;
                color:#000;
            }
            .center{text-align:center;}
            .line{border-bottom:1px dashed #000;margin:6px 0;}
            .row{display:flex;justify-content:space-between;}
            .bold{font-weight:bold;}
        </style>
    </head>
    <body>

    <div class="center bold">LITPOS MART</div>
    <div class="center">JL. KEMANG RAYA NO.18</div>
    <div class="center">JAKARTA SELATAN</div>
    <div class="center">TELP: 0812-3456-7890</div>

    <div class="line"></div>

    <div class="row">
        <span>${formatTanggalIndo()}</span>
        <span>${kasir}</span>
    </div>

    <div class="row">
        <span>${trxId}</span>
        <span>${t.metode}</span>
    </div>

    <div class="line"></div>
    `;

    t.cart.forEach(item=>{
        let sub = item.qty * item.harga;
        total += sub;

        strukHtml += `
        <div>${item.nama}</div>
        <div class="row">
            <span>${item.qty} x ${formatAngka(item.harga)}</span>
            <span>${formatAngka(sub)}</span>
        </div>
        `;
    });

    let grandTotal = total;

    strukHtml += `
    <div class="line"></div>

    <div class="row bold">
        <span>TOTAL</span>
        <span>${formatAngka(grandTotal)}</span>
    </div>

    <div class="row">
        <span>${t.metode}</span>
        <span>${formatAngka(t.bayar)}</span>
    </div>

    <div class="row bold">
        <span>KEMBALI</span>
        <span>${formatAngka(t.bayar - grandTotal)}</span>
    </div>

    <div class="line"></div>

    <div class="center">TERIMA KASIH</div>
    <div class="center">SELAMAT BELANJA KEMBALI</div>

    <div class="line"></div>

    <div class="center">LAYANAN KONSUMEN</div>
    <div class="center">1500580</div>
    <div class="center">SMS: 0816 500 580</div>
    <div class="center">EMAIL: KONTAK@LITPOS.ID</div>

    <div class="line"></div>

    <div class="center">WWW.LITPOS.ID</div>

    <script>
        window.onload = function(){
            window.print();
        }
    </script>

    </body>
    </html>
    `;

    let win = window.open('', '_blank', 'width=400,height=600');
    win.document.write(strukHtml);
    win.document.close();

    setTimeout(()=>{ selesaiTransaksi(); }, 1000);
}
// ==========================================
// FUNGSI BANTUAN
// ==========================================
function formatRupiah(angka) { 
    return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); 
}
function formatAngka(x){
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatTanggalIndo(){
    let d = new Date();
    return d.toLocaleString('id-ID').replace(/\//g, '.');
}

document.addEventListener('DOMContentLoaded', () => { renderKasirUI(); renderCheckoutUI(); });