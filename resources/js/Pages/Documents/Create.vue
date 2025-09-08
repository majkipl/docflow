<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {useForm} from '@inertiajs/vue3'

const form = useForm({
    title: '',
    file: null,
})

const submit = () => {
    form.post('/documents', {
        preserveScroll: true,
    })
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-6">Wgraj nowy dokument</h2>
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tytuł dokumentu</label>
                    <input v-model="form.title" type="text"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border" required/>
                    <div v-if="form.errors.title" class="text-red-600 text-sm mt-1">{{ form.errors.title }}</div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700">Plik</label>
                    <input type="file" @input="form.file = $event.target.files[0]"
                           class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required/>
                    <div v-if="form.errors.file" class="text-red-600 text-sm mt-1">{{ form.errors.file }}</div>
                </div>

                <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                    Wgraj i zapisz jako Draft
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
