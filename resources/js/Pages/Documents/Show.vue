<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {Link, usePage, useForm} from '@inertiajs/vue3'
import {computed} from 'vue'

const props = defineProps({document: Object})
const page = usePage()
const form = useForm({})

const isManager = computed(() => page.props.auth.user.roles.includes('manager'))
const isEmployee = computed(() => page.props.auth.user.roles.includes('employee'))

const action = (route) => form.post(route)
</script>

<template>
    <AuthenticatedLayout>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Szczegóły i Akcje -->
            <div class="md:col-span-2 bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ document.title }}</h2>
                        <p class="text-sm text-gray-500 mt-1">Autor: {{ document.user.name }}</p>
                    </div>
                    <span class="px-3 py-1 text-sm font-bold uppercase rounded-full bg-gray-200 text-gray-800">
                        Status: {{ document.state }}
                    </span>
                </div>

                <div class="mt-4">
                    <a :href="`/documents/${document.id}/download`"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        📥 Pobierz plik załącznika
                    </a>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 space-x-3">
                    <!-- Akcje Pracownika -->
                    <button v-if="isEmployee && document.state === 'draft'"
                            @click="action(`/documents/${document.id}/submit`)"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Wyślij do akceptacji (Zmień na Pending)
                    </button>

                    <!-- Akcje Managera -->
                    <template v-if="isManager && document.state === 'pending'">
                        <button @click="action(`/documents/${document.id}/approve`)"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Zatwierdź
                        </button>
                        <button @click="action(`/documents/${document.id}/reject`)"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Odrzuć
                        </button>
                    </template>
                </div>
            </div>

            <!-- Oś czasu - Audit Log -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Historia zmian (Audit Log)</h3>
                <ol class="relative border-l border-gray-200 ml-3">
                    <li v-for="log in document.audit_logs" :key="log.id" class="mb-6 ml-4">
                        <div
                            class="absolute w-3 h-3 bg-gray-300 rounded-full mt-1.5 -left-1.5 border border-white"></div>
                        <time class="mb-1 text-xs font-normal text-gray-400">
                            {{ new Date(log.created_at).toLocaleString() }}
                        </time>
                        <div class="text-sm font-medium text-gray-900">
                            Status: {{ log.old_state }} → <span class="text-blue-600">{{ log.new_state }}</span>
                        </div>
                        <div class="text-xs text-gray-500">
                            Użytkownik: {{ log.user ? log.user.name : 'System' }}
                        </div>
                    </li>
                    <li v-if="!document.audit_logs.length" class="text-sm text-gray-500 ml-4">
                        Brak wpisów w historii.
                    </li>
                </ol>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
