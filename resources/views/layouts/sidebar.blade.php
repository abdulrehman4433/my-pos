<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <section class="sidebar">

        <!-- User panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ url(auth()->user()->photo ?? '') }}" class="img-circle img-profil" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ auth()->user()->name }}</p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <ul class="sidebar-menu">

            <!-- Dashboard -->
            <li>
                <a href="{{ route('dashboard') }}">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>

            @if (auth()->user()->access_level == 1)

            <!-- ================= STOCK ================= -->
            <li class="ai-treeview">
                <a href="#" class="ai-treeview-toggle">
                    <i class="fa fa-cubes"></i>
                    <span>Stock</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="ai-treeview-menu">
                    <li>
                        <a href="{{ route('kategori.index') }}">
                            <i class="fa fa-cube"></i> Category
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('produk.index') }}">
                            <i class="fa fa-cubes"></i> Product
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ================= SERVICES ================= -->
            <li class="ai-treeview">
                <a href="#" class="ai-treeview-toggle">
                    <i class="fa fa-wrench"></i>
                    <span>Services</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="ai-treeview-menu">
                    <li><a href="{{ route('project.index') }}"><i class="fa fa-briefcase"></i> Project</a></li>
                    <li><a href="{{ route('rental.index') }}"><i class="fa fa-compass"></i> Rental</a></li>
                    <li><a href="{{ route('maintenance.index') }}"><i class="fa fa-cog"></i> Maintenance</a></li>
                </ul>
            </li>

            <!-- ================= TRANSACTION ================= -->
            <li class="ai-treeview">
                <a href="#" class="ai-treeview-toggle">
                    <i class="fa fa-exchange"></i>
                    <span>Transaction</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="ai-treeview-menu">
                    <li><a href="{{ route('pengeluaran.index') }}"><i class="fa fa-money"></i> Expenses</a></li>
                    <li><a href="{{ route('penjualan.index') }}"><i class="fa fa-dollar"></i> Sales List</a></li>
                    <li><a href="{{ route('invoice.index') }}"><i class="fa fa-file-pdf-o"></i> Invoice</a></li>
                    <li><a href="{{ route('transaction-return.index') }}"><i class="fa fa-cart-arrow-down"></i> Return Transaction</a></li>
                    <li><a href="{{ route('partial-transaction-return.data') }}"><i class="fa fa-cart-plus"></i> Partial Transaction</a></li>
                </ul>
            </li>

            <!-- ================= PEOPLE ================= -->
            <li class="ai-treeview">
                <a href="#" class="ai-treeview-toggle">
                    <i class="fa fa-users"></i>
                    <span>People</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="ai-treeview-menu">
                    <li><a href="{{ route('user.index') }}"><i class="fa fa-user"></i> User</a></li>
                    <li><a href="{{ route('member.index') }}"><i class="fa fa-id-card"></i> Worker</a></li>
                    <li><a href="{{ route('supplier.index') }}"><i class="fa fa-truck"></i> Supplier</a></li>
                    <li><a href="{{ route('customer.index') }}"><i class="fa fa-address-card-o"></i> Customer</a></li>
                </ul>
            </li>

            <!-- ================= SYSTEM ================= -->
            <li>
                <a href="{{ route('setting.index') }}">
                    <i class="fa fa-cogs"></i> <span>Settings</span>
                </a>
            </li>

            @else

            <!-- Limited user menu -->
            <li>
                <a href="{{ route('transaction-return.index') }}">
                    <i class="fa fa-cart-arrow-down"></i> <span>Return Transaction</span>
                </a>
            </li>
            <li>
                <a href="{{ route('transaksi.index') }}">
                    <i class="fa fa-cart-plus"></i> <span>Partial Transaction</span>
                </a>
            </li>

            @endif
        </ul>
    </section>
</aside>

<!-- ================= CUSTOM SIDEBAR CSS ================= -->
<style>
/* ================= MAIN MENU HEADERS – 20px ================= */
.main-sidebar .sidebar-menu > li > a,
.main-sidebar .sidebar-menu > li > a > span,
.main-sidebar .ai-treeview > a,
.main-sidebar .ai-treeview > a > span {
    font-size: 20px !important;
    line-height: 1.4 !important;
}

/* ================= SUB MENU ITEMS – 18px ================= */
.main-sidebar .ai-treeview-menu > li > a,
.main-sidebar .ai-treeview-menu > li > a > span {
    font-size: 18px !important;
    line-height: 1.4 !important;
}

/* ================= ICON SIZE MATCH ================= */
.main-sidebar .sidebar-menu i {
    font-size: 18px !important;
}

/* ================= SUB MENU LEFT INDENT ================= */
.main-sidebar .ai-treeview-menu {
    padding-left: 20px; /* overall indent */
}

.main-sidebar .ai-treeview-menu > li > a {
    padding-left: 30px !important; /* extra space for text */
}
/* Add vertical space between dropdown items */
.ai-treeview-menu li {
    margin-bottom: 10px; /* Adjust the spacing as you like */
}

/* Remove extra space after the last item */
.ai-treeview-menu li:last-child {
    margin-bottom: 0;
}


/* Existing styles (unchanged) */
.ai-treeview > a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
}

.ai-treeview-menu {
    display: none;
    padding-left: 15px;
}

.ai-treeview.active > .ai-treeview-menu {
    display: block;
}

@media (max-width: 768px) {
    .ai-treeview-menu {
        padding-left: 10px;
    }
}

</style>

<!-- ================= CUSTOM SIDEBAR JS ================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Force close ALL dropdowns on page load
    document.querySelectorAll('.ai-treeview-menu').forEach(function (menu) {
        menu.style.display = 'none';
    });

    document.querySelectorAll('.ai-treeview-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();

            const parent = this.closest('.ai-treeview');
            const menu   = parent.querySelector('.ai-treeview-menu');

            const isOpen = menu.style.display === 'block';

            // Close all menus
            document.querySelectorAll('.ai-treeview-menu').forEach(function (m) {
                m.style.display = 'none';
            });

            document.querySelectorAll('.ai-treeview').forEach(function (item) {
                item.classList.remove('active');
            });

            // Toggle current
            if (!isOpen) {
                menu.style.display = 'block';
                parent.classList.add('active');
            }
        });
    });
});
</script>