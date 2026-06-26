<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { 
    TrendingUp, 
    TrendingDown, 
    Package, 
    Users, 
    AlertTriangle, 
    ArrowRightLeft, 
    Clock, 
    CheckCircle2 
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

// Recepción de las propiedades enviadas desde el controlador de Laravel
const props = defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({
            totalVentas: 0,
            totalCompras: 0,
            cantidadProductos: 0,
            cantidadClientes: 0,
            productosCriticos: 0
        })
    },
    ultimasVentas: {
        type: Array as () => any[],
        default: () => []
    },
    ultimasCompras: {
        type: Array as () => any[],
        default: () => []
    }
});

// Función interna para dar formato de moneda nacional (Soles Peruanos)
const formatearMoneda = (monto: number) => {
    return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(monto);
};

// Función interna para formatear fechas de los registros
const formatearFecha = (fechaString: string) => {
    if (!fechaString) return '-';
    const fecha = new Date(fechaString);
    return fecha.toLocaleDateString('es-PE', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Panel de Control" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 rounded-xl p-6 bg-[#EBEBEB] min-h-screen">
            
            <div class="flex items-center gap-2 mb-2">
                <ArrowRightLeft class="h-8 w-8 text-blue-600" />
                <h1 class="text-2xl font-black text-gray-900">Panel de Control General</h1>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ingresos por Ventas</span>
                        <span class="text-2xl font-black text-slate-800 mt-1">{{ formatearMoneda(props.stats.totalVentas) }}</span>
                    </div>
                    <div class="p-3 rounded-lg bg-green-50 text-green-600">
                        <TrendingUp class="h-6 w-6" />
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Egresos por Compras</span>
                        <span class="text-2xl font-black text-slate-800 mt-1">{{ formatearMoneda(props.stats.totalCompras) }}</span>
                    </div>
                    <div class="p-3 rounded-lg bg-red-50 text-red-600">
                        <TrendingDown class="h-6 w-6" />
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Materiales en Almacén</span>
                        <span class="text-2xl font-black text-slate-800 mt-1">{{ props.stats.cantidadProductos }} Items</span>
                    </div>
                    <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
                        <Package class="h-6 w-6" />
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Clientes Corporativos</span>
                        <span class="text-2xl font-black text-slate-800 mt-1">{{ props.stats.cantidadClientes }} Registrados</span>
                    </div>
                    <div class="p-3 rounded-lg bg-purple-50 text-purple-600">
                        <Users class="h-6 w-6" />
                    </div>
                </div>

            </div>

            <div v-if="props.stats.productosCriticos > 0" class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                <AlertTriangle class="h-6 w-6 text-amber-600 flex-shrink-0" />
                <div class="flex flex-col sm:flex-row sm:items-center justify-between w-full gap-2">
                    <p class="text-sm font-bold text-amber-800">
                        Atención: Se han detectado <span class="font-black">{{ props.stats.productosCriticos }} producto(s)</span> con stock crítico o agotado en el almacén de redes.
                    </p>
                    <span class="text-xs font-black bg-amber-100 text-amber-800 px-3 py-1 rounded-full uppercase tracking-wider">Revisar Inventario</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-gray-100 bg-slate-50 flex items-center gap-2">
                        <CheckCircle2 class="h-5 w-5 text-green-600" />
                        <h2 class="text-base font-black text-slate-800">Últimos Cobros y Ventas</h2>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white text-slate-400 text-[10px] uppercase tracking-wider border-b">
                                    <th class="p-4 font-bold">N° Venta</th>
                                    <th class="p-4 font-bold">Cliente</th>
                                    <th class="p-4 font-bold">Fecha / Hora</th>
                                    <th class="p-4 font-bold text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs text-slate-700 divide-y divide-slate-100">
                                <tr v-for="venta in props.ultimasVentas" :key="venta.id" class="hover:bg-slate-50 transition">
                                    <td class="p-4 font-black text-blue-600">#VT-{{ venta.id.toString().padStart(4, '0') }}</td>
                                    <td class="p-4 font-bold max-w-[150px] truncate" :title="venta.cliente?.nombre">{{ venta.cliente?.nombre || 'Desconocido' }}</td>
                                    <td class="p-4 text-slate-500">{{ formatearFecha(venta.created_at) }}</td>
                                    <td class="p-4 font-black text-right text-green-600">{{ formatearMoneda(venta.total) }}</td>
                                </tr>
                                <tr v-if="props.ultimasVentas.length === 0">
                                    <td colspan="4" class="p-8 text-center text-slate-400 font-bold">No se registran ventas el día de hoy.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-gray-100 bg-slate-50 flex items-center gap-2">
                        <Clock class="h-5 w-5 text-blue-600" />
                        <h2 class="text-base font-black text-slate-800">Últimas Órdenes de Compra</h2>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white text-slate-400 text-[10px] uppercase tracking-wider border-b">
                                    <th class="p-4 font-bold">N° Orden</th>
                                    <th class="p-4 font-bold">Ubicación de Obra</th>
                                    <th class="p-4 font-bold text-center">Estado</th>
                                    <th class="p-4 font-bold text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs text-slate-700 divide-y divide-slate-100">
                                <tr v-for="compra in props.ultimasCompras" :key="compra.id" class="hover:bg-slate-50 transition">
                                    <td class="p-4 font-black text-slate-700">#OC-{{ compra.id.toString().padStart(4, '0') }}</td>
                                    <td class="p-4 font-medium max-w-[160px] truncate" :title="compra.ubicacion">{{ compra.ubicacion }}</td>
                                    <td class="p-4 text-center">
                                        <span :class="[
                                            'px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider',
                                            compra.estado === 'Pagado' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'
                                        ]">
                                            {{ compra.estado }}
                                        </span>
                                    </td>
                                    <td class="p-4 font-black text-right text-slate-800">{{ formatearMoneda(compra.total) }}</td>
                                </tr>
                                <tr v-if="props.ultimasCompras.length === 0">
                                    <td colspan="4" class="p-8 text-center text-slate-400 font-bold">No existen órdenes de compra procesadas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </AppLayout>
</template>