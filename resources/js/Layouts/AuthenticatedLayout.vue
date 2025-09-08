<script setup>
import {Link, usePage} from '@inertiajs/vue3'

const page = usePage()
const user = page.props.auth.user
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link href="/" class="text-xl font-bold text-blue-600">DocFlow</Link>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">Witaj, {{ user.name }} ({{ user.roles.join(', ') }})</span>
                        <Link href="/logout" method="post" as="button" class="text-sm text-red-600 hover:text-red-800">
                            Wyloguj
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div v-if="$page.props.flash.message" class="mb-4 p-4 text-green-700 bg-green-100 rounded-md">
                {{ $page.props.flash.message }}
            </div>
            <slot/>
        </main>
    </div>
</template>
