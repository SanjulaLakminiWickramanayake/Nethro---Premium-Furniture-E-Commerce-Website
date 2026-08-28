    <!-- Sidebar -->
    <aside id="adminSidebar" class="fixed left-0 top-20 w-64 bg-slate-900 text-slate-200 flex-col overflow-y-auto z-50 h-screen hidden lg:flex border-r border-slate-700">
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $adminPage === 'dashboard.php' ? 'bg-blue-500 text-white' : 'text-slate-300 hover:bg-slate-800'; ?>">
                <i class="fas fa-chart-line w-5"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="add_product.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $adminPage === 'add_product.php' ? 'bg-blue-500 text-white' : 'text-slate-300 hover:bg-slate-800'; ?>">
                <i class="fas fa-plus w-5"></i>
                <span class="font-medium">Add Product</span>
            </a>
            <a href="manage_products.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $adminPage === 'manage_products.php' ? 'bg-blue-500 text-white' : 'text-slate-300 hover:bg-slate-800'; ?>">
                <i class="fas fa-box w-5"></i>
                <span class="font-medium">Manage Products</span>
            </a>
            <a href="customers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $adminPage === 'customers.php' ? 'bg-blue-500 text-white' : 'text-slate-300 hover:bg-slate-800'; ?>">
                <i class="fas fa-users w-5"></i>
                <span class="font-medium">Customers</span>
            </a>
            <a href="payments.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $adminPage === 'payments.php' ? 'bg-blue-500 text-white' : 'text-slate-300 hover:bg-slate-800'; ?>">
                <i class="fas fa-credit-card w-5"></i>
                <span class="font-medium">Payments</span>
            </a>
            <div class="border-t border-slate-700 my-4"></div>
            <a href="../index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 transition">
                <i class="fas fa-arrow-left w-5"></i>
                <span class="font-medium">Back to Site</span>
            </a>
        </nav>
    </aside>

    <!-- Mobile Sidebar Overlay & Drawer -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="closeMobileSidebar()"></div>
    <aside id="mobileSidebar" class="fixed left-0 top-20 w-64 bg-slate-900 text-slate-200 flex flex-col overflow-y-auto z-50 lg:hidden transition-transform -translate-x-full h-screen">
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $adminPage === 'dashboard.php' ? 'bg-blue-500 text-white' : 'text-slate-300 hover:bg-slate-800'; ?>" onclick="closeMobileSidebar()">
                <i class="fas fa-chart-line w-5"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="add_product.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $adminPage === 'add_product.php' ? 'bg-blue-500 text-white' : 'text-slate-300 hover:bg-slate-800'; ?>" onclick="closeMobileSidebar()">
                <i class="fas fa-plus w-5"></i>
                <span class="font-medium">Add Product</span>
            </a>
            <a href="manage_products.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $adminPage === 'manage_products.php' ? 'bg-blue-500 text-white' : 'text-slate-300 hover:bg-slate-800'; ?>" onclick="closeMobileSidebar()">
                <i class="fas fa-box w-5"></i>
                <span class="font-medium">Manage Products</span>
            </a>
            <a href="customers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $adminPage === 'customers.php' ? 'bg-blue-500 text-white' : 'text-slate-300 hover:bg-slate-800'; ?>" onclick="closeMobileSidebar()">
                <i class="fas fa-users w-5"></i>
                <span class="font-medium">Customers</span>
            </a>
            <a href="payments.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $adminPage === 'payments.php' ? 'bg-blue-500 text-white' : 'text-slate-300 hover:bg-slate-800'; ?>" onclick="closeMobileSidebar()">
                <i class="fas fa-credit-card w-5"></i>
                <span class="font-medium">Payments</span>
            </a>
            <div class="border-t border-slate-700 my-4"></div>
            <a href="../index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 transition" onclick="closeMobileSidebar()">
                <i class="fas fa-arrow-left w-5"></i>
                <span class="font-medium">Back to Site</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="pt-20 lg:ml-64 min-h-screen">
