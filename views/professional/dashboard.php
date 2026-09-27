<div class="container-fluid p-0">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="status"><?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($profile['name']) ?>!</h3>
            <p class="text-muted m-0 small">Live technician dispatch radar, active job requests, and duty status.</p>
        </div>
        <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/fulfillment" class="btn btn-primary rounded-pill px-4 fw-semibold">
            <i class="bi bi-play-circle me-1"></i> Open Active Job Board
        </a>
    </div>

    <?php
        $kycStatus = (string)($profile['kyc_status'] ?? 'pending');
        $kycClass = match ($kycStatus) { 'verified' => 'success', 'rejected' => 'danger', default => 'warning' };
        $kycHeading = match ($kycStatus) {
            'verified' => 'Identity verified — you are approved to work',
            'rejected' => 'Action required — your KYC documents were not approved',
            default => 'Verification in progress — your documents are under review',
        };
    ?>
    <section id="proKycStatusCard" class="card-custom p-3 p-md-4 mb-4 border-start border-4 border-<?= $kycClass ?>" aria-labelledby="kyc-status-heading">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="text-<?= $kycClass ?> small fw-bold text-uppercase"><i class="bi bi-shield-check me-1"></i>KYC verification · <span id="proKycStatusLabel"><?= htmlspecialchars(strtoupper($kycStatus), ENT_QUOTES, 'UTF-8') ?></span></div>
                <h5 id="kyc-status-heading" class="fw-bold mt-1 mb-1"><?= htmlspecialchars($kycHeading, ENT_QUOTES, 'UTF-8') ?></h5>
                <p id="proKycStatusDetail" class="mb-0 <?= $kycStatus === 'rejected' ? 'text-danger' : 'text-muted' ?> small">
                    <?php if ($kycStatus === 'rejected' && !empty($profile['kyc_rejected_reason'])): ?>
                        <strong>Admin feedback:</strong> <?= nl2br(htmlspecialchars($profile['kyc_rejected_reason'], ENT_QUOTES, 'UTF-8')) ?>
                    <?php elseif ($kycStatus === 'pending'): ?>
                        Your dashboard will show the result as soon as the administration reviews your dossier.
                    <?php else: ?>
                        Your verified status is active on your technician account.
                    <?php endif; ?>
                </p>
            </div>
            <a id="proKycDossierLink" href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/kyc" class="btn btn-outline-<?= $kycClass ?> rounded-pill px-4 flex-shrink-0">
                <?= $kycStatus === 'rejected' ? 'Update my documents' : 'Open KYC dossier' ?>
            </a>
        </div>
    </section>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-5">
            <section class="card-custom p-4 h-100" aria-labelledby="pro-notifications-heading">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <div><h5 id="pro-notifications-heading" class="fw-bold mb-0"><i class="bi bi-bell-fill text-primary me-2"></i>Notifications</h5><div class="small text-muted">Updates from Fixmate administration</div></div>
                    <span id="proNotificationCount" class="badge text-bg-danger rounded-pill" <?= ($notifications['unread_count'] ?? 0) > 0 ? '' : 'hidden' ?>><?= (int)($notifications['unread_count'] ?? 0) ?> new</span>
                </div>
                <div id="proNotificationsList" class="d-grid gap-2">
                    <?php if (empty($notifications['items'])): ?>
                        <p class="text-muted small mb-0">No notifications yet. KYC decisions and account updates will appear here.</p>
                    <?php else: ?>
                        <?php foreach ($notifications['items'] as $notification): ?>
                            <a class="text-decoration-none text-reset p-3 rounded-3 <?= empty($notification['is_read']) ? 'bg-primary-subtle border border-primary-subtle' : 'bg-light border' ?>" href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') . htmlspecialchars($notification['link'] ?: '/pro/dashboard', ENT_QUOTES, 'UTF-8') ?>">
                                <div class="d-flex justify-content-between gap-2"><strong class="small"><?= htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8') ?></strong><time class="text-muted" style="font-size:.7rem"><?= htmlspecialchars(date('d M, H:i', strtotime($notification['created_at'])), ENT_QUOTES, 'UTF-8') ?></time></div>
                                <div class="small text-muted mt-1"><?= htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8') ?></div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
        <div class="col-12 col-xl-7">
            <section class="card-custom p-4 h-100" id="admin-support-chat" aria-labelledby="admin-support-chat-heading">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <div><h5 id="admin-support-chat-heading" class="fw-bold mb-0"><i class="bi bi-chat-square-text-fill text-primary me-2"></i>Admin Support Chat</h5><div class="small text-muted">Direct conversation with Fixmate administration</div></div>
                    <span id="proAdminUnreadBadge" class="badge text-bg-primary rounded-pill" <?= $adminUnreadCount > 0 ? '' : 'hidden' ?>><?= (int)$adminUnreadCount ?> new</span>
                </div>
                <div id="proAdminChatMessages" class="d-flex flex-column gap-2 mb-3" style="max-height:270px;overflow-y:auto" aria-live="polite">
                    <?php if (empty($adminMessages)): ?>
                        <p class="text-muted small mb-0">No messages yet. Send a message to contact the admin team about your verification or account.</p>
                    <?php else: ?>
                        <?php foreach ($adminMessages as $message): ?>
                            <?php $ownMessage = (int)$message['sender_user_id'] === \App\Core\Auth::id(); ?>
                            <div class="align-self-<?= $ownMessage ? 'end' : 'start' ?> p-3 rounded-3 <?= $ownMessage ? 'bg-primary text-white' : 'bg-light border' ?>" style="max-width:90%">
                                <div class="small fw-bold mb-1"><?= htmlspecialchars($ownMessage ? 'You' : ($message['sender_name'] . ' · Fixmate Admin'), ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="small" style="white-space:pre-wrap"><?= htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8') ?></div>
                                <time class="d-block text-end mt-1 <?= $ownMessage ? 'text-white-50' : 'text-muted' ?>" style="font-size:.68rem"><?= htmlspecialchars(date('d M, H:i', strtotime($message['created_at'])), ENT_QUOTES, 'UTF-8') ?></time>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/admin-chat/send" method="POST" class="d-flex gap-2">
                    <label class="visually-hidden" for="pro-admin-message">Message to administration</label>
                    <input id="pro-admin-message" name="message" class="form-control" maxlength="4000" placeholder="Write a message to the admin team…" required>
                    <button class="btn btn-primary rounded-pill px-4" type="submit"><i class="bi bi-send-fill me-1"></i>Send</button>
                </form>
            </section>
        </div>
    </div>

    <!-- 4 KPI Stat Cards with 4px left borders -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-custom p-3 kpi-green h-100">
                <div class="text-muted text-uppercase small fw-bold">Wallet Balance</div>
                <h3 class="fw-bold text-dark mt-2 mb-0">₹<?= number_format((float)($metrics['wallet_balance'] ?? 0), 2) ?></h3>
                <div class="small text-success mt-2"><i class="bi bi-check-circle"></i> Available to Withdraw</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-custom p-3 kpi-blue h-100">
                <div class="text-muted text-uppercase small fw-bold">Completed Jobs</div>
                <h3 class="fw-bold text-dark mt-2 mb-0"><?= $metrics['completed_jobs'] ?? 0 ?></h3>
                <div class="small text-primary mt-2"><i class="bi bi-patch-check"></i> Lifetime Fulfillment</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-custom p-3 kpi-amber h-100">
                <div class="text-muted text-uppercase small fw-bold">Active Jobs On-Route</div>
                <h3 class="fw-bold text-dark mt-2 mb-0"><?= $metrics['active_jobs'] ?? 0 ?></h3>
                <div class="small text-warning mt-2"><i class="bi bi-hourglass-split"></i> Dispatched to you</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-custom p-3 kpi-purple h-100">
                <div class="text-muted text-uppercase small fw-bold">Client Rating</div>
                <h3 class="fw-bold text-dark mt-2 mb-0">★ <?= number_format((float)($metrics['rating_avg'] ?? 5.0), 2) ?></h3>
                <div class="small text-muted mt-2">Based on <?= $metrics['rating_count'] ?? 0 ?> verified reviews</div>
            </div>
        </div>
    </div>

    <!-- Active Jobs Section -->
    <div class="card-custom p-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Urgent Today's Schedule</h5>
        <?php if (empty($jobs)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-cup-hot fs-1 text-secondary mb-2 d-block"></i>
                No active dispatches right now. Make sure your schedule is toggled on to receive new bookings!
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($jobs as $j): ?>
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary rounded-pill"><?= htmlspecialchars($j['booking_code']) ?></span>
                                <span class="badge bg-dark rounded-pill"><?= strtoupper(str_replace('_', ' ', $j['status'])) ?></span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($j['customer_name']) ?> (<?= htmlspecialchars($j['customer_phone']) ?>)</h6>
                            <p class="text-muted small mb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($j['address_line1']) ?>, <?= htmlspecialchars($j['city']) ?></p>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                <span class="fw-bold text-success">Your Earning: ₹<?= number_format((float)$j['pro_earning'], 2) ?></span>
                                <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/fulfillment" class="btn btn-sm btn-dark rounded-pill px-3">Start Execution</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
(function () {
    const updateUrl = <?= json_encode(($baseUrl ?? '') . '/pro/dashboard/updates', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const baseUrl = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const kycLabels = {
        verified: 'Identity verified — you are approved to work',
        rejected: 'Action required — your KYC documents were not approved',
        pending: 'Verification in progress — your documents are under review'
    };

    async function refreshDashboardUpdates() {
        try {
            const response = await fetch(updateUrl, { headers: { 'Accept': 'application/json' } });
            const result = await response.json();
            if (!response.ok || !result.success) return;

            const status = ['verified', 'rejected', 'pending'].includes(result.kyc_status) ? result.kyc_status : 'pending';
            const tone = status === 'verified' ? 'success' : (status === 'rejected' ? 'danger' : 'warning');
            const statusCard = document.getElementById('proKycStatusCard');
            statusCard.classList.remove('border-success', 'border-danger', 'border-warning');
            statusCard.classList.add(`border-${tone}`);
            document.getElementById('proKycStatusLabel').textContent = status.toUpperCase();
            document.getElementById('proKycStatusLabel').parentElement.classList.remove('text-success', 'text-danger', 'text-warning');
            document.getElementById('proKycStatusLabel').parentElement.classList.add(`text-${tone}`);
            document.getElementById('kyc-status-heading').textContent = kycLabels[status];
            const detail = document.getElementById('proKycStatusDetail');
            detail.classList.toggle('text-danger', status === 'rejected');
            detail.classList.toggle('text-muted', status !== 'rejected');
            detail.replaceChildren();
            if (status === 'rejected' && result.kyc_rejected_reason) {
                const label = document.createElement('strong');
                label.textContent = 'Admin feedback: ';
                detail.append(label, document.createTextNode(result.kyc_rejected_reason));
            } else {
                detail.textContent = status === 'pending'
                    ? 'Your dashboard will show the result as soon as the administration reviews your dossier.'
                    : (status === 'verified' ? 'Your verified status is active on your technician account.' : 'Please open your dossier and contact administration if you need clarification.');
            }
            const dossierLink = document.getElementById('proKycDossierLink');
            dossierLink.classList.remove('btn-outline-success', 'btn-outline-danger', 'btn-outline-warning');
            dossierLink.classList.add(`btn-outline-${tone}`);
            dossierLink.textContent = status === 'rejected' ? 'Update my documents' : 'Open KYC dossier';

            const notificationList = document.getElementById('proNotificationsList');
            notificationList.replaceChildren();
            if (!result.notifications.items.length) {
                const empty = document.createElement('p');
                empty.className = 'text-muted small mb-0';
                empty.textContent = 'No notifications yet. KYC decisions and account updates will appear here.';
                notificationList.appendChild(empty);
            } else {
                result.notifications.items.forEach(function (notification) {
                    const item = document.createElement('a');
                    item.className = `text-decoration-none text-reset p-3 rounded-3 ${Number(notification.is_read) ? 'bg-light border' : 'bg-primary-subtle border border-primary-subtle'}`;
                    item.href = `${baseUrl}${notification.link || '/pro/dashboard'}`;
                    const header = document.createElement('div');
                    header.className = 'd-flex justify-content-between gap-2';
                    const title = document.createElement('strong');
                    title.className = 'small';
                    title.textContent = notification.title;
                    const date = document.createElement('time');
                    date.className = 'text-muted';
                    date.style.fontSize = '.7rem';
                    date.textContent = new Date(notification.created_at.replace(' ', 'T')).toLocaleString();
                    header.append(title, date);
                    const message = document.createElement('div');
                    message.className = 'small text-muted mt-1';
                    message.textContent = notification.message;
                    item.append(header, message);
                    notificationList.appendChild(item);
                });
            }
            const notificationCount = document.getElementById('proNotificationCount');
            notificationCount.textContent = `${result.notifications.unread_count} new`;
            notificationCount.hidden = result.notifications.unread_count < 1;
            const chatCount = document.getElementById('proAdminUnreadBadge');
            chatCount.textContent = `${result.admin_unread_count} new`;
            chatCount.hidden = result.admin_unread_count < 1;
        } catch (error) {
            // Keep the last rendered dashboard status if the network is briefly unavailable.
        }
    }

    const messagesBox = document.getElementById('proAdminChatMessages');
    if (!messagesBox) return;
    async function refreshAdminMessages() {
        try {
            const response = await fetch(`${baseUrl}/pro/admin-chat/messages`, { headers: { 'Accept': 'application/json' } });
            const result = await response.json();
            if (!response.ok || !result.success) return;
            const scrollAtBottom = messagesBox.scrollHeight - messagesBox.scrollTop - messagesBox.clientHeight < 40;
            messagesBox.replaceChildren();
            if (!result.messages.length) {
                const empty = document.createElement('p');
                empty.className = 'text-muted small mb-0';
                empty.textContent = 'No messages yet. Send a message to contact the admin team about your verification or account.';
                messagesBox.appendChild(empty);
                return;
            }
            result.messages.forEach(function (message) {
                const own = Number(message.sender_user_id) === <?= \App\Core\Auth::id() ?>;
                const bubble = document.createElement('div');
                bubble.className = `align-self-${own ? 'end' : 'start'} p-3 rounded-3 ${own ? 'bg-primary text-white' : 'bg-light border'}`;
                bubble.style.maxWidth = '90%';
                const name = document.createElement('div');
                name.className = 'small fw-bold mb-1';
                name.textContent = own ? 'You' : `${message.sender_name} · Fixmate Admin`;
                const text = document.createElement('div');
                text.className = 'small';
                text.style.whiteSpace = 'pre-wrap';
                text.textContent = message.message;
                const time = document.createElement('time');
                time.className = `d-block text-end mt-1 ${own ? 'text-white-50' : 'text-muted'}`;
                time.style.fontSize = '.68rem';
                time.textContent = new Date(message.created_at.replace(' ', 'T')).toLocaleString();
                bubble.append(name, text, time);
                messagesBox.appendChild(bubble);
            });
            if (scrollAtBottom) messagesBox.scrollTop = messagesBox.scrollHeight;
        } catch (error) {
            // Keep the messages already rendered if the network is temporarily unavailable.
        }
    }
    refreshDashboardUpdates();
    window.setInterval(refreshDashboardUpdates, 8000);
    window.setInterval(refreshAdminMessages, 7000);
}());
</script>