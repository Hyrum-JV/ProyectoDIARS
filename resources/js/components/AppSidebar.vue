<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3'; // 1. Se importó usePage
import { computed } from 'vue'; // 2. Se importó computed
import { BookOpen, Folder, LayoutGrid, ShoppingCart, ClipboardList, Package, Shield } from 'lucide-vue-next'; // 3. Se agregó el icono Shield
import AppLogo from './AppLogo.vue';

// Se obtienen los datos del usuario logueado
const page = usePage();
const userRole = computed(() => (page.props as any).auth.user.role);

// 4. Se convierte el menú en un arreglo computado que filtra según el rol
const mainNavItems = computed<NavItem[]>(() => {
    // El Dashboard siempre es visible
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        }
    ];

    // Mostrar Compras y Ventas solo al Vendedor y al Admin
    if (userRole.value === 'admin' || userRole.value === 'vendedor') {
        items.push(
            {
                title: 'Registrar Orden Compra',
                href: '/RegistrarOrdenCompra',
                icon: ShoppingCart,
            },
            {
                title: 'Registrar Orden Venta',
                href: '/RegistrarOrdenVenta',
                icon: ClipboardList,
            }
        );
    }

    // Mostrar Almacén solo al Jefe de Almacén y al Admin
    if (userRole.value === 'admin' || userRole.value === 'almacen') {
        items.push({
            title: 'Almacén',
            href: '/almacen',
            icon: Package,
        });
    }

    // Mostrar la Gestión de Usuarios SOLO al Administrador
    if (userRole.value === 'admin') {
        items.push({
            title: 'Gestión de Usuarios',
            href: '/usuarios',
            icon: Shield,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>