<template>
  <AppLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
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
              <h1 class="text-2xl font-semibold text-foreground">Add New Customer</h1>
              <p class="mt-1 text-sm text-muted-foreground">
                Create a new customer profile and add primary contact information
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

          <!-- Primary Contact -->
          <div class="bg-card rounded-lg border border-border p-6 mb-6">
            <h2 class="text-lg font-medium text-foreground mb-4">Primary Contact Information</h2>
            <p class="text-sm text-muted-foreground mb-4">
              Add the primary contact person for this customer. This information will be used for communications.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label for="contact_first_name" class="block text-sm font-medium text-foreground mb-2">
                  First Name *
                </Label>
                <Input
                  id="contact_first_name"
                  v-model="form.contact.first_name"
                  type="text"
                  required
                  :disabled="form.processing"
                  placeholder="John"
                  class="w-full"
                />
                <InputError :message="form.errors['contact.first_name']" class="mt-1" />
              </div>

              <div>
                <Label for="contact_last_name" class="block text-sm font-medium text-foreground mb-2">
                  Last Name *
                </Label>
                <Input
                  id="contact_last_name"
                  v-model="form.contact.last_name"
                  type="text"
                  required
                  :disabled="form.processing"
                  placeholder="Doe"
                  class="w-full"
                />
                <InputError :message="form.errors['contact.last_name']" class="mt-1" />
              </div>

              <div>
                <Label for="contact_email" class="block text-sm font-medium text-foreground mb-2">
                  Contact Email
                </Label>
                <Input
                  id="contact_email"
                  v-model="form.contact.email"
                  type="email"
                  :disabled="form.processing"
                  placeholder="john.doe@company.com"
                  class="w-full"
                />
                <InputError :message="form.errors['contact.email']" class="mt-1" />
              </div>

              <div>
                <Label for="contact_phone" class="block text-sm font-medium text-foreground mb-2">
                  Contact Phone
                </Label>
                <Input
                  id="contact_phone"
                  v-model="form.contact.phone"
                  type="tel"
                  :disabled="form.processing"
                  placeholder="+1 (555) 123-4567"
                  class="w-full"
                />
                <InputError :message="form.errors['contact.phone']" class="mt-1" />
              </div>

              <div>
                <Label for="contact_mobile" class="block text-sm font-medium text-foreground mb-2">
                  Mobile Phone
                </Label>
                <Input
                  id="contact_mobile"
                  v-model="form.contact.mobile"
                  type="tel"
                  :disabled="form.processing"
                  placeholder="+1 (555) 987-6543"
                  class="w-full"
                />
                <InputError :message="form.errors['contact.mobile']" class="mt-1" />
              </div>

              <div>
                <Label for="contact_job_title" class="block text-sm font-medium text-foreground mb-2">
                  Job Title
                </Label>
                <Input
                  id="contact_job_title"
                  v-model="form.contact.job_title"
                  type="text"
                  :disabled="form.processing"
                  placeholder="CEO, Manager, etc."
                  class="w-full"
                />
                <InputError :message="form.errors['contact.job_title']" class="mt-1" />
              </div>

              <div>
                <Label for="contact_department" class="block text-sm font-medium text-foreground mb-2">
                  Department
                </Label>
                <Input
                  id="contact_department"
                  v-model="form.contact.department"
                  type="text"
                  :disabled="form.processing"
                  placeholder="Sales, Marketing, etc."
                  class="w-full"
                />
                <InputError :message="form.errors['contact.department']" class="mt-1" />
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

          <!-- Form Actions -->
          <div class="flex items-center justify-end space-x-4">
            <Link
              :href="route('customers.index')"
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
                Creating...
              </span>
              <span v-else>Create Customer</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { InputError } from '@/components/ui/input-error'

interface Options {
  statuses: Record<string, string>
  types: Record<string, string>
  industries: Record<string, string>
  sources: Record<string, string>
  teamMembers: Array<{ id: number; name: string }>
}

const props = defineProps<{
  options: Options
}>()

const form = useForm({
  customer_type: 'individual',
  name: '',
  company_name: '',
  email: '',
  phone: '',
  website: '',
  address: '',
  city: '',
  state: '',
  postal_code: '',
  country: '',
  industry: '',
  tax_number: '',
  registration_number: '',
  employees_count: null,
  annual_revenue: null,
  status: '',
  source: '',
  assigned_to: '',
  notes: '',
  contact: {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    mobile: '',
    job_title: '',
    department: '',
  },
})

function onCustomerTypeChange() {
  // Clear validation errors when customer type changes
  form.clearErrors('name', 'company_name', 'industry')
}

function submit() {
  form.post(route('customers.store'), {
    onSuccess: () => {
      form.reset('password', 'password_confirmation')
    },
  })
}
</script>