<template>
  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-semibold text-foreground">Customers</h1>
              <p class="mt-1 text-sm text-muted-foreground">
                Manage your customer relationships and contacts
              </p>
            </div>
            <Link
              :href="route('customers.create')"
              class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-primary/90 focus:outline-none focus:border-primary focus:ring-2 focus:ring-offset-2 focus:ring-primary transition ease-in-out duration-150"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add Customer
            </Link>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-card p-6 rounded-lg border border-border">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-md">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Total Customers</p>
                <p class="text-2xl font-semibold text-foreground">{{ customers.total }}</p>
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
                <p class="text-2xl font-semibold text-foreground">{{ getStatByStatus('active') }}</p>
              </div>
            </div>
          </div>

          <div class="bg-card p-6 rounded-lg border border-border">
            <div class="flex items-center">
              <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-md">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Prospects</p>
                <p class="text-2xl font-semibold text-foreground">{{ getStatByStatus('prospect') }}</p>
              </div>
            </div>
          </div>

          <div class="bg-card p-6 rounded-lg border border-border">
            <div class="flex items-center">
              <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-md">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-foreground mb-2">Search</label>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search customers..."
                class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                @input="debounceSearch"
              />
            </div>

            <!-- Status Filter -->
            <div>
              <label class="block text-sm font-medium text-foreground mb-2">Status</label>
              <select
                v-model="filters.status"
                class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                @change="applyFilters"
              >
                <option value="">All Statuses</option>
                <option v-for="(label, value) in options.statuses" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Type Filter -->
            <div>
              <label class="block text-sm font-medium text-foreground mb-2">Type</label>
              <select
                v-model="filters.customer_type"
                class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                @change="applyFilters"
              >
                <option value="">All Types</option>
                <option v-for="(label, value) in options.types" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Industry Filter -->
            <div>
              <label class="block text-sm font-medium text-foreground mb-2">Industry</label>
              <select
                v-model="filters.industry"
                class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                @change="applyFilters"
              >
                <option value="">All Industries</option>
                <option v-for="(label, value) in options.industries" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>
          </div>

          <!-- Additional Filters Row -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
            <!-- Assigned To Filter -->
            <div>
              <label class="block text-sm font-medium text-foreground mb-2">Assigned To</label>
              <select
                v-model="filters.assigned_to"
                class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                @change="applyFilters"
              >
                <option value="">Unassigned</option>
                <option v-for="member in options.teamMembers" :key="member.id" :value="member.id">
                  {{ member.name }}
                </option>
              </select>
            </div>

            <!-- Active Status Filter -->
            <div>
              <label class="block text-sm font-medium text-foreground mb-2">Active Status</label>
              <select
                v-model="filters.is_active"
                class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                @change="applyFilters"
              >
                <option value="true">Active</option>
                <option value="false">Inactive</option>
              </select>
            </div>

            <!-- Clear Filters Button -->
            <div class="flex items-end">
              <button
                @click="clearFilters"
                class="w-full px-4 py-2 bg-secondary text-secondary-foreground border border-border rounded-md hover:bg-secondary/80 focus:outline-none focus:ring-2 focus:ring-secondary transition ease-in-out duration-150"
              >
                Clear Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Customers Table -->
        <div class="bg-card rounded-lg border border-border overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
              <thead class="bg-muted/50">
                <tr>
                  <th
                    @click="sortBy('name')"
                    class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider cursor-pointer hover:bg-muted/100"
                  >
                    <div class="flex items-center">
                      Customer
                      <svg v-if="filters.sort_field === 'name'" class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path v-if="filters.sort_direction === 'asc'" fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                        <path v-else fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    Contact
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    Assigned To
                  </th>
                  <th
                    @click="sortBy('created_at')"
                    class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider cursor-pointer hover:bg-muted/100"
                  >
                    <div class="flex items-center">
                      Created
                      <svg v-if="filters.sort_field === 'created_at'" class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path v-if="filters.sort_direction === 'asc'" fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                        <path v-else fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-card divide-y divide-border">
                <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-muted/50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                          <span class="text-sm font-medium text-primary">
                            {{ getInitials(customer.display_name) }}
                          </span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-foreground">
                          {{ customer.display_name }}
                        </div>
                        <div class="text-sm text-muted-foreground">
                          {{ customer.email || 'No email' }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                          {{ customer.industry || 'No industry' }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="customer.contacts && customer.contacts.length > 0">
                      <div class="text-sm text-foreground">
                        {{ customer.contacts[0].first_name }} {{ customer.contacts[0].last_name }}
                      </div>
                      <div class="text-sm text-muted-foreground">
                        {{ customer.contacts[0].email || customer.phone || 'No contact info' }}
                      </div>
                    </div>
                    <div v-else class="text-sm text-muted-foreground">
                      No contacts
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusClass(customer.status)" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                      {{ options.statuses[customer.status] || customer.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                    {{ customer.assigned_user ? customer.assigned_user.name : 'Unassigned' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                    {{ formatDate(customer.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Link
                      :href="route('customers.show', customer.id)"
                      class="text-primary hover:text-primary/80 mr-3"
                    >
                      View
                    </Link>
                    <Link
                      :href="route('customers.edit', customer.id)"
                      class="text-primary hover:text-primary/80"
                    >
                      Edit
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="customers.data && customers.data.length > 0" class="bg-card px-6 py-3 flex items-center justify-between border-t border-border">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="prevPage"
                :disabled="!customers.prev_page_url"
                class="relative inline-flex items-center px-4 py-2 border border-border text-sm font-medium rounded-md text-foreground bg-background hover:bg-muted"
                :class="{ 'opacity-50 cursor-not-allowed': !customers.prev_page_url }"
              >
                Previous
              </button>
              <button
                @click="nextPage"
                :disabled="!customers.next_page_url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-border text-sm font-medium rounded-md text-foreground bg-background hover:bg-muted"
                :class="{ 'opacity-50 cursor-not-allowed': !customers.next_page_url }"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-muted-foreground">
                  Showing
                  <span class="font-medium">{{ customers.from || 0 }}</span>
                  to
                  <span class="font-medium">{{ customers.to || 0 }}</span>
                  of
                  <span class="font-medium">{{ customers.total }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button
                    @click="prevPage"
                    :disabled="!customers.prev_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-border bg-background text-sm font-medium text-foreground hover:bg-muted"
                    :class="{ 'opacity-50 cursor-not-allowed': !customers.prev_page_url }"
                  >
                    <span class="sr-only">Previous</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <button
                    @click="nextPage"
                    :disabled="!customers.next_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-border bg-background text-sm font-medium text-foreground hover:bg-muted"
                    :class="{ 'opacity-50 cursor-not-allowed': !customers.next_page_url }"
                  >
                    <span class="sr-only">Next</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="customers.data && customers.data.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-foreground">No customers found</h3>
            <p class="mt-1 text-sm text-muted-foreground">
              {{ filters.search || hasActiveFilters() ? 'Try adjusting your search or filters' : 'Get started by creating a new customer' }}
            </p>
            <div class="mt-6">
              <Link
                :href="route('customers.create')"
                class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-primary/90 focus:outline-none focus:border-primary focus:ring-2 focus:ring-offset-2 focus:ring-primary transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Customer
              </Link>
            </div>
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

interface Customer {
  id: number
  display_name: string
  email: string | null
  phone: string | null
  industry: string | null
  status: string
  assigned_user: { name: string } | null
  contacts: Array<{
    first_name: string
    last_name: string
    email: string | null
  }>
  created_at: string
}

interface CustomersResponse {
  data: Customer[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
  prev_page_url: string | null
  next_page_url: string | null
}

interface Options {
  statuses: Record<string, string>
  types: Record<string, string>
  industries: Record<string, string>
  sources: Record<string, string>
  teamMembers: Array<{ id: number; name: string }>
}

const props = defineProps<{
  customers: CustomersResponse
  filters: {
    search: string
    status: string
    customer_type: string
    industry: string
    assigned_to: string
    is_active: string
    sort_field: string
    sort_direction: string
  }
  options: Options
}>()

const localFilters = reactive({ ...props.filters })
const currentPage = ref(props.customers.current_page)

// Debounced search function
const debounceSearch = debounce(() => {
  applyFilters()
}, 300)

function applyFilters() {
  const params = {
    ...localFilters,
    page: 1, // Reset to first page when filtering
  }

  router.get(route('customers.index'), params, {
    preserveState: true,
    preserveScroll: true,
  })
}

function clearFilters() {
  Object.assign(localFilters, {
    search: '',
    status: '',
    customer_type: '',
    industry: '',
    assigned_to: '',
    is_active: 'true',
    sort_field: 'created_at',
    sort_direction: 'desc',
  })
  applyFilters()
}

function sortBy(field: string) {
  if (localFilters.sort_field === field) {
    localFilters.sort_direction = localFilters.sort_direction === 'asc' ? 'desc' : 'asc'
  } else {
    localFilters.sort_field = field
    localFilters.sort_direction = 'asc'
  }
  applyFilters()
}

function prevPage() {
  if (props.customers.prev_page_url) {
    currentPage.value--
    goToPage(currentPage.value)
  }
}

function nextPage() {
  if (props.customers.next_page_url) {
    currentPage.value++
    goToPage(currentPage.value)
  }
}

function goToPage(page: number) {
  const params = {
    ...localFilters,
    page,
  }

  router.get(route('customers.index'), params, {
    preserveState: true,
    preserveScroll: true,
  })
}

function hasActiveFilters(): boolean {
  return !!(localFilters.search ||
           localFilters.status ||
           localFilters.customer_type ||
           localFilters.industry ||
           localFilters.assigned_to ||
           localFilters.is_active !== 'true')
}

function getInitials(name: string): string {
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .join('')
    .substring(0, 2)
}

function getStatusClass(status: string): string {
  const classes: Record<string, string> = {
    lead: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    prospect: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    churned: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
}

function formatDate(date: string): string {
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(new Date(date))
}

function getStatByStatus(status: string): number {
  return props.customers.data.filter(customer => customer.status === status).length
}

function getNewThisMonthCount(): number {
  const now = new Date()
  const currentMonth = now.getMonth()
  const currentYear = now.getFullYear()

  return props.customers.data.filter(customer => {
    const customerDate = new Date(customer.created_at)
    return customerDate.getMonth() === currentMonth &&
           customerDate.getFullYear() === currentYear
  }).length
}

// Watch for prop changes to update local state
watch(() => props.filters, (newFilters) => {
  Object.assign(localFilters, newFilters)
}, { deep: true })

watch(() => props.customers.current_page, (newPage) => {
  currentPage.value = newPage
})
</script>