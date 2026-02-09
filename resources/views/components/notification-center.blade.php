{{-- Centro de Notificaciones --}}
<div x-data="{ open: false, showClearModal: false }" class="relative">
    {{-- Botón campana --}}
    <flux:button size="sm" variant="ghost" square @click="open = !open" class="relative">
        {{-- Ícono campana --}}
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        {{-- Badge contador --}}
        <template x-if="$store.notifications.unreadCount > 0">
            <span
                class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                x-text="$store.notifications.unreadCount > 9 ? '9+' : $store.notifications.unreadCount"
            ></span>
        </template>
    </flux:button>

    {{-- Panel de notificaciones --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        @click.away="open = false"
        class="absolute right-0 top-full z-50 mt-2 w-80 sm:w-96 rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
    >
        {{-- Encabezado --}}
        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Notificaciones</h3>
                <template x-if="$store.notifications.unreadCount > 0">
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400" x-text="$store.notifications.unreadCount + ' nueva' + ($store.notifications.unreadCount > 1 ? 's' : '')"></span>
                </template>
            </div>
            <div class="flex items-center gap-1">
                <button
                    @click="$store.notifications.markAllAsRead()"
                    class="rounded-md p-1 text-xs text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    title="Marcar todas como leídas"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </button>
                <button
                    @click="showClearModal = true"
                    class="rounded-md p-1 text-xs text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    title="Limpiar todo"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Lista de notificaciones --}}
        <div class="max-h-80 overflow-y-auto">
            <template x-if="$store.notifications.items.length === 0">
                <div class="flex flex-col items-center justify-center gap-2 px-4 py-8">
                    <svg class="size-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    <p class="text-sm text-zinc-400 dark:text-zinc-500">No hay notificaciones</p>
                </div>
            </template>

            <template x-for="notification in $store.notifications.items" :key="notification.id">
                <div
                    @click="$store.notifications.markAsRead(notification.id)"
                    class="flex cursor-pointer items-start gap-3 border-b border-zinc-100 px-4 py-3 transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50"
                    :class="!notification.read ? 'bg-blue-50/50 dark:bg-blue-900/10' : ''"
                >
                    {{-- Ícono por tipo --}}
                    <div class="mt-0.5 flex-shrink-0">
                        {{-- Success --}}
                        <template x-if="notification.type === 'success'">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                <svg class="size-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </template>
                        {{-- Error --}}
                        <template x-if="notification.type === 'error'">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                                <svg class="size-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </template>
                        {{-- Warning --}}
                        <template x-if="notification.type === 'warning'">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                                <svg class="size-4 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </template>
                        {{-- Info --}}
                        <template x-if="notification.type === 'info'">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                <svg class="size-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </template>
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-zinc-800 dark:text-zinc-200 break-words" x-text="notification.message"></p>
                        <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500" x-text="notification.time"></p>
                    </div>

                    {{-- Indicador no leída --}}
                    <template x-if="!notification.read">
                        <div class="mt-2 h-2 w-2 flex-shrink-0 rounded-full bg-blue-500"></div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- Modal de confirmación para limpiar todo --}}
    <flux:modal name="clear-notifications" x-model="showClearModal" class="max-w-md">
        <div class="space-y-4">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <flux:heading size="lg">Limpiar notificaciones</flux:heading>
                    <flux:subheading class="mt-1">
                        ¿Estás seguro de que deseas eliminar todas las notificaciones? Esta acción no se puede deshacer.
                    </flux:subheading>
                </div>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" @click="showClearModal = false">Cancelar</flux:button>
                <flux:button 
                    variant="danger" 
                    @click="$store.notifications.clearAll(); showClearModal = false; open = false"
                >
                    Eliminar todo
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
