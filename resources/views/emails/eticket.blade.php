<!DOCTYPE html>
<html>
<head>
    <title>E-Ticket Anda</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { background-color: #1E5128; color: white; padding: 15px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { padding: 20px; }
        .ticket-box { background-color: #f9f9f9; padding: 15px; border: 1px dashed #ccc; margin: 15px 0; text-align: center; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Penglipuran Smart Tour</h2>
        </div>
        <div class="content">
            <p>Halo, <strong>{{ $reservation->guest_name }}</strong>,</p>
            <p>Terima kasih telah melakukan pemesanan tiket wisata di Desa Penglipuran. Pembayaran Anda telah berhasil kami terima.</p>
            
            <div class="ticket-box">
                <h3>Kode E-Ticket Anda:</h3>
                <h2 style="color: #1E5128;">{{ $reservation->qr_code }}</h2>
                <p>Tunjukkan kode ini kepada petugas saat Anda tiba di lokasi.</p>
            </div>

            <p><strong>Rincian Pesanan:</strong></p>
            <ul>
                <li><strong>Paket Wisata:</strong> {{ $reservation->tourPackage->name }}</li>
                <li><strong>Jumlah Orang:</strong> {{ $reservation->party_size }} Orang</li>
                <li><strong>Tanggal Kunjungan:</strong> {{ \Carbon\Carbon::parse($reservation->scheduled_date)->format('d F Y') }}</li>
                <li><strong>Total Bayar:</strong> Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</li>
            </ul>
            
            <p>Selamat menikmati wisata Anda di Desa Penglipuran!</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Ganesha Smart Edutourism - Desa Penglipuran
        </div>
    </div>
</body>
</html>
