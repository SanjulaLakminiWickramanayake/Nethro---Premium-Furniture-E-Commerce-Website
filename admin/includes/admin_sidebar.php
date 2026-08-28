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

<aside class="fixed left-0 top-20 hidden h-[calc(100vh-5rem)] w-64 border-r border-slate-700 bg-slate-800 p-4 text-slate-200 lg:block">
    <nav class="space-y-1">
        <?php foreach ($navItems as $item): ?>
            <?php $isActive = $currentPage === $item['link']; ?>
            <a href="<?php echo htmlspecialchars($item['link']); ?>" class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition <?php echo $isActive ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?>">
                <i data-lucide="<?php echo htmlspecialchars($item['icon']); ?>" class="h-4 w-4"></i>
                <span><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

<div id="mobileSidebar" class="fixed left-0 top-20 z-50 h-[calc(100vh-5rem)] w-64 -translate-x-full border-r border-slate-700 bg-slate-800 p-4 text-slate-200 transition-transform duration-200 lg:hidden">
    <nav class="space-y-1">
        <?php foreach ($navItems as $item): ?>
            <?php $isActive = $currentPage === $item['link']; ?>
            <a href="<?php echo htmlspecialchars($item['link']); ?>" class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition <?php echo $isActive ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white'; ?>">
                <i data-lucide="<?php echo htmlspecialchars($item['icon']); ?>" class="h-4 w-4"></i>
                <span><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</div>

<div id="sidebarOverlay" class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden"></div>

<main class="min-h-[calc(100vh-5rem)]">
<script>
    const mobileSidebar = document.getElementById('mobileSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function closeMobileSidebar() {
        mobileSidebar?.classList.add('-translate-x-full');
        sidebarOverlay?.classList.add('hidden');
    }

    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        mobileSidebar?.classList.toggle('-translate-x-full');
        sidebarOverlay?.classList.toggle('hidden');
    });
    sidebarOverlay?.addEventListener('click', closeMobileSidebar);
    document.querySelectorAll('#mobileSidebar a').forEach(link => link.addEventListener('click', closeMobileSidebar));
    lucide.createIcons();
</script>
