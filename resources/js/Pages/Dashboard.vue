<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted } from 'vue';

const props = defineProps({ business: Object, totals: Object, followUpSummary: Object, followUpBuckets: Object, inboxLeads: Array });

const statusLabels = ['new', 'contacted', 'quoted', 'won', 'lost'];
const statusClassMap = {
  new: 'text-sky-600 dark:text-sky-400',
  contacted: 'text-amber-600 dark:text-amber-400',
  quoted: 'text-violet-600 dark:text-violet-400',
  won: 'text-emerald-600 dark:text-emerald-400',
  lost: 'text-rose-600 dark:text-rose-400',
};

function statusClass(status) { return statusClassMap[status] || 'text-gray-600 dark:text-gray-300'; }
function refreshDashboard() { router.reload({ only: ['totals', 'followUpSummary', 'followUpBuckets', 'inboxLeads'], preserveState: true, preserveScroll: true }); }

onMounted(() => window.Echo?.channel(`business.${props.business.id}`).listen('.lead.changed', refreshDashboard));
onBeforeUnmount(() => window.Echo?.leave(`business.${props.business.id}`));
</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">{{ business.name }}</h2>
        <div class="text-sm text-indigo-600">
          <span class="font-semibold">Public Enquiry Link:</span>
          <a :href="route('enquiry.create', business.slug)" target="_blank" class="ml-1 underline" title="Share this link with customers so they can submit enquiries.">{{ route('enquiry.create', business.slug) }}</a>
        </div>
      </div>
    </template>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <Link v-for="status in statusLabels" :key="status" :href="route('leads.index', { status })" class="rounded-xl border bg-white px-3 py-3 text-center shadow-sm transition hover:bg-gray-50 dark:border-[#2a2d3a] dark:bg-[#1f2230] dark:hover:bg-[#262a3a]">
          <div :class="['text-sm font-semibold capitalize', statusClass(status)]">{{ status }}</div>
          <div class="mt-1 text-lg font-semibold text-gray-700 dark:text-gray-200">{{ totals[status] ?? 0 }}</div>
        </Link>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border bg-white p-5 shadow-sm dark:border-[#2a2d3a] dark:bg-[#1f2230]">
          <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-gray-100">Lead Inbox</h3>
          <div class="overflow-hidden rounded border dark:border-[#2a2d3a]">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50 text-left dark:bg-[#262a3a] dark:text-gray-200"><tr><th class="px-4 py-3">Lead</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Follow-up</th></tr></thead>
              <tbody>
                <tr v-for="lead in inboxLeads" :key="lead.id" class="cursor-pointer border-t hover:bg-gray-50 dark:border-[#2a2d3a] dark:hover:bg-[#262a3a]" @click="router.visit(route('leads.show', lead.id))"><td class="px-4 py-3"><p class="font-medium text-gray-900 dark:text-gray-100">{{ lead.name }}</p><p class="text-gray-500 dark:text-gray-400">{{ lead.email }}</p></td><td :class="['px-4 py-3 font-semibold capitalize', statusClass(lead.status)]">{{ lead.status }}</td><td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ lead.follow_up_date ?? '-' }}</td></tr>
                <tr v-if="inboxLeads.length===0"><td class="px-4 py-5 text-gray-500 dark:text-gray-400" colspan="3">No leads found.</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="rounded-lg border bg-white p-5 shadow-sm dark:border-[#2a2d3a] dark:bg-[#1f2230]">
          <div class="flex items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Follow-ups</h3>
            <div class="flex gap-2 text-xs font-medium">
              <span class="rounded-full bg-rose-100 px-2 py-1 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300">Overdue {{ followUpSummary.overdue ?? 0 }}</span>
              <span class="rounded-full bg-amber-100 px-2 py-1 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">Today {{ followUpSummary.today ?? 0 }}</span>
              <span class="rounded-full bg-sky-100 px-2 py-1 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">Upcoming {{ followUpSummary.upcoming ?? 0 }}</span>
            </div>
          </div>

          <div class="mt-4 space-y-4">
            <div v-for="bucket in [{ key: 'overdue', label: 'Overdue' }, { key: 'today', label: 'Due Today' }, { key: 'upcoming', label: 'Upcoming' }]" :key="bucket.key">
              <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ bucket.label }}</p>
              <div v-if="!followUpBuckets[bucket.key]?.length" class="rounded border border-dashed px-3 py-2 text-sm text-gray-500 dark:border-[#2a2d3a] dark:text-gray-400">None</div>
              <div v-else class="space-y-2">
                <Link v-for="lead in followUpBuckets[bucket.key]" :key="lead.id" :href="route('leads.show', lead.id)" class="flex items-center justify-between rounded border p-3 hover:bg-gray-50 dark:border-[#2a2d3a] dark:hover:bg-[#262a3a]">
                  <div>
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ lead.name }}</p>
                    <p :class="['text-sm capitalize font-semibold', statusClass(lead.status)]">{{ lead.status }}</p>
                  </div>
                  <p class="text-sm text-gray-600 dark:text-gray-300">{{ lead.follow_up_date }}</p>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
