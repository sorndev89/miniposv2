import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth'; // Assuming an auth store exists

// Layouts
import AuthLayout from '../layouts/AuthLayout.vue';
import AppLayout from '../layouts/AppLayout.vue';

// Pages
import Login from '../pages/Login.vue';
import Item1 from '../pages/extra/Item1.vue';
import Item2 from '../pages/extra/Item2.vue';

const routes = [
    {
        path: '/auth',
        component: AuthLayout,
        children: [
            {
                path: 'login',
                name: 'Login',
                component: Login,
                meta: { guestOnly: true } // Only accessible to unauthenticated users
            },
        ],
    },
    {
        path: '/',
        component: AppLayout, // Main layout for authenticated users
        meta: { requiresAuth: true }, // Requires authentication for all children
        children: [
            {
                path: '', // Default route for AppLayout
                name: 'Dashboard',
                component: ()=>import('../pages/Dashboard.vue'),
            },
            {
                path: 'products',
                name: 'Products',
                component: ()=>import('../pages/Product.vue'),
            },
            {
                path: 'stock/import',
                name: 'StockImport',
                component: ()=>import('../pages/StockImport.vue'),
            },
            {
                path: 'pos',
                name: 'Pos',
                component: ()=>import('../pages/Pos.vue'),
            },
            {
                path: 'reports',
                name: 'Reports',
                component: ()=>import('../pages/Report.vue'),
            },
            {
                path: 'extra/item1',
                name: 'ExtraItem1',
                component: Item1,
            },
            {
                path: 'extra/item2',
                name: 'ExtraItem2',
                component: Item2,
            },
            {
                path: 'settings/categories-unit',
                name: 'SettingsCategoriesUnit',
                component: ()=>import('../pages/settings/CategoriesUnit.vue'),
            },
            {
                path: 'settings/users',
                name: 'SettingsUsers',
                component: ()=>import('../pages/settings/Users.vue'),
            },
        ],
    },

    // Catch-all route for 404 - consider creating a NotFound.vue page
    {
        path: '/:catchAll(.*)',
        name: 'NotFound',
        redirect: '/' // Redirect to dashboard or a dedicated 404 page
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation Guard
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore(); // Access the auth store

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        // If route requires auth and user is not logged in, redirect to login
        next({ name: 'Login' });
    } else if (to.meta.guestOnly && authStore.isAuthenticated) {
        // If route is for guests only and user is logged in, redirect to dashboard
        next({ name: 'Dashboard' });
    } else {
        // Otherwise, proceed to route
        next();
    }
});

export default router;
