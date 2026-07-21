<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    businessName: {
        type: String,
    },
});

const isDark = ref(document.documentElement.classList.contains('dark'));

function toggleTheme() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Profile</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-slate-900">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" :business-name="businessName" class="max-w-xl" />
                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-slate-900">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Appearance</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Switch between light and dark mode.</p>
                    <button @click="toggleTheme" class="mt-4 rounded bg-indigo-600 px-4 py-2 text-sm text-white">
                        {{ isDark ? 'Use light mode' : 'Use dark mode' }}
                    </button>
                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-slate-900">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-slate-900">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
