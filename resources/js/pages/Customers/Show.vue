<template>
  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <Link
                :href="route('customers.index')"
                class="mr-4 text-muted-foreground hover:text-foreground transition-colors"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
              </Link>
              <div>
                <h1 class="text-2xl font-semibold text-foreground">{{ customer.display_name }}</h1>
                <div class="flex items-center mt-1 space-x-4">
                  <span :class="getStatusClass(customer.status)" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ options.statuses[customer.status] || customer.status }}
                  </span>
                  <span v-if="!customer.is_active" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                    Inactive
                  </span>
                  <span class="text-sm text-muted-foreground">
                    Created {{ formatDate(customer.created_at) }}
                  </span>
                </div>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <Link
                :href="route('customers.edit', customer.id)"
                class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-primary/90 focus:outline-none focus:border-primary focus:ring-2 focus:ring-offset-2 focus:ring-primary transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
              </Link>
              <button
                @click="showDeleteModal = true"
                class="inline-flex items-center px-4 py-2 bg-red-600 text-white border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete
              </button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Customer Information Card -->
            <div class="bg-card rounded-lg border border-border p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-medium text-foreground">Customer Information</h2>
                <div class="flex items-center">
                  <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                    <span class="text-lg font-medium text-primary">
                      {{ getInitials(customer.display_name) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h3 class="text-sm font-medium text-muted-foreground">Type</h3>
                  <p class="mt-1 text-sm text-foreground">{{ options.types[customer.customer_type] || customer.customer_type }}</p>
                </div>

                <div v-if="customer.industry">
                  <h3 class="text-sm font-medium text-muted-foreground">Industry</h3>
                  <p class="mt-1 text-sm text-foreground">{{ customer.industry }}</p>
                </div>

                <div v-if="customer.email">
                  <h3 class="text-sm font-medium text-muted-foreground">Email</h3>
                  <p class="mt-1 text-sm text-foreground">
                    <a :href="`mailto:${customer.email}`" class="text-primary hover:text-primary/80">
                      {{ customer.email }}
                    </a>
                  </p>
                </div>

                <div v-if="customer.phone">
                  <h3 class="text-sm font-medium text-muted-foreground">Phone</h3>
                  <p class="mt-1 text-sm text-foreground">
                    <a :href="`tel:${customer.phone}`" class="text-primary hover:text-primary/80">
                      {{ customer.phone }}
                    </a>
                  </p>
                </div>

                <div v-if="customer.website">
                  <h3 class="text-sm font-medium text-muted-foreground">Website</h3>
                  <p class="mt-1 text-sm text-foreground">
                    <a :href="customer.website" target="_blank" rel="noopener noreferrer" class="text-primary hover:text-primary/80">
                      {{ customer.website }}
                    </a>
                  </p>
                </div>

                <div v-if="customer.assigned_user">
                  <h3 class="text-sm font-medium text-muted-foreground">Assigned To</h3>
                  <p class="mt-1 text-sm text-foreground">{{ customer.assigned_user.name }}</p>
                </div>

                <div v-if="customer.source">
                  <h3 class="text-sm font-medium text-muted-foreground">Source</h3>
                  <p class="mt-1 text-sm text-foreground">{{ options.sources[customer.source] || customer.source }}</p>
                </div>

                <div>
                  <h3 class="text-sm font-medium text-muted-foreground">Created By</h3>
                  <p class="mt-1 text-sm text-foreground">{{ customer.creator?.name || 'Unknown' }}</p>
                </div>
              </div>

              <div v-if="customer.address || customer.city || customer.state || customer.country" class="mt-6 pt-6 border-t border-border">
                <h3 class="text-sm font-medium text-muted-foreground mb-3">Address</h3>
                <div class="text-sm text-foreground">
                  <div v-if="customer.address">{{ customer.address }}</div>
                  <div v-if="customer.city || customer.state || customer.postal_code">
                    {{ [customer.city, customer.state, customer.postal_code].filter(Boolean).join(', ') }}
                  </div>
                  <div v-if="customer.country">{{ customer.country }}</div>
                </div>
              </div>

              <div v-if="customer.notes" class="mt-6 pt-6 border-t border-border">
                <h3 class="text-sm font-medium text-muted-foreground mb-3">Notes</h3>
                <p class="text-sm text-foreground whitespace-pre-wrap">{{ customer.notes }}</p>
              </div>
            </div>

            <!-- Contacts Section -->
            <div id="contacts" class="bg-card rounded-lg border border-border p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-medium text-foreground">Contacts ({{ customer.contacts?.length || 0 }})</h2>
                <button
                  @click="showAddContactModal = true"
                  class="inline-flex items-center px-3 py-1.5 bg-primary text-primary-foreground border border-transparent rounded-md text-xs font-medium hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition ease-in-out duration-150"
                >
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Add Contact
                </button>
              </div>

              <div v-if="customer.contacts && customer.contacts.length > 0" class="space-y-4">
                <div v-for="contact in customer.contacts" :key="contact.id" class="p-4 bg-muted/50 rounded-lg">
                  <div class="flex items-start justify-between">
                    <div class="flex items-start">
                      <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mr-3">
                        <span class="text-sm font-medium text-primary">
                          {{ getContactInitials(contact.first_name, contact.last_name) }}
                        </span>
                      </div>
                      <div>
                        <div class="flex items-center">
                          <h3 class="text-sm font-medium text-foreground">
                            {{ contact.first_name }} {{ contact.last_name }}
                          </h3>
                          <span v-if="contact.is_primary" class="ml-2 px-2 py-1 text-xs bg-primary/10 text-primary rounded-full">Primary</span>
                          <span v-if="!contact.is_active" class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full">Inactive</span>
                        </div>
                        <p v-if="contact.job_title" class="text-sm text-muted-foreground">{{ contact.job_title }}</p>
                        <div class="mt-2 space-y-1">
                          <p v-if="contact.email" class="text-sm text-foreground">
                            <a :href="`mailto:${contact.email}`" class="text-primary hover:text-primary/80">
                              {{ contact.email }}
                            </a>
                          </p>
                          <p v-if="contact.phone" class="text-sm text-foreground">
                            <a :href="`tel:${contact.phone}`" class="text-primary hover:text-primary/80">
                              {{ contact.phone }}
                            </a>
                          </p>
                          <p v-if="contact.mobile" class="text-sm text-foreground">
                            <a :href="`tel:${contact.mobile}`" class="text-primary hover:text-primary/80">
                              {{ contact.mobile }}
                            </a>
                          </p>
                          <p v-if="contact.department" class="text-sm text-muted-foreground">Department: {{ contact.department }}</p>
                        </div>
                        <p v-if="contact.notes" class="mt-2 text-sm text-muted-foreground">{{ contact.notes }}</p>
                      </div>
                    </div>
                    <div class="flex items-center space-x-2">
                      <button
                        @click="makePrimaryContact(contact)"
                        v-if="!contact.is_primary && contact.is_active"
                        class="text-xs text-primary hover:text-primary/80"
                      >
                        Make Primary
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-foreground">No contacts</h3>
                <p class="mt-1 text-sm text-muted-foreground">
                  Get started by adding a contact to this customer.
                </p>
                <button
                  @click="showAddContactModal = true"
                  class="mt-4 inline-flex items-center px-4 py-2 bg-primary text-primary-foreground border border-transparent rounded-md text-sm font-medium hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                  Add First Contact
                </button>
              </div>
            </div>

            <!-- Activity Timeline -->
            <div class="bg-card rounded-lg border border-border p-6">
              <h2 class="text-lg font-medium text-foreground mb-6">Recent Activity</h2>
              <div v-if="customer.activities && customer.activities.length > 0" class="space-y-4">
                <div v-for="activity in customer.activities" :key="activity.id" class="flex space-x-3">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                      <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm text-foreground">{{ activity.description }}</p>
                    <p class="text-xs text-muted-foreground mt-1">
                      {{ activity.user?.name }} • {{ formatDate(activity.created_at) }}
                    </p>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8">
                <p class="text-sm text-muted-foreground">No recent activity</p>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-card rounded-lg border border-border p-6">
              <h3 class="text-lg font-medium text-foreground mb-4">Quick Actions</h3>
              <div class="space-y-3">
                <button class="w-full text-left px-4 py-3 bg-muted/50 rounded-lg hover:bg-muted transition-colors">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <div>
                      <p class="text-sm font-medium text-foreground">Create Quote</p>
                      <p class="text-xs text-muted-foreground">Generate a new quote</p>
                    </div>
                  </div>
                </button>

                <button class="w-full text-left px-4 py-3 bg-muted/50 rounded-lg hover:bg-muted transition-colors">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <div>
                      <p class="text-sm font-medium text-foreground">Create Invoice</p>
                      <p class="text-xs text-muted-foreground">Generate an invoice</p>
                    </div>
                  </div>
                </button>

                <button class="w-full text-left px-4 py-3 bg-muted/50 rounded-lg hover:bg-muted transition-colors">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <div>
                      <p class="text-sm font-medium text-foreground">Schedule Meeting</p>
                      <p class="text-xs text-muted-foreground">Book a meeting</p>
                    </div>
                  </div>
                </button>
              </div>
            </div>

            <!-- Recent Quotes -->
            <div class="bg-card rounded-lg border border-border p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-foreground">Recent Quotes</h3>
                <Link href="#" class="text-sm text-primary hover:text-primary/80">View All</Link>
              </div>
              <div v-if="customer.quotes && customer.quotes.length > 0" class="space-y-3">
                <div v-for="quote in customer.quotes" :key="quote.id" class="p-3 bg-muted/50 rounded-lg">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-sm font-medium text-foreground">{{ quote.quote_number }}</p>
                      <p class="text-xs text-muted-foreground">{{ formatDate(quote.created_at) }}</p>
                    </div>
                    <p class="text-sm font-medium text-foreground">{{ formatCurrency(quote.total) }}</p>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4">
                <p class="text-sm text-muted-foreground">No quotes yet</p>
              </div>
            </div>

            <!-- Recent Invoices -->
            <div class="bg-card rounded-lg border border-border p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-foreground">Recent Invoices</h3>
                <Link href="#" class="text-sm text-primary hover:text-primary/80">View All</Link>
              </div>
              <div v-if="customer.invoices && customer.invoices.length > 0" class="space-y-3">
                <div v-for="invoice in customer.invoices" :key="invoice.id" class="p-3 bg-muted/50 rounded-lg">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-sm font-medium text-foreground">{{ invoice.invoice_number }}</p>
                      <p class="text-xs text-muted-foreground">{{ formatDate(invoice.created_at) }}</p>
                    </div>
                    <p class="text-sm font-medium text-foreground">{{ formatCurrency(invoice.total) }}</p>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4">
                <p class="text-sm text-muted-foreground">No invoices yet</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block align-bottom bg-card rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-card px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-foreground" id="modal-title">
                  Delete Customer
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-muted-foreground">
                    Are you sure you want to delete this customer? This action will mark the customer as inactive and can be reversed. All associated data (quotes, invoices, etc.) will remain in the system.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-card px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="deleteCustomer"
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Delete Customer
            </button>
            <button
              @click="showDeleteModal = false"
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-border shadow-sm px-4 py-2 bg-card text-base font-medium text-foreground hover:bg-muted focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Contact Modal -->
    <div v-if="showAddContactModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block align-bottom bg-card rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-card px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <h3 class="text-lg leading-6 font-medium text-foreground mb-4">
              Add New Contact
            </h3>
            <!-- Add contact form would go here -->
            <p class="text-sm text-muted-foreground">Contact form will be implemented here</p>
          </div>
          <div class="bg-card px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="showAddContactModal = false"
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

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
  notes: string | null
}

interface Activity {
  id: number
  description: string
  user: { name: string } | null
  created_at: string
}

interface Quote {
  id: number
  quote_number: string
  total: number
  created_at: string
}

interface Invoice {
  id: number
  invoice_number: string
  total: number
  created_at: string
}

interface Customer {
  id: number
  display_name: string
  customer_type: string
  email: string | null
  phone: string | null
  website: string | null
  address: string | null
  city: string | null
  state: string | null
  postal_code: string | null
  country: string | null
  industry: string | null
  status: string
  source: string | null
  is_active: boolean
  created_at: string
  notes: string | null
  assigned_user: { name: string } | null
  creator: { name: string } | null
  contacts: Contact[]
  activities: Activity[]
  quotes: Quote[]
  invoices: Invoice[]
}

interface Options {
  statuses: Record<string, string>
  types: Record<string, string>
  sources: Record<string, string>
}

const props = defineProps<{
  customer: Customer
  options: Options
}>()

const showDeleteModal = ref(false)
const showAddContactModal = ref(false)

function formatDate(date: string): string {
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(new Date(date))
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

function getInitials(name: string): string {
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .join('')
    .substring(0, 2)
}

function getContactInitials(firstName: string, lastName: string): string {
  return `${firstName.charAt(0).toUpperCase()}${lastName.charAt(0).toUpperCase()}`
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

function deleteCustomer() {
  router.delete(route('customers.destroy', props.customer.id))
}

function makePrimaryContact(contact: Contact) {
  // This would make an API call to set the contact as primary
  router.post(`/contacts/${contact.id}/make-primary`)
}
</script>