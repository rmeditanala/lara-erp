<template>
  <AppLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
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
              <h1 class="text-2xl font-semibold text-foreground">Edit Customer</h1>
              <p class="mt-1 text-sm text-muted-foreground">
                Update customer information and contact details
              </p>
            </div>
          </div>
        </div>

        <form @submit.prevent="submit">
          <!-- Customer Type Selection -->
          <div class="bg-card rounded-lg border border-border p-6 mb-6">
            <h2 class="text-lg font-medium text-foreground mb-4">Customer Type</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label class="relative flex cursor-pointer rounded-lg border border-border p-4 hover:border-primary/50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <input
                  type="radio"
                  v-model="form.customer_type"
                  value="individual"
                  class="sr-only"
                  @change="onCustomerTypeChange"
                />
                <div class="flex w-full items-center justify-between">
                  <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full border-2 border-primary flex items-center justify-center mr-3">
                      <div v-if="form.customer_type === 'individual'" class="w-2 h-2 rounded-full bg-primary"></div>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-foreground">Individual</p>
                      <p class="text-sm text-muted-foreground">Person or sole proprietor</p>
                    </div>
                  </div>
                  <svg class="w-8 h-8 text-primary/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
              </label>

              <label class="relative flex cursor-pointer rounded-lg border border-border p-4 hover:border-primary/50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <input
                  type="radio"
                  v-model="form.customer_type"
                  value="company"
                  class="sr-only"
                  @change="onCustomerTypeChange"
                />
                <div class="flex w-full items-center justify-between">
                  <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full border-2 border-primary flex items-center justify-center mr-3">
                      <div v-if="form.customer_type === 'company'" class="w-2 h-2 rounded-full bg-primary"></div>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-foreground">Company</p>
                      <p class="text-sm text-muted-foreground">Business or organization</p>
                    </div>
                  </div>
                  <svg class="w-8 h-8 text-primary/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
              </label>
            </div>
          </div>

          <!-- Basic Information -->
          <div class="bg-card rounded-lg border border-border p-6 mb-6">
            <h2 class="text-lg font-medium text-foreground mb-4">Basic Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="sm:col-span-2" v-if="form.customer_type === 'company'">
                <Label for="company_name" class="block text-sm font-medium text-foreground mb-2">
                  Company Name *
                </Label>
                <Input
                  id="company_name"
                  v-model="form.company_name"
                  type="text"
                  required
                  :disabled="form.processing"
                  placeholder="Acme Corporation"
                  class="w-full"
                />
                <InputError :message="form.errors.company_name" class="mt-1" />
              </div>

              <div class="sm:col-span-2" v-if="form.customer_type === 'individual'">
                <Label for="name" class="block text-sm font-medium text-foreground mb-2">
                  Full Name *
                </Label>
                <Input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  :disabled="form.processing"
                  placeholder="John Doe"
                  class="w-full"
                />
                <InputError :message="form.errors.name" class="mt-1" />
              </div>

              <div class="sm:col-span-2">
                <Label for="email" class="block text-sm font-medium text-foreground mb-2">
                  Email Address
                </Label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  :disabled="form.processing"
                  placeholder="customer@example.com"
                  class="w-full"
                />
                <InputError :message="form.errors.email" class="mt-1" />
              </div>

              <div>
                <Label for="phone" class="block text-sm font-medium text-foreground mb-2">
                  Phone Number
                </Label>
                <Input
                  id="phone"
                  v-model="form.phone"
                  type="tel"
                  :disabled="form.processing"
                  placeholder="+1 (555) 123-4567"
                  class="w-full"
                />
                <InputError :message="form.errors.phone" class="mt-1" />
              </div>

              <div>
                <Label for="website" class="block text-sm font-medium text-foreground mb-2">
                  Website
                </Label>
                <Input
                  id="website"
                  v-model="form.website"
                  type="url"
                  :disabled="form.processing"
                  placeholder="https://example.com"
                  class="w-full"
                />
                <InputError :message="form.errors.website" class="mt-1" />
              </div>

              <div>
                <Label for="status" class="block text-sm font-medium text-foreground mb-2">
                  Status *
                </Label>
                <select
                  id="status"
                  v-model="form.status"
                  required
                  :disabled="form.processing"
                  class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                >
                  <option value="">Select Status</option>
                  <option v-for="(label, value) in options.statuses" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
                <InputError :message="form.errors.status" class="mt-1" />
              </div>

              <div>
                <Label for="source" class="block text-sm font-medium text-foreground mb-2">
                  Source
                </Label>
                <select
                  id="source"
                  v-model="form.source"
                  :disabled="form.processing"
                  class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                >
                  <option value="">Select Source</option>
                  <option v-for="(label, value) in options.sources" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
                <InputError :message="form.errors.source" class="mt-1" />
              </div>

              <div v-if="form.customer_type === 'company'">
                <Label for="industry" class="block text-sm font-medium text-foreground mb-2">
                  Industry
                </Label>
                <select
                  id="industry"
                  v-model="form.industry"
                  :disabled="form.processing"
                  class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                >
                  <option value="">Select Industry</option>
                  <option v-for="(label, value) in options.industries" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
                <InputError :message="form.errors.industry" class="mt-1" />
              </div>

              <div>
                <Label for="assigned_to" class="block text-sm font-medium text-foreground mb-2">
                  Assigned To
                </Label>
                <select
                  id="assigned_to"
                  v-model="form.assigned_to"
                  :disabled="form.processing"
                  class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                >
                  <option value="">Unassigned</option>
                  <option v-for="member in options.teamMembers" :key="member.id" :value="member.id">
                    {{ member.name }}
                  </option>
                </select>
                <InputError :message="form.errors.assigned_to" class="mt-1" />
              </div>

              <div>
                <Label class="block text-sm font-medium text-foreground mb-2">
                  Active Status
                </Label>
                <div class="flex items-center space-x-4">
                  <label class="flex items-center">
                    <input
                      type="radio"
                      v-model="form.is_active"
                      :value="true"
                      class="mr-2"
                      :disabled="form.processing"
                    />
                    <span class="text-sm text-foreground">Active</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      type="radio"
                      v-model="form.is_active"
                      :value="false"
                      class="mr-2"
                      :disabled="form.processing"
                    />
                    <span class="text-sm text-foreground">Inactive</span>
                  </label>
                </div>
                <InputError :message="form.errors.is_active" class="mt-1" />
              </div>
            </div>
          </div>

          <!-- Address Information -->
          <div class="bg-card rounded-lg border border-border p-6 mb-6">
            <h2 class="text-lg font-medium text-foreground mb-4">Address Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="sm:col-span-2">
                <Label for="address" class="block text-sm font-medium text-foreground mb-2">
                  Street Address
                </Label>
                <textarea
                  id="address"
                  v-model="form.address"
                  :disabled="form.processing"
                  rows="2"
                  placeholder="123 Business St, Suite 100"
                  class="mt-1 flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                ></textarea>
                <InputError :message="form.errors.address" class="mt-1" />
              </div>

              <div>
                <Label for="city" class="block text-sm font-medium text-foreground mb-2">
                  City
                </Label>
                <Input
                  id="city"
                  v-model="form.city"
                  type="text"
                  :disabled="form.processing"
                  placeholder="New York"
                  class="w-full"
                />
                <InputError :message="form.errors.city" class="mt-1" />
              </div>

              <div>
                <Label for="state" class="block text-sm font-medium text-foreground mb-2">
                  State/Province
                </Label>
                <Input
                  id="state"
                  v-model="form.state"
                  type="text"
                  :disabled="form.processing"
                  placeholder="NY"
                  class="w-full"
                />
                <InputError :message="form.errors.state" class="mt-1" />
              </div>

              <div>
                <Label for="postal_code" class="block text-sm font-medium text-foreground mb-2">
                  Postal Code
                </Label>
                <Input
                  id="postal_code"
                  v-model="form.postal_code"
                  type="text"
                  :disabled="form.processing"
                  placeholder="10001"
                  class="w-full"
                />
                <InputError :message="form.errors.postal_code" class="mt-1" />
              </div>

              <div>
                <Label for="country" class="block text-sm font-medium text-foreground mb-2">
                  Country
                </Label>
                <Input
                  id="country"
                  v-model="form.country"
                  type="text"
                  :disabled="form.processing"
                  placeholder="United States"
                  class="w-full"
                />
                <InputError :message="form.errors.country" class="mt-1" />
              </div>
            </div>
          </div>

          <!-- Business Details (Company Only) -->
          <div v-if="form.customer_type === 'company'" class="bg-card rounded-lg border border-border p-6 mb-6">
            <h2 class="text-lg font-medium text-foreground mb-4">Business Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label for="tax_number" class="block text-sm font-medium text-foreground mb-2">
                  Tax Number
                </Label>
                <Input
                  id="tax_number"
                  v-model="form.tax_number"
                  type="text"
                  :disabled="form.processing"
                  placeholder="Tax ID or VAT number"
                  class="w-full"
                />
                <InputError :message="form.errors.tax_number" class="mt-1" />
              </div>

              <div>
                <Label for="registration_number" class="block text-sm font-medium text-foreground mb-2">
                  Registration Number
                </Label>
                <Input
                  id="registration_number"
                  v-model="form.registration_number"
                  type="text"
                  :disabled="form.processing"
                  placeholder="Business registration number"
                  class="w-full"
                />
                <InputError :message="form.errors.registration_number" class="mt-1" />
              </div>

              <div>
                <Label for="employees_count" class="block text-sm font-medium text-foreground mb-2">
                  Number of Employees
                </Label>
                <Input
                  id="employees_count"
                  v-model.number="form.employees_count"
                  type="number"
                  min="0"
                  :disabled="form.processing"
                  placeholder="50"
                  class="w-full"
                />
                <InputError :message="form.errors.employees_count" class="mt-1" />
              </div>

              <div>
                <Label for="annual_revenue" class="block text-sm font-medium text-foreground mb-2">
                  Annual Revenue ($)
                </Label>
                <Input
                  id="annual_revenue"
                  v-model.number="form.annual_revenue"
                  type="number"
                  min="0"
                  step="0.01"
                  :disabled="form.processing"
                  placeholder="1000000"
                  class="w-full"
                />
                <InputError :message="form.errors.annual_revenue" class="mt-1" />
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="bg-card rounded-lg border border-border p-6 mb-6">
            <h2 class="text-lg font-medium text-foreground mb-4">Additional Notes</h2>
            <textarea
              v-model="form.notes"
              :disabled="form.processing"
              rows="4"
              placeholder="Add any additional notes about this customer..."
              class="mt-1 flex min-h-[100px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            ></textarea>
            <InputError :message="form.errors.notes" class="mt-1" />
          </div>

          <!-- Contact Management -->
          <div class="bg-card rounded-lg border border-border p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-medium text-foreground">Contact Management</h2>
              <Link
                :href="route('customers.show', customer.id) + '#contacts'"
                class="text-sm text-primary hover:text-primary/80"
              >
                Manage Contacts →
              </Link>
            </div>
            <div v-if="customer.contacts && customer.contacts.length > 0" class="space-y-3">
              <div v-for="contact in customer.contacts" :key="contact.id" class="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center mr-3">
                    <span class="text-xs font-medium text-primary">
                      {{ getContactInitials(contact.first_name, contact.last_name) }}
                    </span>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-foreground">
                      {{ contact.first_name }} {{ contact.last_name }}
                      <span v-if="contact.is_primary" class="ml-2 px-2 py-1 text-xs bg-primary/10 text-primary rounded-full">Primary</span>
                    </p>
                    <p class="text-sm text-muted-foreground">
                      {{ contact.email || contact.phone || contact.mobile || 'No contact info' }}
                    </p>
                  </div>
                </div>
                <Link
                  :href="route('customers.show', customer.id) + '#contacts'"
                  class="text-sm text-muted-foreground hover:text-foreground"
                >
                  Edit
                </Link>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <p class="text-sm text-muted-foreground">No contacts added yet</p>
              <Link
                :href="route('customers.show', customer.id) + '#contacts'"
                class="mt-2 inline-flex items-center text-sm text-primary hover:text-primary/80"
              >
                Add first contact →
              </Link>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <button
                type="button"
                @click="confirmDelete"
                class="text-sm text-red-600 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                :disabled="form.processing"
              >
                Delete Customer
              </button>
            </div>
            <div class="flex items-center space-x-4">
              <Link
                :href="route('customers.show', customer.id)"
                class="px-4 py-2 text-sm font-medium text-foreground bg-card border border-border rounded-md hover:bg-muted focus:outline-none focus:ring-2 focus:ring-primary transition ease-in-out duration-150"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-6 py-2 bg-primary text-primary-foreground border border-transparent rounded-md font-semibold text-sm uppercase tracking-widest hover:bg-primary/90 focus:outline-none focus:border-primary focus:ring-2 focus:ring-offset-2 focus:ring-primary transition ease-in-out duration-150 disabled:opacity-50"
              >
                <span v-if="form.processing" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Updating...
                </span>
                <span v-else>Update Customer</span>
              </button>
            </div>
          </div>
        </form>
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
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { InputError } from '@/components/ui/input-error'

interface Contact {
  id: number
  first_name: string
  last_name: string
  email: string | null
  phone: string | null
  mobile: string | null
  is_primary: boolean
}

interface Customer {
  id: number
  customer_type: string
  name: string | null
  company_name: string | null
  email: string | null
  phone: string | null
  website: string | null
  address: string | null
  city: string | null
  state: string | null
  postal_code: string | null
  country: string | null
  industry: string | null
  tax_number: string | null
  registration_number: string | null
  employees_count: number | null
  annual_revenue: number | null
  status: string
  source: string | null
  assigned_to: number | null
  is_active: boolean
  notes: string | null
  contacts: Contact[]
}

interface Options {
  statuses: Record<string, string>
  types: Record<string, string>
  industries: Record<string, string>
  sources: Record<string, string>
  teamMembers: Array<{ id: number; name: string }>
}

const props = defineProps<{
  customer: Customer
  options: Options
}>()

const showDeleteModal = ref(false)

const form = useForm({
  customer_type: props.customer.customer_type,
  name: props.customer.name || '',
  company_name: props.customer.company_name || '',
  email: props.customer.email || '',
  phone: props.customer.phone || '',
  website: props.customer.website || '',
  address: props.customer.address || '',
  city: props.customer.city || '',
  state: props.customer.state || '',
  postal_code: props.customer.postal_code || '',
  country: props.customer.country || '',
  industry: props.customer.industry || '',
  tax_number: props.customer.tax_number || '',
  registration_number: props.customer.registration_number || '',
  employees_count: props.customer.employees_count,
  annual_revenue: props.customer.annual_revenue,
  status: props.customer.status,
  source: props.customer.source || '',
  assigned_to: props.customer.assigned_to || '',
  is_active: props.customer.is_active,
  notes: props.customer.notes || '',
})

function onCustomerTypeChange() {
  // Clear validation errors when customer type changes
  form.clearErrors('name', 'company_name', 'industry')
}

function submit() {
  form.put(route('customers.update', props.customer.id), {
    preserveScroll: true,
  })
}

function confirmDelete() {
  showDeleteModal.value = true
}

function deleteCustomer() {
  router.delete(route('customers.destroy', props.customer.id))
}

function getContactInitials(firstName: string, lastName: string): string {
  return `${firstName.charAt(0).toUpperCase()}${lastName.charAt(0).toUpperCase()}`
}
</script>