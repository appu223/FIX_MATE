<style>
    .workspace-launcher { max-width: 1080px; margin: 4vh auto; }
    .workspace-card { height: 100%; border: 1px solid #e3e9f4; border-radius: 20px; background: #fff; box-shadow: 0 12px 35px rgb(31 50 93 / 7%); transition: transform .18s ease, box-shadow .18s ease; }
    .workspace-card:hover { transform: translateY(-3px); box-shadow: 0 18px 42px rgb(31 50 93 / 12%); }
    .workspace-mark { display:grid; width:54px; height:54px; place-items:center; border-radius:17px; color:#fff; background:linear-gradient(135deg,#3157d5,#6685ed); font-size:1.4rem; }
    .workspace-note { border-radius:14px; background:#edf3ff; color:#475b91; }
</style>
<div class="workspace-launcher px-3">
    <div class="text-center mb-4">
        <span class="badge rounded-pill text-bg-primary px-3 py-2">Fixmate workspace launcher</span>
        <h1 class="display-6 fw-bold mt-3">Open panels side by side</h1>
        <p class="text-secondary mx-auto" style="max-width:650px">Open Customer, Technician, and Admin workspaces in separate tabs. Each panel signs in independently and stays active while you switch between them.</p>
    </div>
    <div class="alert workspace-note border-0 mb-4" role="note">
        <strong>How to use:</strong> Open each role in a new tab, sign in with that role's account, and leave the tabs open. Signing out from one panel will not sign out the other panels.
    </div>
    <div class="row g-4">
        <div class="col-12 col-md-4">
            <article class="workspace-card p-4 d-flex flex-column">
                <span class="workspace-mark mb-3"><i class="bi bi-person-circle"></i></span>
                <h2 class="h4 fw-bold">Customer</h2>
                <p class="text-secondary flex-grow-1">Browse services, manage bookings, chat with the technician, confirm completion, and download bills.</p>
                <a class="btn btn-primary rounded-pill" href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/landing?workspace=customer" target="_blank" rel="noopener">Open Customer panel <i class="bi bi-box-arrow-up-right ms-1"></i></a>
            </article>
        </div>
        <div class="col-12 col-md-4">
            <article class="workspace-card p-4 d-flex flex-column">
                <span class="workspace-mark mb-3" style="background:linear-gradient(135deg,#147d5a,#47b38b)"><i class="bi bi-tools"></i></span>
                <h2 class="h4 fw-bold">Technician</h2>
                <p class="text-secondary flex-grow-1">Manage assigned work, chat with customers, request and verify completion codes, and track wallet credit.</p>
                <a class="btn btn-primary rounded-pill" href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/landing?workspace=professional" target="_blank" rel="noopener">Open Technician panel <i class="bi bi-box-arrow-up-right ms-1"></i></a>
            </article>
        </div>
        <div class="col-12 col-md-4">
            <article class="workspace-card p-4 d-flex flex-column">
                <span class="workspace-mark mb-3" style="background:linear-gradient(135deg,#7643b8,#aa79dc)"><i class="bi bi-shield-lock"></i></span>
                <h2 class="h4 fw-bold">Admin</h2>
                <p class="text-secondary flex-grow-1">Review work proofs, see live completion and payment status, and monitor technician commission and payouts.</p>
                <a class="btn btn-primary rounded-pill" href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/landing?workspace=admin" target="_blank" rel="noopener">Open Admin panel <i class="bi bi-box-arrow-up-right ms-1"></i></a>
            </article>
        </div>
    </div>
    <div class="text-center mt-4"><a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/landing" class="text-decoration-none">Back to Fixmate home</a></div>
</div>
