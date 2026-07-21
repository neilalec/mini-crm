<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import axios from 'axios';

const props = defineProps({ templates: Array, businessId: Number });
const form = reactive({ title: '', body: '' });
const templates = ref([...props.templates]);
const expanded = ref({});

async function createTemplate() {
  await axios.post(route('templates.store'), form);
  form.title = '';
  form.body = '';
}

async function updateTemplate(template) {
  await axios.patch(route('templates.update', template.id), { title: template.title, body: template.body });
}

async function destroyTemplate(id) {
  await axios.delete(route('templates.destroy', id));
}

function toggle(id) { expanded.value[id] = !expanded.value[id]; }

function onTemplateChanged(event) {
  const { action, template } = event;
  if (action === 'created' && !templates.value.find((t) => t.id === template.id)) {
    templates.value.unshift(template);
  }
  if (action === 'updated') {
    const idx = templates.value.findIndex((t) => t.id === template.id);
    if (idx !== -1) templates.value[idx] = template;
  }
  if (action === 'deleted') {
    templates.value = templates.value.filter((t) => t.id !== template.id);
  }
}

onMounted(() => {
  window.Echo?.channel(`business.${props.businessId}`).listen('.template.changed', onTemplateChanged);
});
onBeforeUnmount(() => window.Echo?.leave(`business.${props.businessId}`));
</script>

<template>
  <Head title="Reply Templates" />
  <AuthenticatedLayout>
    <template #header><h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Reply Templates</h2></template>
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
      <form @submit.prevent="createTemplate" class="rounded-lg border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <h3 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">New Template</h3>
        <input v-model="form.title" placeholder="Title" class="mb-2 w-full rounded-md border-gray-300" />
        <textarea v-model="form.body" rows="4" placeholder="Body" class="w-full rounded-md border-gray-300"></textarea>
        <button class="mt-2 rounded bg-indigo-600 px-4 py-2 text-sm text-white">Save Template</button>
      </form>

      <div v-for="template in templates" :key="template.id" class="rounded-lg border bg-white p-4 shadow-sm transition hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">
        <button @click="toggle(template.id)" class="flex w-full items-center justify-between text-left">
          <span class="font-medium text-gray-900 dark:text-gray-100">{{ template.title }}</span>
          <span class="text-xs text-gray-500">{{ expanded[template.id] ? 'Hide' : 'Show' }}</span>
        </button>

        <div v-if="expanded[template.id]" class="mt-3">
          <input v-model="template.title" class="mb-2 w-full rounded-md border-gray-300" />
          <textarea v-model="template.body" rows="4" class="w-full rounded-md border-gray-300"></textarea>
          <div class="mt-2 flex gap-2">
            <button @click="updateTemplate(template)" class="rounded bg-gray-800 px-3 py-2 text-sm text-white">Update</button>
            <button @click="destroyTemplate(template.id)" class="rounded bg-red-600 px-3 py-2 text-sm text-white">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
