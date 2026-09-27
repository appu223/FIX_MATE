/* Razorpay checkout shared by customer checkout and booking-history retry. */
(function () {
    'use strict';

    function getBasePath() {
        return window.fixmateBasePath || '';
    }

    window.startFixmatePayment = async function (bookingCode, onMessage) {
        const notify = typeof onMessage === 'function' ? onMessage : function (message) { window.alert(message); };
        if (typeof window.Razorpay !== 'function') {
            notify('The secure payment window did not load. Check your connection and try again.');
            return;
        }

        try {
            const response = await fetch(`${getBasePath()}/customer/checkout/payment-order`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ booking_code: bookingCode })
            });
            const order = await response.json();
            if (!response.ok || !order.success) throw new Error(order.message || 'Could not start online payment.');

            const checkout = new window.Razorpay({
                key: order.key_id,
                amount: order.amount,
                currency: order.currency,
                name: 'Fixmate',
                description: `Booking ${order.booking_code}`,
                order_id: order.order_id,
                method: { upi: true, card: true, netbanking: true, wallet: true },
                theme: { color: '#3459d4' },
                modal: {
                    ondismiss: function () {
                        notify('Payment was not completed. Your booking is saved as unpaid; use Pay online from My bookings to retry.');
                    }
                },
                handler: async function (paymentResult) {
                    try {
                        const verifyResponse = await fetch(`${getBasePath()}/customer/checkout/payment-verify`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(paymentResult)
                        });
                        const verification = await verifyResponse.json();
                        if (!verifyResponse.ok || !verification.success) throw new Error(verification.message || 'Payment verification failed.');
                        window.location.href = `${getBasePath()}/customer/my-bookings`;
                    } catch (error) {
                        notify(`${error.message} If money was debited, contact Fixmate support with booking ${order.booking_code}.`);
                    }
                }
            });
            checkout.on('payment.failed', function (event) {
                const message = event && event.error && event.error.description
                    ? event.error.description
                    : 'Payment failed. You can retry from My bookings.';
                notify(message);
            });
            checkout.open();
        } catch (error) {
            notify(error.message || 'Could not start online payment. Your booking remains unpaid.');
        }
    };
}());
