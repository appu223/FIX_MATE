<div class="container-fluid p-0">

    <!-- Top Headline -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Administrative Overview</h3>
            <p class="text-muted m-0" style="font-size: 0.9rem;">Real-time booking fulfillment, transaction yields & dispatch health.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-white card-custom px-3 py-2 fw-semibold text-secondary" onclick="location.reload();">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Realtime
            </button>
            <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/dispatch" class="btn btn-primary px-3 py-2 fw-semibold rounded-pill">
                <i class="bi bi-plus-circle me-1"></i> Create Booking
            </a>
        </div>
    </div>

    <!-- 5 KPI Stat Cards with 4px left borders -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl">
            <div class="card-custom p-3 kpi-green h-100">
                <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Gross Volume</div>
                <div class="d-flex align-items-baseline gap-2 mt-2">
                    <h3 class="fw-bold mb-0 text-dark">₹<?= number_format($kpis['gross_revenue'] ?? 0, 2) ?></h3>
                </div>
                <div class="mt-2 text-success small fw-medium">
                    <i class="bi bi-graph-up-arrow me-1"></i> Platform GMV
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="card-custom p-3 kpi-blue h-100">
                <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Net Commission</div>
                <div class="d-flex align-items-baseline gap-2 mt-2">
                    <h3 class="fw-bold mb-0 text-dark">₹<?= number_format($kpis['net_commission'] ?? 0, 2) ?></h3>
                </div>
                <div class="mt-2 text-primary small fw-medium">
                    <i class="bi bi-wallet2 me-1"></i> 15% Platform Take
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="card-custom p-3 kpi-purple h-100">
                <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Active Pipeline</div>
                <div class="d-flex align-items-baseline gap-2 mt-2">
                    <h3 class="fw-bold mb-0 text-dark"><?= $kpis['active_bookings'] ?? 0 ?></h3>
                </div>
                <div class="mt-2 text-muted small fw-medium">
                    <i class="bi bi-lightning-charge me-1"></i> Dispatched & In-field
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="card-custom p-3 kpi-amber h-100">
                <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">KYC Approvals</div>
                <div class="d-flex align-items-baseline gap-2 mt-2">
                    <h3 class="fw-bold mb-0 text-dark"><?= $kpis['kyc_pending'] ?? 0 ?></h3>
                </div>
                <div class="mt-2 text-warning small fw-medium">
                    <i class="bi bi-shield-exclamation me-1"></i> Action Required
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="card-custom p-3 kpi-red h-100">
                <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Verified Pros</div>
                <div class="d-flex align-items-baseline gap-2 mt-2">
                    <h3 class="fw-bold mb-0 text-dark"><?= $kpis['total_pros'] ?? 0 ?></h3>
                </div>
                <div class="mt-2 text-danger small fw-medium">
                    <i class="bi bi-person-check me-1"></i> Ready for Duty
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Section -->
    <div class="row g-3 mb-4">
        <!-- Revenue Spline Chart -->
        <div class="col-12 col-lg-8">
            <div class="card-custom p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold m-0 text-dark">Revenue & Commission Spline</h6>
                        <span class="text-muted" style="font-size: 0.78rem;">Financial velocity aggregated month-over-month</span>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">
                        ₹ Monthly Trajectory
                    </span>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="revenueSplineChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Service Category Donut Chart -->
        <div class="col-12 col-lg-4">
            <div class="card-custom p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold m-0 text-dark">Category Distribution</h6>
                        <span class="text-muted" style="font-size: 0.78rem;">Orders divided by vertical</span>
                    </div>
                </div>
                <div style="height: 230px; position: relative;" class="d-flex justify-content-center">
                    <canvas id="serviceCategoryDonut"></canvas>
                </div>
                <div class="mt-3 border-top pt-2" style="max-height: 90px; overflow-y: auto;">
                    <?php if (!empty($categoryBreakdown)): ?>
                        <?php foreach ($categoryBreakdown as $cat): ?>
                            <div class="d-flex justify-content-between align-items-center py-1 small">
                                <span class="text-secondary"><?= htmlspecialchars($cat['category_name']) ?></span>
                                <span class="fw-semibold text-dark">₹<?= number_format((float)$cat['total_sales'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="fw-bold m-0 text-dark">Recent Dispatch Pipeline</h6>
                <span class="text-muted" style="font-size: 0.78rem;">Most recent requests processed across the platform</span>
            </div>
            <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/dispatch" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                View Full Grid <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                    <tr>
                        <th class="border-0">Booking Ref</th>
                        <th class="border-0">Customer Details</th>
                        <th class="border-0">Assigned Professional</th>
                        <th class="border-0">Scheduled For</th>
                        <th class="border-0">Amount</th>
                        <th class="border-0">Payment</th>
                        <th class="border-0">Job Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentBookings)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No booking activity found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentBookings as $b): ?>
                            <tr>
                                <td class="fw-bold text-primary">
                                    <?= htmlspecialchars($b['booking_code']) ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($b['customer_name']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($b['customer_phone']) ?></div>
                                </td>
                                <td>
                                    <?php if ($b['pro_name'] === 'Unassigned'): ?>
                                        <span class="badge badge-pill-amber">Unassigned</span>
                                    <?php else: ?>
                                        <div class="d-flex align-items-center gap-1 text-dark fw-medium">
                                            <i class="bi bi-person-circle text-primary"></i> <?= htmlspecialchars($b['pro_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?= date('d M Y', strtotime($b['scheduled_date'])) ?></div>
                                    <span class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($b['scheduled_time_slot']) ?></span>
                                </td>
                                <td class="fw-bold text-dark">
                                    ₹<?= number_format((float)$b['total_amount'], 2) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $b['payment_status'] === 'paid' ? 'badge-pill-green' : 'badge-pill-amber' ?>">
                                        <?= strtoupper($b['payment_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $statusClass = match($b['status']) {
                                            'completed'   => 'badge-pill-green',
                                            'in_progress' => 'badge-pill-blue',
                                            'assigned'    => 'badge-pill-blue',
                                            'pending'     => 'badge-pill-amber',
                                            'cancelled'   => 'badge-pill-red',
                                            default       => 'bg-secondary text-white rounded-pill px-2 py-1'
                                        };
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= strtoupper(str_replace('_', ' ', $b['status'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart Initialization Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const dashboardBasePath = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Chart) return;

    fetch(`${dashboardBasePath}/admin/api/revenue-analytics`)
        .then(response => response.json())
        .then(data => {
            const ctxSpline = document.getElementById('revenueSplineChart').getContext('2d');
            new Chart(ctxSpline, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Gross Revenue (₹)',
                            data: data.revenue,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.08)',
                            fill: true,
                            tension: 0.38,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointBackgroundColor: '#0d6efd'
                        },
                        {
                            label: 'FixMate Commission (₹)',
                            data: data.profit,
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25, 135, 84, 0.08)',
                            fill: true,
                            tension: 0.38,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointBackgroundColor: '#198754'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return '₹' + value.toLocaleString('en-IN'); }
                            }
                        }
                    }
                }
            });
        });

    const ctxDonut = document.getElementById('serviceCategoryDonut').getContext('2d');
    const categoryLabels = <?= json_encode(!empty($categoryBreakdown) ? array_column($categoryBreakdown, 'category_name') : ['Electrical', 'Plumbing', 'AC', 'Cleaning']) ?>;
    const categoryCounts = <?= json_encode(!empty($categoryBreakdown) ? array_map('intval', array_column($categoryBreakdown, 'bookings_count')) : [4, 3, 2, 1]) ?>;

    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryCounts,
                backgroundColor: ['#0d6efd', '#198754', '#6f42c1', '#f59e0b', '#0dcaf0', '#6c757d'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } }
            },
            cutout: '72%'
        }
    });
});
</script>