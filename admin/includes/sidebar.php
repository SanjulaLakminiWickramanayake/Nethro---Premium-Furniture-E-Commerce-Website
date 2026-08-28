<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$navItems = [
    ['label' => 'Dashboard', 'link' => 'dashboard.php', 'icon' => 'layout-grid'],
    ['label' => 'Add Product', 'link' => 'add_product.php', 'icon' => 'plus-circle'],
    ['label' => 'Manage Products', 'link' => 'manage_products.php', 'icon' => 'boxes'],
    ['label' => 'Customers', 'link' => 'customers.php', 'icon' => 'users'],
    ['label' => 'Payments', 'link' => 'payments.php', 'icon' => 'credit-card'],
    ['label' => 'Back to Site', 'link' => '../index.php', 'icon' => 'arrow-left'],
    ['label' => 'Logout', 'link' => '../logout.php', 'icon' => 'log-out'],
];
?>

<aside id="adminSidebar" class="fixed left-0 top-20 hidden h-[calc(100vh-5rem)] w-72 flex-col border-r border-slate-700 bg-[#1E293B] p-4 text-slate-200 lg:flex">
    <div class="mb-6 flex items-center gap-3 border-b border-slate-700 pb-4 pt-2">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white">
            <i data-lucide="armchair" class="h-5 w-5"></i>
        </div>
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Store</p>
            <p class="font-semibold text-white">Nethro Admin</p>
        </div>
    </div>

    <nav class="space-y-1">
        <?php foreach ($navItems as $item): ?>
            <?php $isActive = $currentPage === $item['link']; ?>
            <a href="<?php echo htmlspecialchars($item['link']); ?>" class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition <?php echo $isActive ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                <i data-lucide="<?php echo htmlspecialchars($item['icon']); ?>" class="h-4 w-4"></i>
                <span><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

<div id="mobileSidebar" class="fixed left-0 top-20 z-40 h-[calc(100vh-5rem)] w-72 -translate-x-full border-r border-slate-700 bg-[#1E293B] p-4 text-slate-200 transition duration-200 lg:hidden">
    <div class="mb-6 flex items-center justify-between border-b border-slate-700 pb-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white">
                <i data-lucide="armchair" class="h-5 w-5"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Store</p>
                <p class="font-semibold text-white">Nethro Admin</p>
            </div>
        </div>
        <button type="button" id="closeMobileSidebar" class="rounded-lg p-2 text-slate-300 hover:bg-slate-800 hover:text-white" aria-label="Close sidebar">
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    </div>

    <nav class="space-y-1">
        <?php foreach ($navItems as $item): ?>
            <?php $isActive = $currentPage === $item['link']; ?>
            <a href="<?php echo htmlspecialchars($item['link']); ?>" class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition <?php echo $isActive ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                <i data-lucide="<?php echo htmlspecialchars($item['icon']); ?>" class="h-4 w-4"></i>
                <span><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</div>

<div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-950/50 lg:hidden"></div>

<main class="flex-1 p-4 sm:p-6 lg:ml-72">

<script>
    function closeSidebarMobile() {
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.add('-translate-x-full');
        if (overlay) overlay.classList.add('hidden');
    }

    const sidebarToggle = document.getElementById('sidebarToggle');
    const closeMobileSidebarBtn = document.getElementById('closeMobileSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            const sidebar = document.getElementById('mobileSidebar');
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
            }
            if (overlay) {
                overlay.classList.toggle('hidden');
            }
        });
    }

    if (closeMobileSidebarBtn) {
        closeMobileSidebarBtn.addEventListener('click', closeSidebarMobile);
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebarMobile);
    }

    document.querySelectorAll('#mobileSidebar a').forEach(function (link) {
        link.addEventListener('click', closeSidebarMobile);
    });

    if (window.lucide) {
        lucide.createIcons();
    }
</script>
