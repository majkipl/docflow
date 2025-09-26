<script setup>
import { useForm, Head } from '@inertiajs/vue3'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}

const fillCredentials = (email) => {
    form.email = email
    form.password = 'password'
}
</script>

<template>
    <Head title="Logowanie - DocFlow" />

    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gray-50">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h1 class="text-center text-3xl font-extrabold text-blue-600 tracking-tight">
                DocFlow
            </h1>
            <h2 class="mt-2 text-center text-sm text-gray-600">
                System Elektronicznego Obiegu i Akceptacji Dokumentów
            </h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Adres E-mail
                        </label>
                        <div class="mt-1">
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                autocomplete="username"
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                :class="{'border-red-500': form.errors.email}"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Hasło
                        </label>
                        <div class="mt-1">
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                autocomplete="current-password"
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                :class="{'border-red-500': form.errors.password}"
                            />
                        </div>
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label for="remember" class="ml-2 block text-sm text-gray-900">
                                Zapamiętaj mnie
                            </label>
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Logowanie...' : 'Zaloguj się' }}
                        </button>
                    </div>
                </form>

                <!-- Szybkie logowanie na konta testowe -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 text-center">
                        Konta demonstracyjne (hasło: password)
                    </p>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            type="button"
                            @click="fillCredentials('admin@docflow.local')"
                            class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded border border-gray-300 text-center"
                        >
                            Admin
                        </button>
                        <button
                            type="button"
                            @click="fillCredentials('manager@docflow.local')"
                            class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded border border-gray-300 text-center"
                        >
                            Manager
                        </button>
                        <button
                            type="button"
                            @click="fillCredentials('employee@docflow.local')"
                            class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded border border-gray-300 text-center"
                        >
                            Pracownik
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
