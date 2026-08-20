import { createApp, h, type DefineComponent, watchEffect } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import { i18n } from '@/i18n';
import { useAuthStore } from '@/stores/authStore';
import { useShopStore } from '@/stores/shopStore';
import { useUiStore } from '@/stores/uiStore';
import '../css/app.css';
import '../css/theme.css';
import AdminLayout from './layouts/AdminLayout.vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import AuthLayout from './layouts/AuthLayout.vue';
import GuestLayout from './layouts/GuestLayout.vue';
import PlatformAdminLayout from './layouts/PlatformAdminLayout.vue';
import PlatformLayout from './layouts/PlatformLayout.vue';
import SettingsLayout from './layouts/SettingsLayout.vue';
import TenantLayout from './layouts/TenantLayout.vue';

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
        const shopStore = useShopStore();
        const uiStore = useUiStore();
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
        shopStore.hydrate((props as any)?.initialPage?.props);

        // Sync Pinia locale → i18n (ensures html dir/lang attrs are set)
        uiStore.setLocale(uiStore.locale);

        // Apply brand colors from shop context
        watchEffect(() => {
            if (shopStore.shop) {
                document.documentElement.style.setProperty('--brand', shopStore.brandColor);
                document.documentElement.style.setProperty('--brand-dark', shopStore.brandColorDark);
                if (shopStore.favicon) {
                    let link = document.querySelector<HTMLLinkElement>('link[rel="icon"]');
                    if (!link) {
                        link = document.createElement('link');
                        link.setAttribute('rel', 'icon');
                        document.head.appendChild(link);
                    }
                    link.setAttribute('href', shopStore.favicon);
                }
                document.title = shopStore.name;
            }
        });

        app.mount(el);

        router.on('success', (event: any) => {
            syncAuth(event?.detail?.page?.props);
            shopStore.hydrate(event?.detail?.page?.props);
        });
    },
    layout: (name) => {
        if (name.startsWith('Admin/')) return AdminLayout;
        if (name.startsWith('Customer/')) return AuthenticatedLayout;
        if (name.startsWith('auth/')) return AuthLayout;
        if (name.startsWith('Platform/Admin/')) return PlatformAdminLayout;
        if (name.startsWith('Platform/')) return PlatformLayout;
        if (name.startsWith('settings/')) return SettingsLayout;
        return TenantLayout;
    },
    progress: {
        color: '#4F46E5',
        showSpinner: true,
    },
});
