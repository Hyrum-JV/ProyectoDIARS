<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ShoppingCart, History, List, PlusCircle, ArrowLeft, Search, CheckCircle2, ClipboardList } from 'lucide-vue-next';
import { ref, computed, onMounted, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Registrar Orden Compra', href: '/RegistrarOrdenCompra' },
];

const props = defineProps({
    productosDb: {
        type: Array as () => any[],
        default: () => []
    },
    // Recibimos el historial de compras desde el backend
    comprasDb: {
        type: Array as () => any[],
        default: () => []
    }
});

// --- VARIABLES DE ESTADO ---
const vistaActual = ref<'formulario' | 'catalogo' | 'historial'>('formulario');
const busqueda = ref('');

const fechaObra = ref('');
const ubicacion = ref('');

const catalogo = ref<any[]>([]);

onMounted(() => {
    if (props.productosDb) mapearCatalogo(props.productosDb);
});

watch(() => props.productosDb, (nuevosDatos) => {
    if (nuevosDatos) mapearCatalogo(nuevosDatos);
}, { deep: true });

const mapearCatalogo = (datos: any[]) => {
    catalogo.value = datos.map(p => ({
        ...p,
        precio: Number(p.precio) || 0,
        cantidadSeleccionada: 0 
    }));
};

const catalogoFiltrado = computed(() =>
    busqueda.value.trim()
        ? catalogo.value.filter(p =>
            (p.producto && p.producto.toLowerCase().includes(busqueda.value.toLowerCase())) ||
            (p.codigo && p.codigo.toLowerCase().includes(busqueda.value.toLowerCase()))
          )
        : catalogo.value
);

const carrito = computed(() => catalogo.value.filter(p => p.cantidadSeleccionada > 0));

// --- FUNCIONES DEL CATÁLOGO Y VISTAS ---
const incrementar = (id: number) => {
    const p = catalogo.value.find(p => p.id === id);
    if (p && p.cantidadSeleccionada < p.stock) {
        p.cantidadSeleccionada++;
    } else if (p && p.cantidadSeleccionada >= p.stock) {
        alert('No puedes exceder el stock disponible en almacén.');
    }
};

const decrementar = (id: number) => {
    const p = catalogo.value.find(p => p.id === id);
    if (p && p.cantidadSeleccionada > 0) {
        p.cantidadSeleccionada--;
    }
};

const abrirCatalogo = () => {
    vistaActual.value = 'catalogo';
    busqueda.value = ''; 
};

const abrirHistorial = () => {
    vistaActual.value = 'historial';
};

const volverFormulario = () => {
    vistaActual.value = 'formulario';
};

const registrar = () => {
    if (!fechaObra.value || !ubicacion.value) {
        return alert('Por favor complete la Fecha de Obra y Ubicación.');
    }
    if (carrito.value.length === 0) {
        return alert('Debe seleccionar al menos un producto del catálogo.');
    }
    
    const total = carrito.value.reduce((sum, item) => sum + (item.precio * item.cantidadSeleccionada), 0);

    router.post('/RegistrarOrdenCompra/guardar', {
        fecha_obra: fechaObra.value,
        ubicacion: ubicacion.value,
        total: total,
        carrito: carrito.value
    });
};

// Utilidad para formatear fechas
const formatearFecha = (fecha: string) => {
    if (!fecha) return '-';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-PE', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Registrar Orden Compra" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 rounded-xl p-6 bg-[#EBEBEB] min-h-screen">
            
            <div v-if="vistaActual === 'formulario'" class="flex flex-col gap-6">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <ShoppingCart class="h-6 w-6 text-blue-600" />
                        <h1 class="text-2xl font-black text-gray-900">Registrar Orden Compra</h1>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="abrirHistorial" class="inline-flex items-center gap-2 rounded-md bg-slate-800 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-slate-700 transition-colors">
                            <History class="h-4 w-4" />
                            Historial
                        </button>
                        <button @click="abrirCatalogo" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition-colors">
                            <List class="h-4 w-4" />
                            Catálogo de Productos
                            <span v-if="carrito.length > 0" class="ml-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-white px-1.5 text-xs font-black text-blue-600">
                                {{ carrito.length }}
                            </span>
                        </button>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 mb-8 bg-slate-50 p-4 rounded-lg border border-slate-100">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase">Fecha de Obra</label>
                            <input type="date" v-model="fechaObra" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-slate-800 font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase">Ubicación</label>
                            <textarea v-model="ubicacion" rows="2" placeholder="Ej: Planta Sur, Sector B" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-slate-800 font-medium resize-none"></textarea>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-slate-800 mb-3 border-b pb-2">Productos Seleccionados</h3>

                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Código</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Producto</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-600">Unidad</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-600">P. Unitario</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-600">Cant. a Llevar</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-600">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-if="carrito.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <ShoppingCart class="mx-auto h-12 w-12 text-slate-300 mb-3" />
                                        <p class="text-sm font-bold text-slate-500">No hay productos seleccionados.</p>
                                        <p class="text-xs text-slate-400 mt-1">Haga clic en "Catálogo de Productos" para agregar materiales.</p>
                                    </td>
                                </tr>
                                <tr v-for="item in carrito" :key="item.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="whitespace-nowrap px-6 py-3 text-xs font-bold text-slate-500">{{ item.codigo }}</td>
                                    <td class="px-6 py-3 text-sm font-bold text-slate-800 line-clamp-2 w-72">{{ item.producto }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-center text-xs font-semibold text-slate-600">{{ item.unid }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-center text-sm font-semibold text-blue-600">S/ {{ item.precio.toFixed(2) }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-center text-sm font-black text-slate-800 bg-slate-50">{{ item.cantidadSeleccionada }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-right text-sm font-black text-green-600">S/ {{ (item.precio * item.cantidadSeleccionada).toFixed(2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button @click="registrar" type="button" class="inline-flex items-center gap-2 rounded-md bg-green-600 px-8 py-3 text-base font-bold text-white shadow-md hover:bg-green-700 transition-all hover:shadow-lg">
                            <PlusCircle class="h-5 w-5" />
                            Registrar Orden
                        </button>
                    </div>
                </div>
            </div>

            <div v-else-if="vistaActual === 'historial'" class="flex flex-col gap-6">
                
                <div class="flex items-center gap-4 border-b border-slate-300 pb-4">
                    <button @click="volverFormulario" class="h-10 rounded bg-white border border-slate-300 px-5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition shadow-sm flex items-center gap-2">
                        <ArrowLeft class="h-4 w-4" /> VOLVER
                    </button>
                    <div class="flex items-center gap-2">
                        <ClipboardList class="h-6 w-6 text-slate-700" />
                        <h2 class="text-2xl font-black text-slate-800">Historial de Órdenes de Compra</h2>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">N° Orden</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Fecha Creada</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Fecha de Obra</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Ubicación</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Items</th>
                                <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Total</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="comprasDb.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500 font-bold">
                                    No hay órdenes de compra registradas en el historial.
                                </td>
                            </tr>
                            <tr v-for="compra in comprasDb" :key="compra.id" class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-slate-700">#{{ compra.id.toString().padStart(5, '0') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-600">{{ formatearFecha(compra.created_at) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800">{{ formatearFecha(compra.fecha_obra) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-600 max-w-[200px] truncate" :title="compra.ubicacion">{{ compra.ubicacion }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-blue-600">{{ compra.detalles?.length || 0 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-slate-800">S/ {{ Number(compra.total).toFixed(2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider',
                                        compra.estado === 'Pagado' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'
                                    ]">
                                        {{ compra.estado }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <div v-else-if="vistaActual === 'catalogo'" class="flex flex-col gap-6">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-300 pb-4">
                    <div class="flex items-center gap-4">
                        <button @click="volverFormulario" class="h-10 rounded bg-white border border-slate-300 px-5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition shadow-sm flex items-center gap-2">
                            <ArrowLeft class="h-4 w-4" /> VOLVER
                        </button>
                        <h2 class="text-2xl font-black text-slate-800">Seleccionar Materiales</h2>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                            <input v-model="busqueda" type="text" placeholder="Buscar en catálogo..." class="h-10 w-72 rounded border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <button @click="volverFormulario" class="h-10 rounded bg-blue-600 px-6 text-sm font-bold text-white hover:bg-blue-700 transition shadow flex items-center gap-2">
                            <CheckCircle2 class="h-4 w-4" />
                            AGREGAR A LA COMPRA ({{ carrito.length }})
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    <div v-for="producto in catalogoFiltrado" :key="producto.id" :class="['flex flex-col items-center gap-1 bg-white p-3 rounded-xl shadow-sm border transition-all', producto.cantidadSeleccionada > 0 ? 'border-blue-500 ring-2 ring-blue-100' : 'border-slate-200']">
                        
                        <div class="w-full aspect-square rounded-lg flex items-center justify-center text-center text-slate-600 font-black text-2xl bg-slate-100 mb-2 overflow-hidden">
                            <template v-if="!producto.imagen">
                                <span class="text-slate-400 text-sm font-extrabold px-2">IMAGEN</span>
                            </template>
                            <img v-else :src="producto.imagen" class="w-full h-full object-cover" />
                        </div>

                        <p class="text-[10px] text-slate-400 font-bold tracking-wide uppercase w-full text-left">
                            {{ producto.codigo }} &bull; {{ producto.unid }}
                        </p>

                        <p class="text-xs text-left text-slate-700 font-bold leading-snug line-clamp-2 w-full h-8">
                            {{ producto.producto }}
                        </p>

                        <div class="flex items-center justify-between w-full mt-1 border-b border-slate-100 pb-2">
                            <p class="text-lg text-blue-600 font-black">S/ {{ producto.precio.toFixed(2) }}</p>
                            <span class="text-[10px] font-semibold text-slate-500">Stock: <span class="font-bold text-slate-800">{{ producto.stock }}</span></span>
                        </div>

                        <div class="flex flex-col items-center w-full mt-2">
                            <span class="text-[10px] font-bold text-slate-400 mb-1 uppercase">Cantidad a llevar:</span>
                            <div class="flex items-center gap-2 bg-slate-50 rounded-lg p-1 border border-slate-200 w-full justify-center">
                                <button @click="decrementar(producto.id)" class="w-8 h-8 rounded bg-white border border-slate-300 text-slate-600 font-bold hover:bg-slate-100 hover:text-red-500 transition shadow-sm">&minus;</button>
                                
                                <span class="w-8 text-center text-sm font-black text-slate-800">{{ producto.cantidadSeleccionada }}</span>
                                
                                <button @click="incrementar(producto.id)" :disabled="producto.cantidadSeleccionada >= producto.stock" :class="['w-8 h-8 rounded border text-slate-600 font-bold transition shadow-sm', producto.cantidadSeleccionada >= producto.stock ? 'bg-slate-100 border-slate-200 opacity-50 cursor-not-allowed' : 'bg-white border-slate-300 hover:bg-slate-100 hover:text-blue-600']">
                                    +
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>