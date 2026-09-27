<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Fixmate document', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/css/custom.css" rel="stylesheet">
    <style>
        body { min-height:100vh; padding:24px; background:#eef1f6; }
        .invoice-card { max-width:800px; margin:30px auto; padding:clamp(1.25rem,5vw,2.5rem); border:1px solid var(--fm-border); border-radius:var(--fm-radius-md); background:#fff; box-shadow:var(--fm-shadow-card); }
        @media print { body { padding:0; background:#fff; } .no-print { display:none!important; } }
        @media print { .invoice-card { max-width:none; margin:0; padding:0; border:0; border-radius:0; box-shadow:none; } }
    </style>
</head>
<body>
    <?= $viewContent ?>
</body>
</html>