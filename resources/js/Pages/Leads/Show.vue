<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ lead: Object, statuses: Array, templates: Array });

const leadForm = useForm({
  status: props.lead.status,
  quote_amount: props.lead.quote_amount,
  quote_notes: props.lead.quote_notes,
  follow_up_date: props.lead.follow_up_date,
});
const noteForm = useForm({ body: '' });

function saveLead() { leadForm.patch(route('leads.update', props.lead.id)); }
function addNote() { noteForm.post(route('leads.notes.store', props.lead.id), { onSuccess: () => noteForm.reset() }); }
function insertTemplate(body) { leadForm.quote_notes = body; }
</script>

<template>
  <Head :title="`Lead: ${lead.name}`" />
  <AuthenticatedLayout>
    <template #header><h2 class="text-xl font-semibold text-gray-800">Lead Detail</h2></template>
    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-3 lg:px-8">
      <div class="space-y-4 lg:col-span-2 rounded-lg border bg-white p-5 shadow-sm">
        <h3 class="text-lg font-semibold">{{ lead.name }}</h3>
        <p class="text-sm text-gray-600">{{ lead.email }} {{ lead.phone ? `| ${lead.phone}` : '' }}</p>
        <p class="text-sm text-gray-700">{{ lead.message }}</p>
        <div class="grid gap-3 sm:grid-cols-2">
          <label class="text-sm">Status<select v-model="leadForm.status" class="mt-1 w-full rounded-md border-gray-300"><option v-for="status in statuses" :key="status" :value="status">{{ status }}</option></select></label>
          <label class="text-sm">Follow-up date<input v-model="leadForm.follow_up_date" type="date" class="mt-1 w-full rounded-md border-gray-300" /></label>
          <label class="text-sm">Quote amount<input v-model="leadForm.quote_amount" type="number" step="0.01" class="mt-1 w-full rounded-md border-gray-300" /></label>
        </div>
        <label class="text-sm">Quote notes<textarea v-model="leadForm.quote_notes" rows="5" class="mt-1 w-full rounded-md border-gray-300"></textarea></label>
        <button @click="saveLead" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Save Lead</button>
      </div>
      <div class="space-y-4">
        <div class="rounded-lg border bg-white p-5 shadow-sm"><h4 class="mb-2 font-semibold">Reply Templates</h4><button v-for="template in templates" :key="template.id" @click="insertTemplate(template.body)" class="mb-2 block w-full rounded border p-2 text-left text-sm hover:bg-gray-50">{{ template.title }}</button></div>
        <div class="rounded-lg border bg-white p-5 shadow-sm">
          <h4 class="mb-2 font-semibold">Notes</h4>
          <form @submit.prevent="addNote" class="mb-3"><textarea v-model="noteForm.body" rows="3" class="w-full rounded-md border-gray-300"></textarea><button class="mt-2 rounded bg-gray-800 px-3 py-2 text-sm text-white">Add Note</button></form>
          <div class="space-y-2"><div v-for="note in lead.notes" :key="note.id" class="rounded border p-2 text-sm"><p>{{ note.body }}</p><p class="mt-1 text-xs text-gray-500">{{ note.user.name }}</p></div></div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
