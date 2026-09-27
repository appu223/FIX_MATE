<div class="container py-3">
    <h4 class="fw-bold mb-4"><i class="bi bi-lightning-charge-fill text-primary me-2"></i>Express Direct Booking</h4>

    <div class="row g-4">
        <!-- Service Selection -->
        <div class="col-12 col-lg-7">
            <div class="card-custom p-4 mb-3">
                <h6 class="fw-bold border-bottom pb-2 mb-3">Instant Booking Details</h6>
                <div class="p-3 border rounded-3 bg-light mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold m-0" id="dir_service_name">Split AC Foam Jet Servicing</h5>
                            <span class="text-muted small"><i class="bi bi-clock me-1"></i>60 Mins Duration</span>
                        </div>
                        <div class="fs-4 fw-bold text-success" id="dir_service_price">₹899.00</div>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Select Date</label>
                        <input type="date" id="direct_date" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Preferred Arrival Slot</label>
                        <select id="direct_slot" class="form-select">
                            <option value="10:00 AM - 12:00 PM">10:00 AM - 12:00 PM</option>
                            <option value="02:00 PM - 04:00 PM">02:00 PM - 04:00 PM</option>
                            <option value="04:00 PM - 06:00 PM">04:00 PM - 06:00 PM</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Select Service Address</label>
                    <select id="direct_address" class="form-select">
                        <option value="1">Home: Flat 402, Sunshine Heights, Bengaluru</option>
                        <option value="2">Office: Indiranagar 100ft Road, Bengaluru</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-bold">Notes for Technician (Optional)</label>
                    <textarea id="direct_notes" rows="2" class="form-control" placeholder="e.g. Please bring extra ladder or ring the bell twice..."></textarea>
                </div>
            </div>
        </div>

        <!-- Summary & 1-Click Checkout -->
        <div class="col-12 col-lg-5">
            <div class="card-custom p-4">
                <h6 class="fw-bold border-bottom pb-2 mb-3">1-Click Order Summary</h6>
                
                <div class="small mb-3">
                    <div class="d-flex justify-content-between py-1"><span>Base Diagnostic Fee:</span> <span id="lblDirectBase">₹899.00</span></div>
                    <div class="d-flex justify-content-between py-1 text-muted"><span>GST (18%):</span> <span>+ ₹161.82</span></div>
                    <div class="d-flex justify-content-between py-2 border-top fw-bold fs-5 text-dark"><span>Total Payable:</span> <span class="text-primary">₹1,060.82</span></div>
                </div>

                <h6 class="fw-bold border-top pt-3 mb-2">Payment Choice</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="direct_payment" id="dpay_online" value="online" checked>
                    <label class="form-check-label fw-semibold" for="dpay_online">Instant Online Payment (UPI / Cards)</label>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="radio" name="direct_payment" id="dpay_cod" value="cod">
                    <label class="form-check-label fw-semibold" for="dpay_cod">Pay Cash on Completion</label>
                </div>

                <button class="btn btn-primary w-100 rounded-pill py-2 fw-bold" onclick="confirmDirectBooking()">
                    <i class="bi bi-shield-check me-1"></i> Confirm & Dispatch Pro
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const appBaseUrl = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
function confirmDirectBooking() {
    const payload = {
        items: [{ id: 5, name: 'Split AC Foam Jet Servicing', base_price: 899.00, quantity: 1 }],
        order: {
            address_id: document.getElementById('direct_address').value,
            scheduled_date: document.getElementById('direct_date').value,
            scheduled_time_slot: document.getElementById('direct_slot').value,
            notes: document.getElementById('direct_notes').value,
            coupon_id: null,
            subtotal: 899.00,
            discount_amount: 0.00,
            surge_amount: 0.00,
            tax_amount: 161.82,
            total_amount: 1060.82,
            payment_method: document.querySelector('input[name="direct_payment"]:checked').value
        }
    };

    fetch(`${appBaseUrl}/customer/checkout/place-order`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert(`Booking Dispatched! Reference: ${res.booking_code}`);
            window.location.href = `${appBaseUrl}/customer/my-bookings`;
        } else {
            alert('Failed: ' + res.message);
        }
    })
    .catch(() => alert('Network error placing booking.'));
}
</script>