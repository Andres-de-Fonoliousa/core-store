import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import { i18n } from '@/i18n';
import { useAuthStore } from '@/stores/authStore';
import '../css/app.css';
import '../css/theme.css';
import AdminLayout from './layouts/AdminLayout.vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import AuthLayout from './layouts/AuthLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'VoucherMarket';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')) as Promise<DefineComponent>,
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(createPinia());
        app.use(i18n);

        const authStore = useAuthStore();
        const syncAuth = (pageProps: any) => {
            const user = pageProps?.auth?.user ?? null;
            const token = pageProps?.auth?.token ?? null;
            if (user) {
                authStore.setUser(user);
                if (token) {
                    authStore.setAuth(token, user);
                }
            } else {
                authStore.logout();
            }
        };
        syncAuth((props as any)?.initialPage?.props);

        app.mount(el);

        router.on('success', (event: any) => {
            syncAuth(event?.detail?.page?.props);
        });
    },
    layout: (name) => {
        if (name.startsWith('Admin/')) return AdminLayout;
        if (name.startsWith('Customer/')) return AuthenticatedLayout;
        if (name.startsWith('auth/')) return AuthLayout;
        return undefined;
    },
    progress: {
        color: '#4F46E5',
        showSpinner: true,
    },
});
