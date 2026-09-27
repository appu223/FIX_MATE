<div class="container py-3">
    <h4 class="fw-bold mb-4"><i class="bi bi-bag-check-fill text-primary me-2"></i>Review Cart & Checkout</h4>

    <div id="selectedProfessionalSummary" class="customer-checkout-pro mb-4" hidden>
        <span class="checkout-pro-icon"><i class="bi bi-person-check-fill"></i></span>
        <div><span class="small text-uppercase fw-bold">Your selected technician</span><div id="selectedProfessionalName" class="fw-bold"></div><div class="small">Booking availability will be checked against your selected service address.</div></div>
        <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/pros" class="btn btn-sm btn-outline-primary ms-auto">Change</a>
    </div>
    <div id="bookingError" class="alert alert-danger" role="alert" hidden></div>

    <div class="row g-4">
        <!-- Cart Items -->
        <div class="col-12 col-lg-7">
            <div class="card-custom p-4 mb-3">
                <h6 class="fw-bold border-bottom pb-2 mb-3">Selected Repair Services</h6>
                <div id="cartItemsContainer"></div>
            </div>

            <!-- Schedule Slot -->
            <div class="card-custom p-4 mb-3">
                <h6 class="fw-bold border-bottom pb-2 mb-3">Preferred Date & Arrival Time</h6>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Date</label>
                        <input type="date" id="booking_date" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Slot</label>
                        <select id="booking_slot" class="form-select">
                            <option value="10:00 AM - 12:00 PM">10:00 AM - 12:00 PM</option>
                            <option value="02:00 PM - 04:00 PM">02:00 PM - 04:00 PM</option>
                            <option value="04:00 PM - 06:00 PM">04:00 PM - 06:00 PM</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Select Address -->
            <div class="card-custom p-4">
                <h6 class="fw-bold border-bottom pb-2 mb-3">Select Service Address</h6>
                <?php if (empty($addresses)): ?>
                    <div class="alert alert-warning small">No address on file! <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/profile">Add Address</a> first.</div>
                <?php else: ?>
                    <select id="selected_address" class="form-select">
                        <?php foreach ($addresses as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['label']) ?>: <?= htmlspecialchars($a['address_line1']) ?>, <?= htmlspecialchars($a['city']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Summary & Payment -->
        <div class="col-12 col-lg-5">
            <div class="card-custom p-4">
                <h6 class="fw-bold border-bottom pb-2 mb-3">Payment Summary</h6>
                
                <!-- Coupon Input -->
                <div class="input-group mb-3">
                    <input type="text" id="coupon_code" class="form-control text-uppercase" placeholder="Coupon (e.g. FIX20)">
                    <button id="applyCouponButton" class="btn btn-outline-dark" type="button" onclick="applyCoupon()">Apply</button>
                </div>
                <div id="couponMsg" class="small mb-2"></div>

                <div class="small mb-3">
                    <div class="d-flex justify-content-between py-1"><span>Subtotal:</span> <span id="lblSubtotal">₹0.00</span></div>
                    <div class="d-flex justify-content-between py-1 text-success"><span>Discount:</span> <span id="lblDiscount">- ₹0.00</span></div>
                    <div class="d-flex justify-content-between py-1 text-muted"><span>GST (18%):</span> <span id="lblTax">₹0.00</span></div>
                    <div class="d-flex justify-content-between py-2 border-top fw-bold fs-5 text-dark"><span>Payable Amount:</span> <span id="lblTotal">₹0.00</span></div>
                </div>

                <h6 class="fw-bold border-top pt-3 mb-2">Payment Method</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="payment_method" id="pay_online" value="online" <?= !empty($onlinePaymentsEnabled) ? 'checked' : 'disabled' ?>>
                    <label class="form-check-label fw-semibold <?= empty($onlinePaymentsEnabled) ? 'text-muted' : '' ?>" for="pay_online">Pay online securely</label>
                    <?php if (!empty($onlinePaymentsEnabled)): ?>
                        <div class="d-flex flex-wrap gap-2 ms-4 mt-2"><span class="badge bg-light text-dark border">UPI</span><span class="badge bg-light text-dark border">Cards</span><span class="badge bg-light text-dark border">NetBanking</span><span class="badge bg-light text-dark border">Wallets</span></div>
                        <div class="small text-muted ms-4 mt-1">Secure checkout powered by Razorpay. Payment is confirmed only after server verification.</div>
                    <?php else: ?>
                        <div class="small text-muted ms-4">Online payment is temporarily unavailable. Choose cash below; Fixmate support must configure the payment gateway to enable UPI.</div>
                    <?php endif; ?>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="cod" <?= empty($onlinePaymentsEnabled) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold" for="pay_cod">Cash on service completion</label>
                </div>

                <button class="btn btn-primary w-100 rounded-pill py-2 fw-bold" onclick="placeOrder()">Confirm & Place Booking</button>
            </div>
        </div>
    </div>
</div>

<script>
const appBaseUrl = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
let appliedCouponId = null;
let discountAmount = 0.00;
let selectedProfessional = null;

try {
    selectedProfessional = JSON.parse(localStorage.getItem('fixmate_selected_professional') || 'null');
} catch (error) {
    selectedProfessional = null;
}
if (selectedProfessional && Number(selectedProfessional.id) > 0) {
    document.getElementById('selectedProfessionalSummary').hidden = false;
    document.getElementById('selectedProfessionalName').textContent = selectedProfessional.name || 'Selected professional';
}

function renderCart() {
    const cart = JSON.parse(localStorage.getItem('fixmate_cart') || '[]');
    const container = document.getElementById('cartItemsContainer');
    if (cart.length === 0) {
        container.innerHTML = `<div class="text-muted small py-3">Your cart is empty. <a href="${appBaseUrl}/customer">Browse Services</a></div>`;
        updateTotals(0);
        return;
    }

    let subtotal = 0;
    container.innerHTML = cart.map((item, idx) => {
        const itemTotal = item.base_price * item.quantity;
        subtotal += itemTotal;
        return `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <div class="fw-bold">${item.name}</div>
                    <div class="text-muted small">₹${item.base_price} x ${item.quantity}</div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold">₹${itemTotal.toFixed(2)}</span>
                    <button class="btn btn-sm btn-outline-danger border-0" onclick="removeItem(${idx})"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
    }).join('');

    updateTotals(subtotal);
}

function updateTotals(subtotal) {
    const tax = Math.round((subtotal - discountAmount) * 0.18);
    const total = Math.max(0, subtotal - discountAmount + tax);

    document.getElementById('lblSubtotal').innerText = '₹' + subtotal.toFixed(2);
    document.getElementById('lblDiscount').innerText = '- ₹' + discountAmount.toFixed(2);
    document.getElementById('lblTax').innerText = '+ ₹' + tax.toFixed(2);
    document.getElementById('lblTotal').innerText = '₹' + total.toFixed(2);
}

function removeItem(idx) {
    let cart = JSON.parse(localStorage.getItem('fixmate_cart') || '[]');
    cart.splice(idx, 1);
    localStorage.setItem('fixmate_cart', JSON.stringify(cart));
    refreshCartBadge();
    appliedCouponId = null;
    discountAmount = 0;
    document.getElementById('couponMsg').textContent = 'Cart changed. Apply your coupon again to refresh the discount.';
    document.getElementById('couponMsg').className = 'small text-muted mb-2';
    renderCart();
}

function applyCoupon() {
    const code = document.getElementById('coupon_code').value.trim();
    const cart = JSON.parse(localStorage.getItem('fixmate_cart') || '[]');
    const subtotal = cart.reduce((acc, i) => acc + (i.base_price * i.quantity), 0);

    const formData = new FormData();
    formData.append('code', code);
    formData.append('subtotal', subtotal);

    fetch(`${appBaseUrl}/customer/checkout/apply-coupon`, { method: 'POST', body: formData })
        .then(async response => {
            const result = await response.json();
            if (!response.ok && result.success) throw new Error('Coupon could not be validated.');
            return result;
        })
        .then(res => {
            const msgEl = document.getElementById('couponMsg');
            if (res.success) {
                appliedCouponId = res.coupon_id;
                discountAmount = Number(res.discount_amount);
                msgEl.className = 'small text-success mb-2';
                msgEl.innerText = `Coupon '${res.code}' applied! Saved ₹${discountAmount}`;
                updateTotals(subtotal);
            } else {
                appliedCouponId = null;
                discountAmount = 0;
                msgEl.className = 'small text-danger mb-2';
                msgEl.innerText = res.message || 'Coupon could not be applied.';
                updateTotals(subtotal);
            }
        })
        .catch(error => {
            appliedCouponId = null;
            discountAmount = 0;
            const msgEl = document.getElementById('couponMsg');
            msgEl.className = 'small text-danger mb-2';
            msgEl.textContent = error.message || 'Coupon could not be validated.';
            updateTotals(subtotal);
        });
}

function placeOrder() {
    const cart = JSON.parse(localStorage.getItem('fixmate_cart') || '[]');
    if (cart.length === 0) { alert('Cart is empty.'); return; }

    const addressId = document.getElementById('selected_address') ? document.getElementById('selected_address').value : null;
    if (!addressId) { alert('Please select a service address.'); return; }

    const subtotal = cart.reduce((acc, i) => acc + (i.base_price * i.quantity), 0);
    const tax = Math.round((subtotal - discountAmount) * 0.18);
    const total = Math.max(0, subtotal - discountAmount + tax);

    const payload = {
        items: cart,
        order: {
            address_id: addressId,
            scheduled_date: document.getElementById('booking_date').value,
            scheduled_time_slot: document.getElementById('booking_slot').value,
            coupon_id: appliedCouponId,
            subtotal: subtotal,
            discount_amount: discountAmount,
            surge_amount: 0.00,
            tax_amount: tax,
            total_amount: total,
            payment_method: document.querySelector('input[name="payment_method"]:checked').value,
            professional_id: selectedProfessional && Number(selectedProfessional.id) > 0 ? Number(selectedProfessional.id) : null
        }
    };

    const bookingError = document.getElementById('bookingError');
    bookingError.hidden = true;
    const submitButton = document.querySelector('button[onclick="placeOrder()"]');
    submitButton.disabled = true;
    fetch(`${appBaseUrl}/customer/checkout/place-order`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(async response => {
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Could not place this booking.');
        return result;
    })
    .then(res => {
        if (res.success) {
            localStorage.removeItem('fixmate_cart');
            localStorage.removeItem('fixmate_selected_professional');
            refreshCartBadge();
            if (payload.order.payment_method === 'online') {
                window.startFixmatePayment(res.booking_code, function (message) {
                    bookingError.textContent = `${message} Booking ${res.booking_code} is saved as pending; you can retry from My bookings.`;
                    bookingError.hidden = false;
                    submitButton.disabled = false;
                    const bookingsLink = document.createElement('a');
                    bookingsLink.href = `${appBaseUrl}/customer/my-bookings`;
                    bookingsLink.className = 'alert-link d-block mt-2';
                    bookingsLink.textContent = 'Open My bookings';
                    bookingError.appendChild(bookingsLink);
                });
            } else {
                alert(`Booking Confirmed! Ref: ${res.booking_code}`);
                window.location.href = `${appBaseUrl}/customer/my-bookings`;
            }
        } else {
            bookingError.textContent = res.message || 'Failed to place booking.';
            bookingError.hidden = false;
            submitButton.disabled = false;
        }
    })
    .catch(error => {
        bookingError.textContent = error.message || 'Network error placing booking.';
        bookingError.hidden = false;
        submitButton.disabled = false;
    });
}

document.addEventListener('DOMContentLoaded', renderCart);
</script>