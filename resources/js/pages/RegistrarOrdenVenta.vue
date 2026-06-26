<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ClipboardList, User, MapPin, CreditCard, ShoppingCart, Search, Users, ArrowLeft, CheckCircle2, Edit, Trash2, Lock, Download, Mail } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Registrar Orden Venta', href: '/RegistrarOrdenVenta' },
];

const props = defineProps({
    compraDb: { type: Object, required: true },
    clientesDb: { type: Array as () => any[], default: () => [] }
});

// --- ESTADOS DE VISTA SPA ---
const vistaActual = ref<'formulario' | 'clientes' | 'pago' | 'completado'>('formulario');

// --- DATOS DE VENTA ---
const clienteVenta = ref({ id: null as number | null, nombre: '', telefono: '', ruc: '' });
const carrito = computed(() => props.compraDb?.detalles || []);
const totalPagar = computed(() => props.compraDb?.total || 0);

// --- DATOS SIMULACIÓN TARJETA BCP ---
const tarjeta = ref({
    numero: '',
    cvv: '',
    procesando: false
});

// --- MÓDULO DE CLIENTES ---
const busquedaCliente = ref('');
const modoEdicionCliente = ref(false);
const clienteForm = ref({ id: null as number | null, nombre: '', ruc: '', telefono: '' });

const clientesFiltrados = computed(() => 
    busquedaCliente.value.trim()
        ? props.clientesDb.filter(c => 
            (c.nombre && c.nombre.toLowerCase().includes(busquedaCliente.value.toLowerCase())) || 
            (c.ruc && c.ruc.includes(busquedaCliente.value))
          )
        : props.clientesDb
);

// --- FUNCIONES ---
const cambiarVista = (vista: 'formulario' | 'clientes' | 'pago' | 'completado') => {
    vistaActual.value = vista;
    if (vista === 'clientes') {
        cancelarEdicionCliente();
        busquedaCliente.value = '';
    }
};

const seleccionarCliente = (cliente: any) => {
    clienteVenta.value = { ...cliente };
    cambiarVista('formulario');
};

const editarCliente = (cliente: any) => {
    clienteForm.value = { ...cliente };
    modoEdicionCliente.value = true;
};

const cancelarEdicionCliente = () => {
    clienteForm.value = { id: null, nombre: '', ruc: '', telefono: '' };
    modoEdicionCliente.value = false;
};

const guardarCliente = () => {
    if (!clienteForm.value.nombre || !clienteForm.value.ruc) return alert('El Nombre y RUC son obligatorios.');
    
    if (modoEdicionCliente.value) {
        router.put(`/clientes/actualizar/${clienteForm.value.id}`, clienteForm.value, {
            preserveScroll: true, 
            onSuccess: () => { alert('Cliente actualizado exitosamente.'); cancelarEdicionCliente(); }
        });
    } else {
        router.post('/clientes/guardar', clienteForm.value, {
            preserveScroll: true, 
            onSuccess: () => { alert('Cliente registrado exitosamente.'); cancelarEdicionCliente(); },
            onError: (errors) => { if (errors.ruc) alert('Ese RUC ya se encuentra registrado.'); }
        });
    }
};

const eliminarCliente = (id: number) => {
    if (!confirm('¿Estás seguro de eliminar a este cliente permanentemente?')) return;
    router.delete(`/clientes/eliminar/${id}`, { preserveScroll: true, onSuccess: () => alert('Cliente eliminado.') });
};

// --- FUNCIONES DE PAGO ---
const irAPasarela = () => {
    if (!clienteVenta.value.nombre || !clienteVenta.value.ruc) {
        return alert('Por favor, asigne o seleccione un Cliente antes de realizar el pago.');
    }
    cambiarVista('pago');
};

const procesarPagoSimulado = () => {
    if (tarjeta.value.numero.length < 16) return alert('Ingrese un número de tarjeta válido de 16 dígitos.');
    if (tarjeta.value.cvv.length < 3) return alert('Ingrese un CVV válido.');

    tarjeta.value.procesando = true;

    // Simula 2 segundos conectando al banco
    setTimeout(() => {
        router.post('/RegistrarOrdenVenta/guardar', {
            compra_id: props.compraDb.id,
            ruc: clienteVenta.value.ruc,
            nombre: clienteVenta.value.nombre,
            telefono: clienteVenta.value.telefono
        }, {
            onSuccess: () => {
                tarjeta.value.procesando = false;
                cambiarVista('completado'); 
            },
            onError: () => {
                tarjeta.value.procesando = false;
                alert('Hubo un error al procesar la venta en la base de datos.');
            }
        });
    }, 2000);
};

const formatearTarjeta = (e: Event) => {
    const input = e.target as HTMLInputElement;
    let valor = input.value.replace(/\D/g, '').substring(0, 16);
    tarjeta.value.numero = valor;
};
</script>

<template>
    <Head title="Registrar Orden Venta" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 rounded-xl p-6 bg-[#EBEBEB] min-h-screen">
            
            <div v-if="vistaActual === 'formulario'" class="flex flex-col gap-6">
                
                <div class="flex items-center gap-2 mb-2">
                    <ClipboardList class="h-8 w-8 text-blue-600" />
                    <h1 class="text-2xl font-black text-gray-900">Registrar Orden de Venta</h1>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="flex flex-col gap-6 lg:col-span-1">
                        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center gap-2 mb-4 border-b pb-2">
                                <MapPin class="h-5 w-5 text-slate-500" />
                                <h2 class="text-lg font-bold text-gray-800">Datos de la Obra</h2>
                            </div>
                            <div class="flex flex-col gap-2">
                                <p class="text-sm font-bold text-slate-700">Ubicación: <span class="font-medium text-slate-500">{{ props.compraDb?.ubicacion }}</span></p>
                                <p class="text-sm font-bold text-slate-700">Fecha de entrega: <span class="font-medium text-slate-500">{{ props.compraDb?.fecha_obra }}</span></p>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-4 border-b pb-2">
                                <div class="flex items-center gap-2">
                                    <User class="h-5 w-5 text-slate-500" />
                                    <h2 class="text-lg font-bold text-gray-800">Datos del Cliente</h2>
                                </div>
                                <button @click="cambiarVista('clientes')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-md transition" title="Directorio de Clientes">
                                    <Users class="h-5 w-5" />
                                </button>
                            </div>
                            <div class="flex flex-col gap-4">
                                <button @click="cambiarVista('clientes')" class="w-full bg-slate-800 text-white font-bold py-2 rounded-md hover:bg-slate-700 transition flex items-center justify-center gap-2">
                                    <Search class="h-4 w-4" /> Seleccionar Cliente de la Lista
                                </button>
                                <div class="mt-2 bg-slate-50 p-3 rounded-lg border border-slate-100 flex flex-col gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase">RUC / DNI</label>
                                        <input type="text" v-model="clienteVenta.ruc" placeholder="-" class="w-full bg-transparent border-0 border-b border-slate-300 px-0 py-1 text-sm font-bold focus:ring-0" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase">Nombre / Razón Social</label>
                                        <input type="text" v-model="clienteVenta.nombre" placeholder="-" class="w-full bg-transparent border-0 border-b border-slate-300 px-0 py-1 text-sm font-bold focus:ring-0" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-6 lg:col-span-2">
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden flex flex-col h-full">
                            <div class="px-6 py-4 border-b border-gray-200 bg-slate-50 flex justify-between items-center">
                                <h2 class="text-lg font-bold text-gray-800">Resumen de Productos</h2>
                                <span class="text-xs font-bold bg-blue-100 text-blue-700 px-3 py-1 rounded-full">{{ carrito.length }} items</span>
                            </div>
                            <div class="overflow-x-auto flex-1">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-white">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-400">Producto</th>
                                            <th class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-slate-400">Cant</th>
                                            <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-slate-400">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <tr v-for="item in carrito" :key="item.id" class="hover:bg-slate-50">
                                            <td class="px-6 py-3 text-xs font-bold text-slate-700 w-48">{{ item.product?.producto }}</td>
                                            <td class="px-6 py-3 text-center text-sm font-black text-slate-800">{{ item.cantidad }}</td>
                                            <td class="px-6 py-3 text-right text-sm font-black text-blue-600">S/ {{ (item.precio_unitario * item.cantidad).toFixed(2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="bg-slate-800 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="flex flex-col text-white">
                                    <span class="text-sm text-slate-400 font-semibold uppercase tracking-wider">Total a Cobrar</span>
                                    <span class="text-3xl font-black">S/ {{ Number(totalPagar).toFixed(2) }}</span>
                                </div>
                                <button @click="irAPasarela" type="button" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-green-500 px-8 py-4 text-base font-black text-white shadow-lg hover:bg-green-600 transition">
                                    <CreditCard class="h-6 w-6" /> REALIZAR PAGO
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else-if="vistaActual === 'clientes'" class="flex flex-col gap-6">
                
                <div class="flex items-center gap-4 border-b border-slate-300 pb-4">
                    <button @click="cambiarVista('formulario')" class="h-10 rounded bg-white border border-slate-300 px-5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition shadow-sm flex items-center gap-2">
                        <ArrowLeft class="h-4 w-4" /> VOLVER A LA VENTA
                    </button>
                    <div class="flex items-center gap-2">
                        <Users class="h-6 w-6 text-slate-700" />
                        <h2 class="text-2xl font-black text-slate-800">Directorio de Clientes</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <div class="md:col-span-1 bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-700 mb-4">{{ modoEdicionCliente ? 'Editar Cliente' : 'Nuevo Cliente' }}</h3>
                        
                        <div class="flex flex-col gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">RUC / DNI *</label>
                                <input v-model="clienteForm.ruc" type="text" maxlength="11" class="w-full h-10 rounded border border-slate-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Nombre / Razón Social *</label>
                                <input v-model="clienteForm.nombre" type="text" class="w-full h-10 rounded border border-slate-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Teléfono</label>
                                <input v-model="clienteForm.telefono" type="text" class="w-full h-10 rounded border border-slate-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>

                            <div class="flex gap-2 mt-2">
                                <button v-if="modoEdicionCliente" @click="cancelarEdicionCliente" class="flex-1 h-10 rounded bg-slate-200 text-slate-700 font-bold hover:bg-slate-300 transition">
                                    CANCELAR
                                </button>
                                <button @click="guardarCliente" class="flex-1 h-10 rounded bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow">
                                    {{ modoEdicionCliente ? 'ACTUALIZAR' : 'GUARDAR' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                        <div class="p-4 border-b border-slate-200 bg-slate-50">
                            <div class="relative w-full max-w-md">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                                <input v-model="busquedaCliente" type="text" placeholder="Buscar por RUC o Nombre..." class="h-10 w-full rounded border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>
                        
                        <div class="overflow-y-auto flex-1 max-h-[500px]">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-white text-slate-400 text-[10px] uppercase tracking-wider sticky top-0 shadow-sm">
                                        <th class="p-4 font-bold border-b">RUC/DNI</th>
                                        <th class="p-4 font-bold border-b">Nombre</th>
                                        <th class="p-4 font-bold border-b">Teléfono</th>
                                        <th class="p-4 font-bold border-b text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                                    <tr v-for="c in clientesFiltrados" :key="c.id" class="hover:bg-slate-50 transition">
                                        <td class="p-4 font-bold">{{ c.ruc }}</td>
                                        <td class="p-4">{{ c.nombre }}</td>
                                        <td class="p-4">{{ c.telefono || '-' }}</td>
                                        <td class="p-4 flex items-center justify-center gap-2">
                                            <button @click="seleccionarCliente(c)" class="bg-green-100 text-green-700 hover:bg-green-200 font-bold text-xs px-3 py-1.5 rounded transition flex items-center gap-1">
                                                <CheckCircle2 class="h-3 w-3" /> SELECCIONAR
                                            </button>
                                            <button @click="editarCliente(c)" class="text-blue-500 hover:text-blue-700 p-1" title="Editar">
                                                <Edit class="h-4 w-4" />
                                            </button>
                                            <button @click="eliminarCliente(c.id)" class="text-red-400 hover:text-red-600 p-1" title="Eliminar">
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="clientesFiltrados.length === 0">
                                        <td colspan="4" class="p-8 text-center text-slate-400 font-bold">
                                            No se encontraron clientes registrados.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else-if="vistaActual === 'pago'" class="flex justify-center items-center py-10">
                <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-200">
                    <div class="flex justify-between items-center mb-6">
                        <button @click="cambiarVista('formulario')" class="text-slate-400 hover:text-slate-600 transition">
                            <ArrowLeft class="h-6 w-6" />
                        </button>
                        <h2 class="text-xl font-black text-[#002A8D]">Pasarela Segura BCP</h2>
                        <div class="bg-[#FF7A00] w-8 h-8 rounded-full flex items-center justify-center border-4 border-[#002A8D]"></div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 mb-6 flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-500">Monto a Pagar:</span>
                        <span class="text-2xl font-black text-green-600">S/ {{ Number(totalPagar).toFixed(2) }}</span>
                    </div>

                    <div class="bg-gradient-to-r from-[#002A8D] to-[#0044CC] rounded-xl p-5 text-white shadow-lg mb-6 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-10 -mt-10"></div>
                        <CreditCard class="h-8 w-8 mb-4 opacity-80" />
                        <p class="text-xl font-mono tracking-widest mb-2 shadow-sm">{{ tarjeta.numero || '•••• •••• •••• ••••' }}</p>
                        <div class="flex justify-between text-xs font-bold uppercase opacity-80 mt-4">
                            <span>{{ clienteVenta.nombre || 'NOMBRE DEL TITULAR' }}</span>
                            <span>CVV: {{ tarjeta.cvv ? '•••' : '***' }}</span>
                        </div>
                    </div>

                    <form @submit.prevent="procesarPagoSimulado" class="flex flex-col gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Nombre del Titular</label>
                            <input type="text" :value="clienteVenta.nombre" disabled class="w-full h-10 rounded border border-slate-200 bg-slate-100 px-3 text-sm text-slate-500 cursor-not-allowed" />
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Número de Tarjeta (16 dígitos) *</label>
                            <div class="relative">
                                <input type="text" :value="tarjeta.numero" @input="formatearTarjeta" placeholder="0000 0000 0000 0000" maxlength="16" required class="w-full h-10 rounded border border-slate-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF7A00] font-mono tracking-widest pl-10" />
                                <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                            </div>
                        </div>

                        <div class="w-1/2">
                            <label class="block text-xs font-bold text-slate-500 mb-1">Código CVV *</label>
                            <input type="password" v-model="tarjeta.cvv" maxlength="4" placeholder="***" required class="w-full h-10 rounded border border-slate-300 px-3 text-center text-lg tracking-widest focus:outline-none focus:ring-2 focus:ring-[#FF7A00]" />
                        </div>

                        <button type="submit" :disabled="tarjeta.procesando" class="mt-4 w-full bg-[#FF7A00] hover:bg-[#E66E00] text-white font-black py-4 rounded-lg transition shadow-md flex justify-center items-center gap-2">
                            <span v-if="tarjeta.procesando" class="animate-spin h-5 w-5 border-4 border-white border-t-transparent rounded-full"></span>
                            {{ tarjeta.procesando ? 'PROCESANDO CON EL BANCO...' : 'PROCESAR PAGO SEGURO' }}
                        </button>
                    </form>
                </div>
            </div>

            <div v-else-if="vistaActual === 'completado'" class="flex justify-center items-center py-10">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 w-full max-w-md text-center flex flex-col items-center">
                    <div class="h-20 w-20 bg-green-100 rounded-full flex items-center justify-center mb-6">
                        <CheckCircle2 class="h-10 w-10 text-green-600" />
                    </div>
                    <h2 class="text-2xl font-black text-slate-800 mb-2">¡Pago Exitoso!</h2>
                    <p class="text-sm font-medium text-slate-500 mb-6">La orden ha sido registrada. El recibo y los detalles han sido enviados a tu correo electrónico.</p>
                    
                    <div class="w-full bg-slate-50 rounded-lg p-4 mb-6 border border-slate-100 text-left">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Cliente:</p>
                        <p class="text-sm font-black text-slate-700 mb-3">{{ clienteVenta.nombre }}</p>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Monto Pagado:</p>
                        <p class="text-lg font-black text-green-600">S/ {{ Number(totalPagar).toFixed(2) }}</p>
                    </div>

                    <div class="flex flex-col gap-3 w-full">
                        <a :href="`/RegistrarOrdenVenta/pdf/${props.compraDb?.id}`" target="_blank" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-3 rounded-lg transition flex items-center justify-center gap-2">
                            <Download class="h-5 w-5" /> DESCARGAR COMPROBANTE PDF
                        </a>
                        <button @click="router.get('/almacen')" class="w-full bg-white hover:bg-slate-50 text-blue-600 border border-blue-200 font-bold py-3 rounded-lg transition">
                            Volver al Almacén
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>