<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({ lead: Object, statuses: Array, templates: Array });
const flash = usePage().props.flash;
const activities = ref([...props.lead.activities]);

const leadForm = useForm({
  status: props.lead.status,
  quote_amount: props.lead.quote_amount,
  follow_up_date: props.lead.follow_up_date ? props.lead.follow_up_date.slice(0, 10) : '',
});
const noteForm = useForm({ body: '' });
const messageForm = useForm({ body: '' });
const isEditingQuote = ref(!props.lead.quote_amount);
const showEditIcon = ref(false);

const statusClassMap = {
  new: 'text-sky-600 dark:text-sky-400',
  contacted: 'text-amber-600 dark:text-amber-400',
  quoted: 'text-violet-600 dark:text-violet-400',
  won: 'text-emerald-600 dark:text-emerald-400',
  lost: 'text-rose-600 dark:text-rose-400',
};

function statusClass(status) { return statusClassMap[status] || 'text-gray-600 dark:text-gray-300'; }
function saveLead() { leadForm.patch(route('leads.update', props.lead.id)); }
function deleteLead() {
  if (confirm('Delete this lead? This cannot be undone.')) {
    leadForm.delete(route('leads.destroy', props.lead.id));
  }
}
function addNote() { noteForm.post(route('leads.notes.store', props.lead.id), { preserveScroll: true, onSuccess: () => noteForm.reset() }); }
function insertTemplate(body) { messageForm.body = body; }
function sendMessage() { if (messageForm.body.trim()) messageForm.post(route('leads.messages.store', props.lead.id), { preserveScroll: true, onSuccess: () => messageForm.reset() }); }
function onChatKeydown(event) {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault();
    sendMessage();
  }
}

function addActivity(item) {
  if (!activities.value.find((activity) => activity.id === item.id)) {
    activities.value.push(item);
  }
}

function activityLabel(activity) {
  return {
    enquiry_received: 'Enquiry received',
    customer_message: activity.actor_name || 'Customer message',
    business_message: activity.actor_name || 'Business message',
    note_added: activity.actor_name ? `Note from ${activity.actor_name}` : 'Note added',
    status_changed: 'Status update',
    quote_updated: 'Quote update',
    follow_up_updated: 'Follow-up update',
  }[activity.type] || 'Activity';
}

function activityCardClass(activity) {
  if (['enquiry_received', 'customer_message'].includes(activity.type)) {
    return 'mr-10 border border-gray-200 bg-gray-50 text-gray-800 dark:border-[#2a2d3a] dark:bg-[#262a3a] dark:text-gray-100';
  }

  if (activity.type === 'business_message') {
    return 'ml-10 bg-indigo-600 text-white';
  }

  return 'border border-gray-200 bg-white text-gray-800 dark:border-[#2a2d3a] dark:bg-[#1a1d29] dark:text-gray-100';
}

function activityMeta(activity) {
  if (activity.type === 'enquiry_received') {
    const details = [];
    if (activity.meta?.email) details.push(activity.meta.email);
    if (activity.meta?.phone) details.push(activity.meta.phone);
    if (activity.meta?.subject) details.push(activity.meta.subject);
    return details.join(' | ');
  }

  return activity.created_at ? new Date(activity.created_at).toLocaleString('en-GB') : '';
}

const timelineItems = computed(() => {
  const items = [...activities.value];

  if (!items.find((activity) => activity.type === 'enquiry_received') && props.lead.message) {
    items.unshift({
      id: 'legacy-enquiry',
      type: 'enquiry_received',
      actor_name: props.lead.name,
      body: props.lead.message,
      meta: {
        email: props.lead.email,
        phone: props.lead.phone,
        subject: props.lead.subject,
      },
      created_at: props.lead.created_at,
    });
  }

  return items.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
});

onMounted(() => {
  window.Echo?.channel(`lead-activity.${props.lead.id}`).listen('.lead.activity.created', (event) => addActivity(event.activity));
});

onBeforeUnmount(() => {
  window.Echo?.leave(`lead-activity.${props.lead.id}`);
});
</script>

<template>
  <Head :title="`Lead: ${lead.name}`" />
  <AuthenticatedLayout>
    <template #header><h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Lead Detail</h2></template>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
      <div v-if="flash.success" class="rounded border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-700">{{ flash.success }}</div>

      <div class="rounded-lg border bg-white p-4 shadow-sm dark:border-[#2a2d3a] dark:bg-[#1f2230]">
        <div class="mb-2 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Lead</p>
            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ lead.name }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ lead.email }} {{ lead.phone ? `| ${lead.phone}` : '' }}</p>
          </div>
          <p :class="['text-sm font-semibold uppercase tracking-wide', statusClass(leadForm.status)]">{{ leadForm.status }}</p>
        </div>

        <div class="grid gap-3 md:grid-cols-4 md:items-end">
          <label class="text-sm">Status
            <select v-model="leadForm.status" :class="['mt-1 w-full rounded-md border-gray-300 font-semibold', statusClass(leadForm.status)]">
              <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
            </select>
          </label>

          <div class="text-sm" @mouseenter="showEditIcon = true" @mouseleave="showEditIcon = false">
            <label class="block">Quote amount</label>
            <div v-if="!isEditingQuote" class="mt-1 flex items-center justify-between rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-semibold text-gray-800 dark:border-[#2a2d3a] dark:bg-[#262a3a] dark:text-gray-100">
              <span>{{ leadForm.quote_amount ? `$${Number(leadForm.quote_amount).toFixed(2)}` : 'No quote yet' }}</span>
              <button v-if="showEditIcon" @click="isEditingQuote = true" class="text-xs text-indigo-600">Edit</button>
            </div>
            <input v-else v-model="leadForm.quote_amount" @blur="isEditingQuote = false" type="number" step="0.01" class="mt-1 w-full rounded-md border-gray-300" />
          </div>

          <label class="text-sm">Follow-up
            <input v-model="leadForm.follow_up_date" type="date" class="mt-1 w-full rounded-md border-gray-300" />
          </label>

          <div class="flex gap-2">
            <button @click="saveLead" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Save</button>
            <button @click="deleteLead" class="rounded bg-rose-600 px-4 py-2 text-sm font-medium text-white">Delete</button>
          </div>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2 rounded-lg border bg-white p-5 shadow-sm dark:border-[#2a2d3a] dark:bg-[#1f2230]">
          <div class="flex items-center justify-between">
            <h4 class="font-semibold dark:text-gray-100">Lead Timeline</h4>
            <a :href="route('chat.show', lead.chat_token)" target="_blank" class="text-xs text-indigo-600" title="Share with customer so they can reply directly">Customer Chat Link</a>
          </div>

          <div class="max-h-80 space-y-2 overflow-y-auto rounded border p-3 dark:border-[#2a2d3a]">
            <div v-for="activity in timelineItems" :key="activity.id" class="rounded px-3 py-2 text-sm" :class="activityCardClass(activity)">
              <p class="mb-1 text-xs opacity-80">{{ activityLabel(activity) }}</p>
              <p v-if="activity.body">{{ activity.body }}</p>
              <p v-if="activityMeta(activity)" class="mt-1 text-xs opacity-75">{{ activityMeta(activity) }}</p>
            </div>
          </div>

          <div class="space-y-2">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Message customer</p>
            <textarea v-model="messageForm.body" @keydown="onChatKeydown" rows="4" class="w-full rounded-md border-gray-300" placeholder="Type message and press Enter to send (Shift+Enter for newline)..."></textarea>
          </div>
        </div>

        <div class="space-y-4">
          <div class="rounded-lg border bg-white p-5 shadow-sm dark:border-[#2a2d3a] dark:bg-[#1f2230]"><h4 class="mb-2 font-semibold dark:text-gray-100">Reply Templates</h4><button v-for="template in templates" :key="template.id" @click="insertTemplate(template.body)" class="mb-2 block w-full rounded border p-2 text-left text-sm hover:bg-gray-50 dark:border-[#2a2d3a] dark:text-gray-200 dark:hover:bg-[#262a3a]">{{ template.title }}</button></div>
          <div class="rounded-lg border bg-white p-5 shadow-sm dark:border-[#2a2d3a] dark:bg-[#1f2230]">
            <h4 class="mb-2 font-semibold dark:text-gray-100">Notes</h4>
            <form @submit.prevent="addNote" class="mb-3"><textarea v-model="noteForm.body" rows="3" class="w-full rounded-md border-gray-300"></textarea><button class="mt-2 rounded bg-gray-800 px-3 py-2 text-sm text-white">Add Note</button></form>
            <p class="text-xs text-gray-500 dark:text-gray-400">Notes and messages now appear together in the lead timeline.</p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
