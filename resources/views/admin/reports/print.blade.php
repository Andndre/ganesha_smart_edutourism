<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan {{ $selectedPeriod }} — Ganesha Smart Edutourism</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
            color: #191A19;
            background: #FAF9F6;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .action-bar {
            display: flex;
            gap: 12px;
            padding: 16px 24px;
            background: #F5F5F5;
            border-bottom: 1px solid #E0E0E0;
            justify-content: flex-end;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .btn-secondary {
            background: #E8E8E8;
            color: #191A19;
        }

        .btn-secondary:hover {
            background: #D0D0D0;
        }

        .btn-primary {
            background: #1E5128;
            color: white;
        }

        .btn-primary:hover {
            background: #153d1f;
        }

        .report-content {
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #1E5128;
            padding-bottom: 24px;
        }

        .logo-text {
            font-size: 28px;
            font-weight: 700;
            color: #1E5128;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 16px;
        }

        .report-title {
            font-size: 20px;
            font-weight: 600;
            color: #191A19;
            margin-bottom: 8px;
        }

        .report-meta {
            font-size: 13px;
            color: #999;
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .kpi-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 40px;
        }

        .kpi-card {
            background: #F9F9F9;
            border: 1px solid #E5E5E5;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .kpi-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .kpi-value {
            font-size: 32px;
            font-weight: 700;
            color: #1E5128;
            margin-bottom: 8px;
        }

        .kpi-delta {
            font-size: 13px;
            font-weight: 500;
        }

        .delta-positive {
            color: #4CAF50;
        }

        .delta-negative {
            color: #F44336;
        }

        .section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1E5128;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #D4AF37;
            display: inline-block;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 13px;
        }

        .table thead {
            background: #F5F5F5;
            border-bottom: 2px solid #1E5128;
        }

        .table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #191A19;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid #E5E5E5;
        }

        .table tbody tr:hover {
            background: #F9F9F9;
        }

        .progress-bar {
            width: 100%;
            height: 24px;
            background: #E8E8E8;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #1E5128 0%, #2A7A3F 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 11px;
            font-weight: 600;
        }

        .amount-text {
            font-weight: 600;
            color: #1E5128;
        }

        .pct-text {
            text-align: right;
            font-weight: 500;
            color: #666;
        }

        .footer {
            text-align: center;
            padding: 24px;
            background: #F5F5F5;
            border-top: 1px solid #E0E0E0;
            font-size: 12px;
            color: #999;
        }

        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
                padding: 0;
            }

            .container {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
            }

            .report-content {
                padding: 0;
            }

            .kpi-section {
                page-break-inside: avoid;
            }

            .section {
                page-break-inside: avoid;
            }

            .table {
                page-break-inside: avoid;
            }
        }

        @page {
            size: A4;
            margin: 20mm;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Action Bar -->
        <div class="action-bar no-print">
            <a href="javascript:history.back()" class="btn btn-secondary">← Kembali</a>
            <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak / Simpan PDF</button>
        </div>

        <!-- Report Content -->
        <div class="report-content">
            <!-- Header -->
            <div class="header">
                <div class="logo-text">Ganesha Smart Edutourism</div>
                <div class="subtitle">Desa Wisata Penglipuran</div>
                <div class="report-title">Laporan Analytics & Performa</div>
                <div class="report-meta">
                    <span>Periode: <strong>{{ $selectedPeriod }}</strong></span>
                    <span>Generated: <strong>{{ $generatedAt }}</strong></span>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="kpi-section">
                <div class="kpi-card">
                    <div class="kpi-label">Total Pengunjung</div>
                    <div class="kpi-value">{{ number_format($visitorCount) }}</div>
                    <div class="kpi-delta {{ $visitorDelta >= 0 ? 'delta-positive' : 'delta-negative' }}">
                        {{ $visitorDelta >= 0 ? '+' : '' }}{{ $visitorDelta }}% vs bulan lalu
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-label">Total Pendapatan</div>
                    <div class="kpi-value">Rp {{ number_format($revenue / 1000000, 0, ',', '.') }} Jt</div>
                    <div class="kpi-delta {{ $revenueDelta >= 0 ? 'delta-positive' : 'delta-negative' }}">
                        {{ $revenueDelta >= 0 ? '+' : '' }}{{ $revenueDelta }}% vs bulan lalu
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-label">Tiket Terjual</div>
                    <div class="kpi-value">{{ number_format($ticketsSold) }}</div>
                    <div class="kpi-delta {{ $ticketsDelta >= 0 ? 'delta-positive' : 'delta-negative' }}">
                        {{ $ticketsDelta >= 0 ? '+' : '' }}{{ $ticketsDelta }}% vs bulan lalu
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-label">Rating Kepuasan</div>
                    <div class="kpi-value">{{ $rating }}</div>
                    <div class="kpi-delta {{ $ratingDelta >= 0 ? 'delta-positive' : 'delta-negative' }}">
                        {{ $ratingDelta >= 0 ? '+' : '' }}{{ $ratingDelta }} vs bulan lalu
                    </div>
                </div>
            </div>

            <!-- Trend Pengunjung -->
            <div class="section">
                <h3 class="section-title">Trend Pengunjung (21 Hari Pertama)</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hari</th>
                            <th colspan="3">Jumlah Pengunjung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($day = 1; $day <= 21; $day++)
                            <tr>
                                <td><strong>Hari {{ $day }}</strong></td>
                                <td colspan="3">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="progress-bar" style="flex: 1; min-width: 200px;">
                                            <div class="progress-fill" style="width: {{ (($chartData[$day - 1] ?? 0) / 800) * 100 }}%">
                                                {{ $chartData[$day - 1] ?? 0 }}
                                            </div>
                                        </div>
                                        <span style="min-width: 60px; text-align: right; font-weight: 500;">{{ $chartData[$day - 1] ?? 0 }} orang</span>
                                    </div>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <!-- Revenue Breakdown -->
            <div class="section">
                <h3 class="section-title">Breakdown Pendapatan per Paket</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Paket</th>
                            <th>Jumlah</th>
                            <th style="width: 40%;">Proporsi</th>
                            <th style="text-align: right;">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($revenueBreakdown as $item)
                            <tr>
                                <td><strong>{{ $item['label'] }}</strong></td>
                                <td class="amount-text">{{ $item['amount'] }}</td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: {{ $item['pct'] }}%;">
                                            {{ $item['pct'] }}%
                                        </div>
                                    </div>
                                </td>
                                <td class="pct-text">{{ $item['pct'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Hari Tersibuk -->
            <div class="section">
                <h3 class="section-title">Hari Tersibuk dalam Seminggu</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hari</th>
                            <th>Jumlah Pengunjung</th>
                            <th style="width: 40%;">Aktivitas</th>
                            <th style="text-align: right;">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($busyDays as $busyDay)
                            <tr>
                                <td><strong>{{ $busyDay['day'] }}</strong></td>
                                <td>{{ $busyDay['visitors'] }} orang</td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: {{ $busyDay['pct'] }}%;">
                                            {{ $busyDay['pct'] }}%
                                        </div>
                                    </div>
                                </td>
                                <td class="pct-text">{{ $busyDay['pct'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini digenerate otomatis oleh sistem Ganesha Smart Edutourism</p>
            <p style="margin-top: 8px; font-size: 11px; color: #BBB;">Laporan ini bersifat confidential dan hanya untuk penggunaan internal manajemen.</p>
        </div>
    </div>

    <script>
        // Auto-trigger print dialog after page fully renders
        window.addEventListener('load', function() {
            setTimeout(() => window.print(), 500);
        });
    </script>
</body>
</html>
