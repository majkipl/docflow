<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {Link} from '@inertiajs/vue3'

defineProps({
    documents: Object,
    viewType: String
})
</script>

<template>
    <AuthenticatedLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">
                {{ viewType === 'manager' ? 'Dokumenty oczekujące na akceptację' : 'Moje Dokumenty' }}
            </h1>
            <Link v-if="viewType === 'employee'" href="/documents/create"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                + Wgraj Nowy Dokument
            </Link>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                <li v-for="doc in documents.data" :key="doc.id">
                    <Link :href="`/documents/${doc.id}`" class="block hover:bg-gray-50">
                        <div class="px-4 py-4 flex items-center justify-between sm:px-6">
                            <div class="text-sm font-medium text-blue-600 truncate">{{ doc.title }}</div>
                            <div class="ml-2 flex-shrink-0 flex">
                                <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 uppercase">
                                    {{ doc.state }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </li>
                <li v-if="documents.data.length === 0" class="px-4 py-8 text-center text-gray-500">
                    Brak dokumentów do wyświetlenia.
                </li>
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
