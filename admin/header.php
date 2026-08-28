<?php
$adminPage = basename($_SERVER['PHP_SELF']);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Admin'); ?> - Nethro Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { navy: '#1E293B', accent: '#3B82F6' },
                    fontFamily: { sans: ['Poppins', 'sans-serif'], display: ['Playfair Display', 'serif'] }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8fafc; }
        .admin-main { padding: 32px 24px; }
        @media (max-width: 768px) {
            .admin-main { padding: 20px 16px; }
        }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Top Navbar -->
    <header class="fixed top-0 left-0 right-0 bg-white border-b border-slate-200 z-40 h-20">
        <div class="flex items-center justify-between h-full px-4 sm:px-6">
            <div class="flex items-center gap-3 flex-1 sm:flex-none">
                <button id="mobileSidebarToggle" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg transition">
                    <i class="fas fa-bars text-slate-600 text-lg"></i>
                </button>
                <a href="dashboard.php" class="flex items-center gap-2 font-display text-lg font-bold text-slate-900">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-500 text-white text-sm">
                        <i class="fas fa-couch"></i>
                    </span>
                    <span class="hidden sm:inline">Nethro Admin</span>
                </a>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-600 hidden sm:inline"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                <a href="../logout.php" class="inline-flex items-center justify-center w-9 h-9 rounded-lg hover:bg-slate-100 transition text-slate-600" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>
<?php if ($flash): ?>
    <div class="fixed right-4 top-24 z-50 rounded-xl bg-<?php echo $flash['type'] === 'error' ? 'red' : 'emerald'; ?>-600 px-5 py-3 text-sm font-semibold text-white shadow-lg" onclick="this.remove()">
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
<?php endif; ?>
<div class="admin-shell">
    <div id="adminOverlay" class="admin-overlay fixed inset-0 z-30 bg-slate-900/50"></div>
    <div class="admin-content">
        <header class="admin-topbar flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-8">
            <div class="flex items-center gap-3">
                <button id="adminMenuButton" type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Open navigation">
                    <i data-lucide="menu" class="h-5 w-5"></i>
                </button>
                <div class="flex items-center gap-2 lg:hidden"><span class="rounded-lg bg-blue-600 p-2 text-white"><i data-lucide="sofa" class="h-4 w-4"></i></span><span class="font-display text-lg font-bold text-slate-900">Nethro</span></div>
            </div>
            <div class="flex items-center gap-3 text-sm text-slate-500"><span class="hidden sm:inline">Admin workspace</span><span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-50 font-bold text-blue-600">A</span></div>
        </header>
        <div class="p-4 sm:p-8">
