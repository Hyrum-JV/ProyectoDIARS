<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Almacén', href: '/almacen' },
];

const props = defineProps({
    productosDb: {
        type: Array as () => any[],
        required: true,
        default: () => []
    },
    proveedoresDb: {
        type: Array as () => any[],
        required: true,
        default: () => []
    }
});

// --- VARIABLES DE ESTADO PRINCIPAL ---
const vistaActual = ref<'almacen' | 'proveedores'>('almacen');
const busqueda = ref('');
const selectedItem = ref<string | number | null>(null);
const modoEdicion = ref(false);

const productos = ref<any[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);
const imagenArchivo = ref<File | null>(null);

// --- VARIABLES PARA PROVEEDORES ---
const modoEdicionProveedor = ref(false);
const proveedorForm = ref({
    id: null as number | null,
    razon_social: '',
    ruc: '',
    telefono: '',
    direccion: ''
});

onMounted(() => {
    if (props.productosDb && props.productosDb.length > 0) mapearProductos(props.productosDb);
});

watch(() => props.productosDb, (nuevosDatos) => {
    if (nuevosDatos) mapearProductos(nuevosDatos);
}, { deep: true });

const mapearProductos = (datos: any[]) => {
    productos.value = datos.map(p => ({
        ...p,
        precio: Number(p.precio) || 0,
        cantidad: 0,
        imagenPreview: p.imagen,
        proveedor_id: p.proveedor_id || ''
    }));
};

const productosFiltrados = computed(() =>
    busqueda.value.trim()
        ? productos.value.filter(p =>
            (p.producto && p.producto.toLowerCase().includes(busqueda.value.toLowerCase())) ||
            (p.codigo && p.codigo.toLowerCase().includes(busqueda.value.toLowerCase()))
          )
        : productos.value
);

// --- LÓGICA DE ALMACÉN ---
const seleccionar = (id: string | number) => {
    if (modoEdicion.value) {
        selectedItem.value = selectedItem.value === id ? null : id;
        imagenArchivo.value = null;
    }
};

const toggleModificar = () => {
    modoEdicion.value = !modoEdicion.value;
    if (!modoEdicion.value) {
        selectedItem.value = null;
        imagenArchivo.value = null;
        productos.value = productos.value.filter(p => !p.isNew);
    }
};

const triggerFileInput = () => {
    if (modoEdicion.value && selectedItem.value) fileInput.value?.click();
};

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        imagenArchivo.value = target.files[0];
        const p = productos.value.find(prod => prod.id === selectedItem.value);
        if (p) p.imagenPreview = URL.createObjectURL(target.files[0]);
    }
};

const actualizarEstado = (producto: any) => {
    const stock = parseInt(producto.stock) || 0;
    if (stock === 0) producto.estado = 'Agotado';
    else if (stock < 100) producto.estado = 'Menor al mínimo';
    else producto.estado = 'OK';
};

const agregar = () => {
    const nuevoId = 'temp-' + Date.now();
    productos.value.unshift({
        id: nuevoId,
        codigo: 'PD0****',
        producto: '',
        unid: 'Unidades',
        estado: 'Agotado',
        stock: 0,
        precio: 0.00,
        proveedor_id: '',
        supplier: null,
        imagen: null,
        imagenPreview: null,
        cantidad: 0,
        isNew: true
    });
    selectedItem.value = nuevoId;
};

const guardar = () => {
    if (!selectedItem.value) return alert('Seleccione un producto para guardar los cambios.');
    const p = productos.value.find(prod => prod.id === selectedItem.value);
    if (!p || !p.producto.trim()) return alert('El nombre del producto es obligatorio.');

    const formData = {
        codigo: p.codigo,
        producto: p.producto,
        unid: p.unid,
        stock: p.stock,
        precio: p.precio,
        estado: p.estado,
        proveedor_id: p.proveedor_id || null,
        imagen: imagenArchivo.value
    };

    if (p.isNew) {
        formData.codigo = `PD0${Math.floor(1000 + Math.random() * 9000)}`;
        router.post('/almacen/guardar', formData, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Nuevo producto agregado correctamente.');
                imagenArchivo.value = null;
                selectedItem.value = null;
            }
        });
    } else {
        router.post(`/almacen/actualizar/${p.id}`, { _method: 'put', ...formData }, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Producto actualizado correctamente.');
                imagenArchivo.value = null;
            }
        });
    }
};

const eliminar = () => {
    if (!selectedItem.value) return alert('Seleccione un producto primero.');
    const p = productos.value.find(prod => prod.id === selectedItem.value);
    if (p && p.isNew) {
        productos.value = productos.value.filter(prod => prod.id !== selectedItem.value);
        selectedItem.value = null;
        return;
    }
    if (!confirm('¿Eliminar este producto permanentemente de la base de datos?')) return;
    router.delete(`/almacen/eliminar/${selectedItem.value}`, {
        preserveScroll: true,
        onSuccess: () => { selectedItem.value = null; }
    });
};

// --- LÓGICA DE PROVEEDORES ---
const cambiarVista = (vista: 'almacen' | 'proveedores') => {
    vistaActual.value = vista;
    cancelarEdicionProveedor(); // Limpia el formulario al cambiar de vista
};

const editarProveedor = (prov: any) => {
    proveedorForm.value = { ...prov };
    modoEdicionProveedor.value = true;
};

const cancelarEdicionProveedor = () => {
    proveedorForm.value = { id: null, razon_social: '', ruc: '', telefono: '', direccion: '' };
    modoEdicionProveedor.value = false;
};

const guardarProveedor = () => {
    if (!proveedorForm.value.razon_social || !proveedorForm.value.ruc) {
        return alert('La Razón Social y el RUC son obligatorios.');
    }

    if (modoEdicionProveedor.value) {
        router.put(`/proveedores/actualizar/${proveedorForm.value.id}`, proveedorForm.value, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Proveedor actualizado.');
                cancelarEdicionProveedor();
            }
        });
    } else {
        router.post('/proveedores/guardar', proveedorForm.value, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Proveedor agregado.');
                cancelarEdicionProveedor();
            }
        });
    }
};

const eliminarProveedor = (id: number) => {
    if (!confirm('¿Estás seguro de eliminar este proveedor? Sus productos asociados quedarán "Sin proveedor".')) return;
    
    router.delete(`/proveedores/eliminar/${id}`, {
        preserveScroll: true,
        onSuccess: () => alert('Proveedor eliminado correctamente.')
    });
};
</script>

<template>
    <Head title="Almacén" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-[#EBEBEB] min-h-screen">
            
            <div v-if="vistaActual === 'almacen'" class="flex flex-col gap-6">
                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="onFileChange" />

                <div class="flex flex-wrap items-center gap-3">
                    <input v-model="busqueda" type="text" placeholder="Buscar producto o código..." class="h-10 w-72 rounded border border-slate-300 bg-white px-3 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    
                    <button @click="cambiarVista('proveedores')" class="h-10 rounded bg-slate-700 px-5 text-sm font-bold text-white hover:bg-slate-800 transition shadow flex items-center gap-2">
                        PROVEEDORES
                    </button>

                    <div class="flex-1" />

                    <button @click="agregar" :disabled="!modoEdicion" :class="['h-10 rounded px-6 text-sm font-bold text-white transition shadow', !modoEdicion ? 'bg-slate-300 cursor-not-allowed shadow-none' : 'bg-violet-500 hover:bg-violet-600']">
                        AGREGAR
                    </button>

                    <button @click="toggleModificar" :class="['h-10 rounded px-6 text-sm font-bold text-white transition shadow', modoEdicion ? 'bg-amber-500 hover:bg-amber-600 ring-2 ring-amber-300' : 'bg-sky-400 hover:bg-sky-500']">
                        {{ modoEdicion ? 'CANCELAR EDICIÓN' : 'MODIFICAR' }}
                    </button>

                    <button @click="eliminar" :disabled="!modoEdicion" :class="['h-10 rounded px-6 text-sm font-bold text-white transition shadow', !modoEdicion ? 'bg-slate-300 cursor-not-allowed shadow-none' : 'bg-red-500 hover:bg-red-600']">
                        ELIMINAR
                    </button>

                    <button @click="guardar" :disabled="!modoEdicion" :class="['h-10 rounded px-6 text-sm font-bold text-white transition shadow', !modoEdicion ? 'bg-slate-300 cursor-not-allowed shadow-none' : 'bg-green-500 hover:bg-green-600']">
                        GUARDAR
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    <div v-for="producto in productosFiltrados" :key="producto.id" @click="seleccionar(producto.id)" :class="['flex flex-col items-center gap-1 bg-white p-3 rounded-xl shadow-sm border transition-all', modoEdicion ? 'cursor-pointer hover:border-blue-400' : 'opacity-90', selectedItem === producto.id ? 'border-blue-500 ring-2 ring-blue-200 bg-blue-50' : 'border-slate-200']">
                        
                        <div @click.stop="selectedItem === producto.id ? triggerFileInput() : seleccionar(producto.id)" :class="['w-full aspect-square rounded-lg flex items-center justify-center text-center text-slate-600 font-black text-2xl leading-tight bg-slate-100 mb-2 overflow-hidden relative group', selectedItem === producto.id && modoEdicion ? 'cursor-pointer' : '']">
                            <template v-if="!producto.imagenPreview">
                                <span class="text-slate-400 text-sm font-extrabold leading-tight px-2">IMAGEN</span>
                            </template>
                            <img v-else :src="producto.imagenPreview" class="w-full h-full object-cover" />
                            <div v-if="selectedItem === producto.id && modoEdicion" class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="text-white text-xs font-bold">Cambiar Foto</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 w-full text-slate-400">
                            <span class="text-[10px] font-bold tracking-wide uppercase">{{ producto.codigo }} &bull;</span>
                            <select v-if="selectedItem === producto.id && modoEdicion" v-model="producto.unid" class="text-[10px] font-bold border border-blue-300 rounded px-1 py-0.5 text-slate-700 focus:outline-none w-full">
                                <option value="Unidades">Unidades</option>
                                <option value="Metros">Metros</option>
                                <option value="Metros cubicos">Metros cúbicos</option>
                            </select>
                            <span v-else class="text-[10px] font-bold tracking-wide uppercase">{{ producto.unid }}</span>
                        </div>

                        <div class="w-full h-10 flex items-start mt-1">
                            <textarea v-if="selectedItem === producto.id && modoEdicion" v-model="producto.producto" placeholder="Nombre del producto..." class="w-full h-full text-xs text-slate-700 font-bold leading-snug border border-blue-300 rounded p-1 resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                            <p v-else class="text-xs text-left text-slate-700 font-bold leading-snug line-clamp-2 w-full">{{ producto.producto }}</p>
                        </div>

                        <div class="w-full flex flex-col mt-1 mb-1">
                            <div class="flex items-center gap-1 w-full">
                                <span class="text-lg text-blue-600 font-black">S/</span>
                                <input v-if="selectedItem === producto.id && modoEdicion" type="number" v-model="producto.precio" step="0.10" class="w-full text-lg text-blue-600 font-black border border-blue-300 rounded px-1 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                <span v-else class="text-lg text-left text-blue-600 font-black w-full">{{ Number(producto.precio).toFixed(2) }}</span>
                            </div>

                            <div class="mt-1 flex items-center">
                                <span class="text-[9px] font-bold text-slate-400 mr-1 uppercase">Prov:</span>
                                <select v-if="selectedItem === producto.id && modoEdicion" v-model="producto.proveedor_id" class="w-full text-[10px] font-bold border border-blue-300 rounded px-1 py-0.5 text-slate-700 focus:outline-none">
                                    <option value="">Sin proveedor</option>
                                    <option v-for="prov in proveedoresDb" :key="prov.id" :value="prov.id">{{ prov.razon_social }}</option>
                                </select>
                                <span v-else class="text-[10px] font-bold text-slate-600 truncate w-full">
                                    {{ producto.supplier ? producto.supplier.razon_social : 'Sin proveedor' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between w-full mt-2 mb-2 gap-2">
                            <div class="flex flex-col w-1/3">
                                <span class="text-[10px] font-semibold text-slate-500">Stock</span>
                                <input v-if="selectedItem === producto.id && modoEdicion" @input="actualizarEstado(producto)" v-model="producto.stock" type="number" min="0" class="w-full text-xs font-bold text-slate-800 border border-blue-300 rounded px-1 py-0.5 focus:outline-none" />
                                <span v-else class="text-sm font-bold text-slate-800">{{ producto.stock }}</span>
                            </div>
                            
                            <div class="flex flex-col w-2/3 items-end">
                                <span class="text-[10px] font-semibold text-slate-500 mb-0.5">Estado</span>
                                <span :class="['text-[9px] px-2 py-1 rounded-md font-bold uppercase tracking-wider text-center w-full truncate', producto.estado === 'OK' ? 'bg-green-100 text-green-700' : producto.estado === 'Agotado' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700']">
                                    {{ producto.estado }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div v-else-if="vistaActual === 'proveedores'" class="flex flex-col gap-6">
                <div class="flex items-center gap-4 border-b border-slate-300 pb-4">
                    <button @click="cambiarVista('almacen')" class="h-10 rounded bg-white border border-slate-300 px-5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition shadow-sm flex items-center gap-2">
                        <span>&larr;</span> VOLVER AL ALMACÉN
                    </button>
                    <h2 class="text-2xl font-black text-slate-800">Gestión de Proveedores</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1 bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-700 mb-4">{{ modoEdicionProveedor ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</h3>
                        
                        <div class="flex flex-col gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Razón Social *</label>
                                <input v-model="proveedorForm.razon_social" type="text" class="w-full h-10 rounded border border-slate-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">RUC *</label>
                                <input v-model="proveedorForm.ruc" type="text" maxlength="11" class="w-full h-10 rounded border border-slate-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Teléfono</label>
                                <input v-model="proveedorForm.telefono" type="text" class="w-full h-10 rounded border border-slate-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Dirección</label>
                                <input v-model="proveedorForm.direccion" type="text" class="w-full h-10 rounded border border-slate-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>

                            <div class="flex gap-2 mt-2">
                                <button v-if="modoEdicionProveedor" @click="cancelarEdicionProveedor" class="flex-1 h-10 rounded bg-slate-200 text-slate-700 font-bold hover:bg-slate-300 transition">
                                    CANCELAR
                                </button>
                                <button @click="guardarProveedor" class="flex-1 h-10 rounded bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow">
                                    {{ modoEdicionProveedor ? 'ACTUALIZAR' : 'GUARDAR' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                                    <th class="p-4 font-bold border-b">Razón Social</th>
                                    <th class="p-4 font-bold border-b">RUC</th>
                                    <th class="p-4 font-bold border-b">Teléfono</th>
                                    <th class="p-4 font-bold border-b text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                                <tr v-for="prov in proveedoresDb" :key="prov.id" class="hover:bg-slate-50 transition">
                                    <td class="p-4 font-bold">{{ prov.razon_social }}</td>
                                    <td class="p-4">{{ prov.ruc }}</td>
                                    <td class="p-4">{{ prov.telefono || '-' }}</td>
                                    <td class="p-4 flex items-center justify-center gap-2">
                                        <button @click="editarProveedor(prov)" class="text-blue-500 hover:text-blue-700 font-bold text-xs bg-blue-50 px-2 py-1 rounded">
                                            EDITAR
                                        </button>
                                        <button @click="eliminarProveedor(prov.id)" class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 px-2 py-1 rounded">
                                            ELIMINAR
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="proveedoresDb.length === 0">
                                    <td colspan="4" class="p-8 text-center text-slate-400 font-bold">No hay proveedores registrados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>