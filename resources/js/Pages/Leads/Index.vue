<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, reactive } from 'vue';

const props = defineProps({ leads: Object, filters: Object, statuses: Array, businessId: Number });
const filterForm = reactive({
  q: props.filters.q || '',
  status: props.filters.status || '',
  follow_up: props.filters.follow_up || '',
  contact_state: props.filters.contact_state || '',
});

const statusClassMap = {
  new: 'text-sky-600 dark:text-sky-400',
  contacted: 'text-amber-600 dark:text-amber-400',
  quoted: 'text-violet-600 dark:text-violet-400',
  won: 'text-emerald-600 dark:text-emerald-400',
  lost: 'text-rose-600 dark:text-rose-400',
};

function statusClass(status) { return statusClassMap[status] || 'text-gray-600 dark:text-gray-300'; }
function applyFilters() {
  router.get(route('leads.index'), {
    q: filterForm.q || undefined,
    status: filterForm.status || undefined,
    follow_up: filterForm.follow_up || undefined,
    contact_state: filterForm.contact_state || undefined,
  }, { preserveState: true, replace: true });
}
function openLead(leadId) { router.visit(route('leads.show', leadId)); }
function resetFilters() {
  filterForm.q = '';
  filterForm.status = '';
  filterForm.follow_up = '';
  filterForm.contact_state = '';
  applyFilters();
}

function refreshInbox() {
  router.reload({ only: ['leads'], preserveState: true, preserveScroll: true });
}

onMounted(() => {
  window.Echo?.channel(`business.${props.businessId}`).listen('.lead.changed', refreshInbox);
});
onBeforeUnmount(() => window.Echo?.leave(`business.${props.businessId}`));
</script>

<template>
  <Head title="Lead Inbox" />
  <AuthenticatedLayout>
    <template #header><h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Lead Inbox</h2></template>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="mb-4 rounded-lg border bg-white p-4 shadow-sm dark:border-[#2a2d3a] dark:bg-[#1f2230]">
        <div class="grid gap-3 md:grid-cols-4">
          <input v-model="filterForm.q" @keyup.enter="applyFilters" type="text" class="rounded-md border-gray-300" placeholder="Search name, email, phone, subject" />
          <select v-model="filterForm.status" @change="applyFilters" class="rounded-md border-gray-300">
            <option value="">All statuses</option>
            <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
          </select>
          <select v-model="filterForm.follow_up" @change="applyFilters" class="rounded-md border-gray-300">
            <option value="">All follow-ups</option>
            <option value="overdue">Overdue</option>
            <option value="today">Due today</option>
            <option value="upcoming">Upcoming</option>
          </select>
          <select v-model="filterForm.contact_state" @change="applyFilters" class="rounded-md border-gray-300">
            <option value="">All contact states</option>
            <option value="unreplied">Unreplied</option>
            <option value="replied">Replied</option>
          </select>
        </div>
        <div class="mt-3 flex gap-2">
          <button @click="applyFilters" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Apply</button>
          <button @click="resetFilters" class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-[#3a3f52] dark:text-gray-200">Reset</button>
        </div>
      </div>
      <div class="overflow-hidden rounded-lg border bg-white shadow-sm dark:border-[#2a2d3a] dark:bg-[#1f2230]">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-left dark:bg-[#262a3a] dark:text-gray-200"><tr><th class="px-4 py-3">Lead</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Follow-up</th></tr></thead>
          <tbody>
            <tr v-for="lead in leads.data" :key="lead.id" class="cursor-pointer border-t hover:bg-gray-50 dark:border-[#2a2d3a] dark:hover:bg-[#262a3a]" @click="openLead(lead.id)"><td class="px-4 py-3"><p class="font-medium dark:text-gray-100">{{ lead.name }}</p><p class="text-gray-500 dark:text-gray-400">{{ lead.email }}</p></td><td :class="['px-4 py-3 capitalize font-semibold', statusClass(lead.status)]">{{ lead.status }}</td><td class="px-4 py-3 dark:text-gray-300">{{ lead.follow_up_date ?? '-' }}</td></tr>
            <tr v-if="leads.data.length===0"><td class="px-4 py-5 text-gray-500 dark:text-gray-400" colspan="3">No leads found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
