<?php
// Robust variable resolution: accepts both direct extractions and nested data wrappers
$b = $booking ?? ($data['booking'] ?? null);
$items = $items ?? ($data['items'] ?? []);

if (!$b) {
    echo "<div class='alert alert-danger m-5'>Invoice record could not be loaded. Please return to the dispatch list.</div>";
    return;
}

$subtotal = (float)($b['subtotal'] ?? 0);
$surge    = (float)($b['surge_amount'] ?? 0);
$discount = (float)($b['discount_amount'] ?? 0);
$tax      = (float)($b['tax_amount'] ?? 0);
$total    = (float)($b['total_amount'] ?? ($subtotal + $surge - $discount + $tax));
$cgst     = round($tax / 2, 2);
$sgst     = round($tax / 2, 2);

$invoiceFileName = 'Booking_Cost_Summary_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)($b['booking_code'] ?? 'FX-' . $b['id'])) . '.pdf';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cost Summary - <?= htmlspecialchars($b['booking_code'] ?? 'INV') ?></title>
    
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    <!-- html2pdf.js Library for 1-Click Client-Side PDF Generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root {
            --fm-cream-bg: #fdfbf7;
            --fm-cream-card: #fdf9ee;
            --fm-sand-border: #e8e2d2;
            --fm-amber: #eab308;
            --fm-amber-deep: #ca8a04;
            --fm-ink: #1e1b18;
        }

        body { 
            background: var(--fm-cream-bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--fm-ink);
            margin: 0;
            padding: 20px;
        }

        .invoice-card { 
            max-width: 820px; 
            margin: 15px auto; 
            background: #ffffff; 
            border: 1px solid var(--fm-sand-border); 
            border-radius: 16px; 
            padding: 40px; 
            box-shadow: 0 10px 30px rgba(120, 53, 15, 0.06);
            position: relative;
        }

        .font-heading {
            font-family: 'Space Grotesk', sans-serif;
        }

        .bg-light-cream {
            background-color: var(--fm-cream-card);
        }

        /* Print media specifications */
        @media print {
            body { 
                background: #ffffff !important; 
                padding: 0 !important;
            }
            .invoice-card { 
                border: none !important; 
                margin: 0 !important; 
                padding: 15px !important; 
                box-shadow: none !important;
                max-width: 100% !important;
            }
            .no-print { 
                display: none !important; 
            }
        }
    </style>
</head>
<body>

    <!-- Action Toolbar (Hidden during Print and in generated PDF) -->
    <div class="text-center my-3 no-print d-flex justify-content-center align-items-center gap-2">
        <!-- Direct PDF Download Button -->
        <button id="btnDownloadPdf" onclick="downloadInvoicePDF()" class="btn btn-dark rounded-pill px-4 fw-semibold shadow-sm d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-arrow-down-fill text-warning"></i> Download PDF
        </button>

        <!-- Browser System Print Button -->
        <button onclick="window.print()" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2" style="background: linear-gradient(135deg, #fde047 0%, #eab308 100%); border: 1px solid #ca8a04; color: #713f12;">
            <i class="bi bi-printer-fill"></i> Print Invoice
        </button>

        <!-- Close Window Button -->
        <button onclick="window.close()" class="btn btn-outline-secondary rounded-pill px-3">
            Close
        </button>
    </div>

    <!-- Printable Invoice Sheet (Target container for PDF generator) -->
    <div id="invoiceSheet" class="invoice-card">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4" style="border-color: var(--fm-sand-border) !important;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; background: #eab308; color: #ffffff;">
                        <i class="bi bi-wrench-adjustable-circle-fill fs-5"></i>
                    </div>
                    <h3 class="fw-bold m-0 text-dark font-heading">FixMate<span class="text-warning">.pro</span></h3>
                </div>
                <div class="text-muted small fw-semibold">FixMate Technologies India Private Limited</div>
                <div class="text-muted small">100ft Ring Road, Koramangala 4th Block, Bengaluru, KA - 560034</div>
            </div>
            <div class="text-end">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.75rem;">
                    BOOKING COST SUMMARY
                </span>
                <div class="fw-bold mt-2 fs-5 text-dark font-heading">#<?= htmlspecialchars($b['booking_code'] ?? 'FX-' . $b['id']) ?></div>
                <div class="text-muted small">Date: <?= !empty($b['created_at']) ? date('d F Y', strtotime($b['created_at'])) : date('d F Y') ?></div>
                <div class="text-muted small">
                    Payment: <span class="badge bg-light text-dark border"><?= strtoupper($b['payment_status'] ?? 'PAID') ?> (<?= strtoupper($b['payment_method'] ?? 'ONLINE') ?>)</span>
                </div>
            </div>
        </div>

        <!-- Parties Details -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="text-muted text-uppercase small fw-bold mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">Billed To (Customer):</h6>
                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($b['customer_name'] ?? 'Registered Customer') ?></div>
                <div class="text-muted small">
                    <?= htmlspecialchars($b['address_line1'] ?? '') ?><?= !empty($b['address_line2']) ? ', ' . htmlspecialchars($b['address_line2']) : '' ?>
                </div>
                <div class="text-muted small">
                    <?= htmlspecialchars($b['city'] ?? 'Bengaluru') ?>, <?= htmlspecialchars($b['state'] ?? 'Karnataka') ?> - <?= htmlspecialchars($b['postal_code'] ?? '') ?>
                </div>
                <div class="text-muted small mt-1"><i class="bi bi-telephone text-secondary me-1"></i> <?= htmlspecialchars($b['customer_phone'] ?? 'N/A') ?></div>
            </div>
            <div class="col-6 text-end">
                <h6 class="text-muted text-uppercase small fw-bold mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">Fulfillment Partner:</h6>
                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($b['pro_name'] ?? 'FixMate Authorized Technician') ?></div>
                <div class="text-muted small">Service Zone: <?= htmlspecialchars($b['zone_name'] ?? 'Bengaluru Urban') ?></div>
                <div class="text-muted small">
                    Scheduled: <?= !empty($b['scheduled_date']) ? date('d M Y', strtotime($b['scheduled_date'])) : 'On Demand' ?> 
                    (<?= htmlspecialchars($b['scheduled_time_slot'] ?? 'Standard Slot') ?>)
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle mb-0" style="border-color: var(--fm-sand-border);">
                <thead class="bg-light-cream text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 55%;">Task / Service Description</th>
                        <th class="text-center" style="width: 15%;">Unit Rate</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-end" style="width: 15%;">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td>1</td>
                            <td class="fw-semibold">Diagnostic & Home Repair Inspection</td>
                            <td class="text-center">₹<?= number_format($subtotal, 2) ?></td>
                            <td class="text-center">1</td>
                            <td class="text-end fw-bold">₹<?= number_format($subtotal, 2) ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $idx => $item): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($item['service_name'] ?? 'Service Item') ?></td>
                                <td class="text-center text-muted">₹<?= number_format((float)($item['unit_price'] ?? 0), 2) ?></td>
                                <td class="text-center"><?= (int)($item['quantity'] ?? 1) ?></td>
                                <td class="text-end fw-bold text-dark">₹<?= number_format((float)($item['total_price'] ?? 0), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Calculation Breakdowns -->
        <div class="row justify-content-end mb-4">
            <div class="col-7 col-md-5">
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted">Item Subtotal:</td>
                        <td class="text-end fw-semibold">₹<?= number_format($subtotal, 2) ?></td>
                    </tr>
                    <?php if ($surge > 0): ?>
                        <tr>
                            <td class="text-muted">Peak Area Surge:</td>
                            <td class="text-end text-danger fw-semibold">+ ₹<?= number_format($surge, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($discount > 0): ?>
                        <tr>
                            <td class="text-muted">Promotional Voucher (<?= htmlspecialchars($b['coupon_code'] ?? 'FIX20') ?>):</td>
                            <td class="text-end text-success fw-semibold">- ₹<?= number_format($discount, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">CGST:</td>
                        <td class="text-end fw-semibold">₹<?= number_format($cgst, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">SGST:</td>
                        <td class="text-end fw-semibold">₹<?= number_format($sgst, 2) ?></td>
                    </tr>
                    <tr class="border-top" style="border-color: #ca8a04 !important;">
                        <td class="fw-bold fs-6 text-dark pt-2">Total Invoice Value:</td>
                        <td class="text-end fw-bold fs-5 text-dark pt-2 font-heading">₹<?= number_format($total, 2) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="border-top pt-3 text-center text-muted small" style="border-color: var(--fm-sand-border) !important; font-size: 0.76rem;">
            This cost summary shows the booking charges, discounts, taxes, and total recorded by Fixmate. Payment status: <?= htmlspecialchars(strtoupper($b['payment_status'] ?? 'PENDING')) ?>. Thank you for choosing Fixmate!
        </div>
    </div>

    <!-- Client-Side PDF Generation Script -->
    <script>
        function downloadInvoicePDF() {
            const btn = document.getElementById('btnDownloadPdf');
            const element = document.getElementById('invoiceSheet');
            const originalContent = btn.innerHTML;

            // Visual feedback on the button
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Preparing PDF...';

            const opt = {
                margin:       [10, 10, 10, 10], // top, left, bottom, right in mm
                filename:     <?= json_encode($invoiceFileName) ?>,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, letterRendering: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // Run html2pdf generator
            html2pdf().set(opt).from(element).save().then(() => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }).catch(err => {
                console.error('PDF Export Error:', err);
                alert('Could not export PDF automatically. Using browser print as fallback.');
                window.print();
                btn.disabled = false;
                btn.innerHTML = originalContent;
            });
        }
    </script>

</body>
</html>