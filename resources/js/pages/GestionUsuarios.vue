<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Users, Shield, Edit, Trash2, KeyRound } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gestión de Usuarios', href: '/usuarios' },
];

const props = defineProps({
    usuariosDb: {
        type: Array as () => any[],
        default: () => []
    }
});

const busqueda = ref('');
const modoEdicion = ref(false);

const form = ref({
    id: null as number | null,
    name: '',
    email: '',
    password: '',
    role: 'vendedor'
});

const usuariosFiltrados = computed(() => 
    busqueda.value.trim()
        ? props.usuariosDb.filter(u => 
            (u.name && u.name.toLowerCase().includes(busqueda.value.toLowerCase())) || 
            (u.email && u.email.toLowerCase().includes(busqueda.value.toLowerCase()))
          )
        : props.usuariosDb
);

const editarUsuario = (user: any) => {
    form.value = { 
        id: user.id, 
        name: user.name, 
        email: user.email, 
        password: '', // Dejamos en blanco por seguridad
        role: user.role 
    };
    modoEdicion.value = true;
};

const cancelarEdicion = () => {
    form.value = { id: null, name: '', email: '', password: '', role: 'vendedor' };
    modoEdicion.value = false;
};

const guardarUsuario = () => {
    if (!form.value.name || !form.value.email || !form.value.role) {
        return alert('Nombre, Correo y Rol son obligatorios.');
    }

    if (modoEdicion.value) {
        router.put(`/usuarios/actualizar/${form.value.id}`, form.value, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Usuario actualizado correctamente.');
                cancelarEdicion();
            }
        });
    } else {
        if (!form.value.password || form.value.password.length < 8) {
            return alert('La contraseña es obligatoria para nuevos usuarios (Mínimo 8 caracteres).');
        }
        router.post('/usuarios/guardar', form.value, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Nuevo usuario registrado.');
                cancelarEdicion();
            },
            onError: (errors) => {
                if (errors.email) alert('Este correo ya está en uso.');
            }
        });
    }
};

const eliminarUsuario = (id: number) => {
    if (!confirm('¿Estás seguro de eliminar a este usuario del sistema permanentemente?')) return;
    router.delete(`/usuarios/eliminar/${id}`, {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.error) alert(errors.error);
        }
    });
};
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 rounded-xl p-6 bg-[#EBEBEB] min-h-screen">
            
            <div class="flex items-center gap-3 border-b border-slate-300 pb-4">
                <Shield class="h-8 w-8 text-blue-600" />
                <h1 class="text-2xl font-black text-slate-800">Panel de Control: Gestión de Usuarios</h1>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="md:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-slate-200 h-fit">
                    <h3 class="text-lg font-bold text-slate-700 mb-5 border-b pb-2">
                        {{ modoEdicion ? 'Actualizar Permisos' : 'Registrar Nuevo Empleado' }}
                    </h3>
                    
                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Nombre Completo *</label>
                            <input v-model="form.name" type="text" class="w-full h-10 rounded border border-slate-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Correo Electrónico (Para Inicio de Sesión) *</label>
                            <input v-model="form.email" type="email" class="w-full h-10 rounded border border-slate-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 flex items-center gap-1">
                                <KeyRound class="h-3 w-3" /> Contraseña {{ modoEdicion ? '(Opcional)' : '*' }}
                            </label>
                            <input v-model="form.password" type="password" :placeholder="modoEdicion ? 'Dejar en blanco para no cambiar' : 'Mínimo 8 caracteres'" class="w-full h-10 rounded border border-slate-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Rol en el Sistema *</label>
                            <select v-model="form.role" class="w-full h-10 rounded border border-slate-300 px-3 text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="vendedor">Vendedor (Acceso a Compras/Ventas)</option>
                                <option value="almacen">Jefe de Almacén (Solo Inventario)</option>
                                <option value="admin">Administrador (Acceso Total)</option>
                            </select>
                        </div>

                        <div class="flex gap-2 mt-4">
                            <button v-if="modoEdicion" @click="cancelarEdicion" class="flex-1 h-10 rounded bg-slate-200 text-slate-700 font-bold hover:bg-slate-300 transition">
                                CANCELAR
                            </button>
                            <button @click="guardarUsuario" class="flex-1 h-10 rounded bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow">
                                {{ modoEdicion ? 'ACTUALIZAR USUARIO' : 'CREAR USUARIO' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                        <div class="relative w-full max-w-sm">
                            <input v-model="busqueda" type="text" placeholder="Buscar empleado por nombre o correo..." class="h-10 w-full rounded border border-slate-300 bg-white px-3 text-sm text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <span class="text-xs font-bold text-slate-500 bg-slate-200 px-3 py-1 rounded-full">{{ usuariosDb.length }} Usuarios</span>
                    </div>
                    
                    <div class="overflow-y-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white text-slate-400 text-[10px] uppercase tracking-wider sticky top-0 shadow-sm">
                                    <th class="p-4 font-bold border-b">Empleado</th>
                                    <th class="p-4 font-bold border-b">Correo Acceso</th>
                                    <th class="p-4 font-bold border-b text-center">Rol de Acceso</th>
                                    <th class="p-4 font-bold border-b text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                                <tr v-for="u in usuariosFiltrados" :key="u.id" class="hover:bg-slate-50 transition">
                                    <td class="p-4 font-black text-slate-800 flex items-center gap-2">
                                        <Users class="h-4 w-4 text-slate-400" /> {{ u.name }}
                                    </td>
                                    <td class="p-4">{{ u.email }}</td>
                                    <td class="p-4 text-center">
                                        <span :class="[
                                            'px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wider',
                                            u.role === 'admin' ? 'bg-red-100 text-red-700' :
                                            u.role === 'almacen' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'
                                        ]">
                                            {{ u.role }}
                                        </span>
                                    </td>
                                    <td class="p-4 flex items-center justify-end gap-2">
                                        <button @click="editarUsuario(u)" class="text-blue-500 hover:text-blue-700 p-2 bg-blue-50 rounded" title="Editar Credenciales">
                                            <Edit class="h-4 w-4" />
                                        </button>
                                        <button @click="eliminarUsuario(u.id)" class="text-red-500 hover:text-red-700 p-2 bg-red-50 rounded" title="Dar de baja">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>