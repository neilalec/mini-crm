<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  business: Object,
  totals: Object,
  upcomingFollowUps: Array,
});

const statusLabels = ['new', 'contacted', 'quoted', 'won', 'lost'];
</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ business.name }} Dashboard</h2>
    </template>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div v-for="status in statusLabels" :key="status" class="rounded-lg border bg-white p-4 shadow-sm">
          <p class="text-sm capitalize text-gray-500">{{ status }}</p>
          <p class="mt-1 text-2xl font-semibold text-gray-900">{{ totals[status] ?? 0 }}</p>
        </div>
      </div>

      <div class="rounded-lg border bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Upcoming Follow-ups</h3>
          <Link :href="route('leads.index')" class="text-sm text-indigo-600">Open Inbox</Link>
        </div>
        <div v-if="upcomingFollowUps.length === 0" class="text-sm text-gray-500">No follow-up reminders scheduled.</div>
        <div v-else class="space-y-2">
          <Link v-for="lead in upcomingFollowUps" :key="lead.id" :href="route('leads.show', lead.id)" class="flex items-center justify-between rounded border p-3 hover:bg-gray-50">
            <div>
              <p class="font-medium text-gray-900">{{ lead.name }}</p>
              <p class="text-sm capitalize text-gray-500">{{ lead.status }}</p>
            </div>
            <p class="text-sm text-gray-600">{{ lead.follow_up_date }}</p>
          </Link>
        </div>
      </div>

      <div class="rounded-lg border bg-white p-5 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900">Public Enquiry Link</h3>
        <a :href="route('enquiry.create', business.slug)" class="mt-2 block text-sm text-indigo-600" target="_blank">{{ route('enquiry.create', business.slug) }}</a>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
