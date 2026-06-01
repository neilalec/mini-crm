<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({ templates: Array });
const form = useForm({ title: '', body: '' });
const editForm = useForm({ title: '', body: '' });

function createTemplate() { form.post(route('templates.store'), { onSuccess: () => form.reset() }); }
function updateTemplate(id) { editForm.patch(route('templates.update', id)); }
function destroyTemplate(id) { editForm.delete(route('templates.destroy', id)); }
</script>

<template>
  <Head title="Reply Templates" />
  <AuthenticatedLayout>
    <template #header><h2 class="text-xl font-semibold text-gray-800">Reply Templates</h2></template>
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
      <form @submit.prevent="createTemplate" class="rounded-lg border bg-white p-5 shadow-sm">
        <h3 class="mb-3 font-semibold">New Template</h3>
        <input v-model="form.title" placeholder="Title" class="mb-2 w-full rounded-md border-gray-300" />
        <textarea v-model="form.body" rows="4" placeholder="Body" class="w-full rounded-md border-gray-300"></textarea>
        <button class="mt-2 rounded bg-indigo-600 px-4 py-2 text-sm text-white">Save Template</button>
      </form>
      <div v-for="template in templates" :key="template.id" class="rounded-lg border bg-white p-5 shadow-sm">
        <input v-model="editForm.title" :placeholder="template.title" class="mb-2 w-full rounded-md border-gray-300" @focus="editForm.title = template.title; editForm.body = template.body"/>
        <textarea v-model="editForm.body" rows="4" class="w-full rounded-md border-gray-300"></textarea>
        <div class="mt-2 flex gap-2"><button @click="updateTemplate(template.id)" class="rounded bg-gray-800 px-3 py-2 text-sm text-white">Update</button><button @click="destroyTemplate(template.id)" class="rounded bg-red-600 px-3 py-2 text-sm text-white">Delete</button></div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
