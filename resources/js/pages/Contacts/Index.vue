<template>
  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <Link
                :href="route('customers.show', customer.id)"
                class="mr-4 text-muted-foreground hover:text-foreground transition-colors"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
              </Link>
              <div>
                <h1 class="text-2xl font-semibold text-foreground">Contacts</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                  Manage contacts for {{ customer.display_name }}
                </p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <Link
                :href="route('contacts.create') + '?customer_id=' + customer.id"
                class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-primary/90 focus:outline-none focus:border-primary focus:ring-2 focus:ring-offset-2 focus:ring-primary transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Contact
              </Link>
              <button
                @click="showBulkActionsModal = true"
                class="inline-flex items-center px-4 py-2 bg-secondary text-secondary-foreground border border-border rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-secondary/80 focus:outline-none focus:border-secondary focus:ring-2 focus:ring-offset-2 focus:ring-secondary transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4v2m0 14V4m6 0h6m-6 0h6m-6 10h6" />
                </svg>
                Bulk Actions
              </button>
            </div>
          </div>
        </div>

        <!-- Contact Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-card p-6 rounded-lg border border-border">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-md">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Total Contacts</p>
                <p class="text-2xl font-semibold text-foreground">{{ contacts.total }}</p>
              </div>
            </div>
          </div>

          <div class="bg-card p-6 rounded-lg border border-border">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 dark:bg-green-900 rounded-md">
                <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Active</p>
                <p class="text-2xl font-semibold text-foreground">{{ getActiveContactsCount() }}</p>
              </div>
            </div>
          </div>

          <div class="bg-card p-6 rounded-lg border border-border">
            <div class="flex items-center">
              <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-md">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m2 2h10m4-14h-4m2 2h-2m-2-2h2" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Primary</p>
                <p class="text-2xl font-semibold text-foreground">{{ getPrimaryContactsCount() }}</p>
              </div>
            </div>
          </div>

          <div class="bg-card p-6 rounded-lg border border-border">
            <div class="flex items-center">
              <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-md">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 3-3 3m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">New This Month</p>
                <p class="text-2xl font-semibold text-foreground">{{ getNewThisMonthCount() }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-card rounded-lg border border-border p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-foreground mb-2">Search Contacts</label>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search by name, email, phone..."
                class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                @input="debounceSearch"
              />
            </div>

            <!-- Status Filter -->
            <div>
              <label class="block text-sm font-medium text-foreground mb-2">Status</label>
              <select
                v-model="filters.is_active"
                class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                @change="applyFilters"
              >
                <option value="true">Active</option>
                <option value="false">Inactive</option>
                <option value="">All</option>
              </select>
            </div>

            <!-- Primary Contact Filter -->
            <div>
              <label class="block text-sm font-medium text-foreground mb-2">Primary Contact</label>
              <select
                v-model="filters.is_primary"
                class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                @change="applyFilters"
              >
                <option value="">All Contacts</option>
                <option value="true">Primary Only</option>
                <option value="false">Non-Primary</option>
              </select>
            </div>
          </div>

          <!-- Clear Filters Button -->
          <div class="flex justify-end">
            <button
              @click="clearFilters"
              class="px-4 py-2 bg-secondary text-secondary-foreground border border-border rounded-md hover:bg-secondary/80 focus:outline-none focus:ring-2 focus:ring-secondary transition ease-in-out duration-150"
            >
              Clear Filters
            </button>
          </div>
        </div>

        <!-- Contacts Table -->
        <div class="bg-card rounded-lg border border-border overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
              <thead class="bg-muted/50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    Contact
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    Title
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    Department
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    Contact Info
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-card divide-y divide-border">
                <tr v-for="contact in contacts.data" :key="contact.id" class="hover:bg-muted/50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                          <span class="text-sm font-medium text-primary">
                            {{ getContactInitials(contact.first_name, contact.last_name) }}
                          </span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="flex items-center">
                          <p class="text-sm font-medium text-foreground">
                            {{ contact.first_name }} {{ contact.last_name }}
                          </p>
                          <span v-if="contact.is_primary" class="ml-2 px-2 py-1 text-xs bg-primary/10 text-primary rounded-full">Primary</span>
                        </div>
                        <p v-if="contact.email" class="text-sm text-muted-foreground">{{ contact.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                    {{ contact.job_title || '—' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                    {{ contact.department || '—' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="space-y-1">
                      <div v-if="contact.phone" class="text-sm text-foreground">
                        <a :href="`tel:${contact.phone}`" class="text-primary hover:text-primary/80">
                          {{ contact.phone }}
                        </a>
                      </div>
                      <div v-if="contact.mobile" class="text-sm text-foreground">
                        <a :href="`tel:${contact.mobile}`" class="text-primary hover:text-primary/80">
                          {{ contact.mobile }}
                        </a>
                      </div>
                      <div v-if="!contact.phone && !contact.mobile" class="text-sm text-muted-foreground">
                        No phone number
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getContactStatusClass(contact)" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                      {{ contact.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Link
                      :href="route('contacts.show', { customer: customer.id, contact: contact.id })"
                      class="text-primary hover:text-primary/80 mr-3"
                    >
                      View
                    </Link>
                    <Link
                      :href="route('contacts.edit', { customer: customer.id, contact: contact.id })"
                      class="text-primary hover:text-primary/80 mr-3"
                    >
                      Edit
                    </Link>
                    <button
                      @click="!contact.is_primary ? makePrimary(contact) : null"
                      :disabled="contact.is_primary || processing"
                      class="text-yellow-600 hover:text-yellow-700 disabled:text-muted-foreground disabled:cursor-not-allowed"
                      title="Make Primary Contact"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 2.902 0l1.94 1.94a3.007 3.007 0 0 0-4.244 0l-1.94-1.94a3.007 3.007 0 0 0 0-4.244L3.07 5.757a3.007 3.007 0 0 0 4.244 0l1.94 1.94c.889.889 2.303.889 3.499-.01l.176-.005a3.006 3.006 0 0 0 3.398-.012l.176-.005zm.1-3.698a1.002 1.002 0 0 0-.331-.686L3.51 9.254a1 1 0 0 0 1.414 0l4.95 4.95a1 1 0 0 0 1.414 0l4.95-4.95a1 1 0 0 0 0-1.414l-4.95-4.95a1 1 0 0 0-.707.293l-4.95 4.95a1 1 0 0 0 1.414 1.414l4.95-4.95A1 1 0 0 0 11.07-.01z" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="contacts.data && contacts.data.length > 0" class="bg-card px-6 py-3 flex items-center justify-between border-t border-border">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="prevPage"
                :disabled="!contacts.prev_page_url"
                class="relative inline-flex items-center px-4 py-2 border border-border text-sm font-medium rounded-md text-foreground bg-background hover:bg-muted"
                :class="{ 'opacity-50 cursor-not-allowed': !contacts.prev_page_url }"
              >
                Previous
              </button>
              <button
                @click="nextPage"
                :disabled="!contacts.next_page_url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-border text-sm font-medium rounded-md text-foreground bg-background hover:bg-muted"
                :class="{ 'opacity-50 cursor-not-allowed': !contacts.next_page_url }"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-muted-foreground">
                  Showing
                  <span class="font-medium">{{ contacts.from || 0 }}</span>
                  to
                  <span class="font-medium">{{ contacts.to || 0 }}</span>
                  of
                  <span class="font-medium">{{ contacts.total }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button
                    @click="prevPage"
                    :disabled="!contacts.prev_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-border bg-background text-sm font-medium text-foreground hover:bg-muted"
                    :class="{ 'opacity-50 cursor-not-allowed': !contacts.prev_page_url }"
                  >
                    <span class="sr-only">Previous</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <button
                    @click="nextPage"
                    :disabled="!contacts.next_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-border bg-background text-sm font-medium text-foreground hover:bg-muted"
                    :class="{ 'opacity-50 cursor-not-allowed': !contacts.next_page_url }"
                  >
                    <span class="sr-only">Next</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10 10.586 3.293 3.293a1 1 0 011.414 1.414l4 4a1 1 0 001.414 0l4-4a1 1 0 00-1.414-1.414l-4 4a1 1 0 00-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="contacts.data && contacts.data.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-foreground">No contacts found</h3>
            <p class="mt-1 text-sm text-muted-foreground">
              {{ hasActiveFilters() ? 'Try adjusting your search or filters' : 'Get started by adding the first contact for this customer' }}
            </p>
            <div class="mt-6">
              <Link
                :href="route('contacts.create') + '?customer_id=' + customer.id"
                class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-primary/90 focus:outline-none focus:border-primary focus:ring-2 focus:ring-offset-2 focus:ring-primary transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Contact
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Actions Modal -->
    <div v-if="showBulkActionsModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block align-bottom bg-card rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-card px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <h3 class="text-lg leading-6 font-medium text-foreground" id="modal-title">
              Bulk Actions
            </h3>
            <div class="mt-2">
              <p class="text-sm text-muted-foreground">
                Select multiple contacts and perform actions on them.
              </p>
            </div>
          </div>
          <div class="bg-card px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="showBulkActionsModal = false"
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-border shadow-sm px-4 py-2 bg-card text-base font-medium text-foreground hover:bg-muted focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
            <button
              @click="exportContacts"
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm"
            >
              Export Contacts
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { debounce } from 'lodash-es'

interface Contact {
  id: number
  first_name: string
  last_name: string
  email: string | null
  phone: string | null
  mobile: string | null
  job_title: string | null
  department: string | null
  is_primary: boolean
  is_active: boolean
  created_at: string
}

interface Customer {
  id: number
  display_name: string
}

interface ContactsResponse {
  data: Contact[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
  prev_page_url: string | null
  next_page_url: string | null
}

const props = defineProps<{
  customer: Customer
  contacts: ContactsResponse
  filters: {
    search: string
    is_active: string
    is_primary: string
  }
}>()

const localFilters = reactive({ ...props.filters })
const currentPage = ref(props.contacts.current_page)
const processing = ref(false)
const showBulkActionsModal = ref(false)

// Debounced search function
const debounceSearch = debounce(() => {
  applyFilters()
}, 300)

function applyFilters() {
  const params = {
    ...localFilters,
    page: 1, // Reset to first page when filtering
  }

  router.get(route('contacts.index', props.customer.id), params, {
    preserveState: true,
    preserveScroll: true,
  })
}

function clearFilters() {
  Object.assign(localFilters, {
    search: '',
    is_active: 'true',
    is_primary: '',
  })
  applyFilters()
}

function prevPage() {
  if (props.contacts.prev_page_url) {
    currentPage.value--
    goToPage(currentPage.value)
  }
}

function nextPage() {
  if (props.contacts.next_page_url) {
    currentPage.value++
    goToPage(currentPage.value)
  }
}

function goToPage(page: number) {
  const params = {
    ...localFilters,
    page,
  }

  router.get(route('contacts.index', props.customer.id), params, {
    preserveState: true,
    preserveScroll: true,
  })
}

function hasActiveFilters(): boolean {
  return !!(localFilters.search ||
           localFilters.is_primary !== '')
}

function getContactInitials(firstName: string, lastName: string): string {
  return `${firstName.charAt(0).toUpperCase()}${lastName.charAt(0).toUpperCase()}`
}

function getContactStatusClass(contact: Contact): string {
  if (contact.is_active) {
    return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
  }
  return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
}

function getActiveContactsCount(): number {
  return props.contacts.data.filter(contact => contact.is_active).length
}

function getPrimaryContactsCount(): number {
  return props.contacts.data.filter(contact => contact.is_primary).length
}

function getNewThisMonthCount(): number {
  const now = new Date()
  const currentMonth = now.getMonth()
  const currentYear = now.getFullYear()

  return props.contacts.data.filter(contact => {
    const contactDate = new Date(contact.created_at)
    return contactDate.getMonth() === currentMonth &&
           contactDate.getFullYear() === currentYear
  }).length
}

function makePrimary(contact: Contact) {
  processing.value = true

  router.post(`/contacts/${props.customer.id}/${contact.id}/make-primary`, {}, {
    onSuccess: () => {
      // Refresh the contacts list
      router.reload({ only: ['contacts', 'filters'] })
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

function exportContacts() {
  // TODO: Implement export functionality
  showBulkActionsModal.value = false
}

// Watch for prop changes to update local state
watch(() => props.filters, (newFilters) => {
  Object.assign(localFilters, newFilters)
}, { deep: true })

watch(() => props.contacts.current_page, (newPage) => {
  currentPage.value = newPage
})
</script>