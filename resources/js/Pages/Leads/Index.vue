<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ leads: Object, filters: Object, statuses: Array });

function filterByStatus(event) {
  router.get(route('leads.index'), { status: event.target.value || undefined }, { preserveState: true, replace: true });
}
</script>

<template>
  <Head title="Lead Inbox" />
  <AuthenticatedLayout>
    <template #header><h2 class="text-xl font-semibold text-gray-800">Lead Inbox</h2></template>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="mb-4"><select class="rounded-md border-gray-300" :value="filters.status" @change="filterByStatus"><option value="">All statuses</option><option v-for="status in statuses" :key="status" :value="status">{{ status }}</option></select></div>
      <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-left"><tr><th class="px-4 py-3">Lead</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Follow-up</th><th class="px-4 py-3"></th></tr></thead>
          <tbody>
            <tr v-for="lead in leads.data" :key="lead.id" class="border-t"><td class="px-4 py-3"><p class="font-medium">{{ lead.name }}</p><p class="text-gray-500">{{ lead.email }}</p></td><td class="px-4 py-3 capitalize">{{ lead.status }}</td><td class="px-4 py-3">{{ lead.follow_up_date ?? '-' }}</td><td class="px-4 py-3 text-right"><Link :href="route('leads.show', lead.id)" class="text-indigo-600">Open</Link></td></tr>
            <tr v-if="leads.data.length===0"><td class="px-4 py-5 text-gray-500" colspan="4">No leads found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
