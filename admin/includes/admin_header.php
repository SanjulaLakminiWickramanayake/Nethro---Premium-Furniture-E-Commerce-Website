<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | Nethro Admin' : 'Nethro Admin'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8'
                        }
                    }
                }
            }
        };
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
        });
    </script>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <header class="fixed inset-x-0 top-0 z-40 h-20 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="flex h-full items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <button id="sidebarToggle" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 lg:hidden" aria-label="Open sidebar">
                    <i data-lucide="menu" class="h-5 w-5"></i>
                </button>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white">
                    <i data-lucide="armchair" class="h-5 w-5"></i>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">Control Panel</p>
                    <h1 class="text-lg font-bold tracking-tight text-slate-900">Nethro Admin</h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600 sm:inline-flex">
                    <?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'admin@example.com'); ?>
                </span>
                <a href="../logout.php" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                    <i data-lucide="log-out" class="h-4 w-4"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="fixed right-4 top-24 z-50 rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg <?php echo $flash['type'] === 'error' ? 'bg-red-600' : 'bg-emerald-600'; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="pt-20">
