import { createRouter, createWebHistory } from 'vue-router';

// Layouts
import AuthLayout from '@/layouts/AuthLayout.vue';
import AppLayout from '@/layouts/AppLayout.vue';

// Pages
import Login from '@/pages/auth/Login.vue';
import Dashboard from '@/pages/Dashboard.vue';
import Products from '@/pages/products/Index.vue';
import ProductForm from '@/pages/products/Form.vue';
import Categories from '@/pages/categories/Index.vue';
import POS from '@/pages/pos/Index.vue';
import Transactions from '@/pages/transactions/Index.vue';
import TransactionDetail from '@/pages/transactions/Detail.vue';
import Reports from '@/pages/reports/Index.vue';
import Settings from '@/pages/Settings.vue';
import Users from '@/pages/users/Index.vue';
import Customers from '@/pages/customers/Index.vue';
import Debts from '@/pages/debts/Index.vue';
import BankAccounts from '@/pages/BankAccounts.vue';
import Welcome from '@/pages/Welcome.vue';
import Backup from '@/pages/Backup.vue';

const routes = [
    {
        path: '/welcome',
        name: 'welcome',
        component: Welcome,
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        component: AuthLayout,
        children: [
            { path: '', name: 'login', component: Login }
        ],
        meta: { guest: true }
    },
    {
        path: '/',
        component: AppLayout,
        meta: { requiresAuth: true },
        children: [
            { path: '', name: 'dashboard', component: Dashboard },
            { path: 'products', name: 'products', component: Products },
            { path: 'products/create', name: 'products.create', component: ProductForm },
            { path: 'products/:id/edit', name: 'products.edit', component: ProductForm },
            { path: 'categories', name: 'categories', component: Categories },
            { path: 'pos', name: 'pos', component: POS },
            { path: 'transactions', name: 'transactions', component: Transactions },
            { path: 'transactions/:id', name: 'transactions.detail', component: TransactionDetail },
            { path: 'customers', name: 'customers', component: Customers },
            { path: 'debts', name: 'debts', component: Debts },
            {
                path: 'bank-accounts',
                name: 'bank-accounts',
                component: BankAccounts,
                meta: { permission: 'bank_accounts' }
            },
            {
                path: 'discounts',
                children: [
                    { path: '', name: 'discounts', component: () => import('@/pages/discounts/Index.vue') },
                    { path: 'create', name: 'discounts.create', component: () => import('@/pages/discounts/Form.vue') },
                    { path: ':id/edit', name: 'discounts.edit', component: () => import('@/pages/discounts/Form.vue') },
                ],
                meta: { permission: 'discounts' }
            },
            { path: 'reports', name: 'reports', component: Reports },
            { path: 'settings', name: 'settings', component: Settings },
            { path: 'users', name: 'users', component: Users, meta: { role: 'admin' } },
            { path: 'backup', name: 'backup', component: Backup, meta: { role: 'admin' } },
        ]
    },
    {
        path: '/transactions/:id/print',
        name: 'transactions.print',
        component: () => import('@/pages/transactions/Receipt.vue'),
        meta: { public: true } // Allow access without login for printing
    },
    {
        path: '/products/:id/barcode',
        name: 'products.barcode',
        component: () => import('@/pages/products/Barcode.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/products/barcode/all',
        name: 'products.barcode.all',
        component: () => import('@/pages/products/BarcodeAll.vue'),
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory('/kasirku/public/'),
    routes
});

// Navigation guards
router.beforeEach((to, from, next) => {
    const token = sessionStorage.getItem('auth_token');
    const user = JSON.parse(sessionStorage.getItem('user') || 'null');

    // Allow public routes without any auth check
    if (to.meta.public) {
        next();
        return;
    }

    if (to.meta.requiresAuth && !token) {
        next({ name: 'login' });
    } else if (to.meta.guest && token) {
        next({ name: 'dashboard' });
    } else if (to.meta.role && user?.role !== to.meta.role) {
        next({ name: 'dashboard' });
    } else if (to.meta.permission) {
        // Check permissions
        const permissions = JSON.parse(sessionStorage.getItem('permissions') || '[]');
        if (user?.role === 'admin') {
            next();
        } else if (permissions && permissions.includes(to.meta.permission)) {
            next();
        } else {
            next({ name: 'dashboard' });
        }
    } else {
        next();
    }
});

export default router;
