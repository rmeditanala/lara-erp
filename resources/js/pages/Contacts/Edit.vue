<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import { InputError } from '@/components/ui/input-error'
import {
  Phone,
  Mail,
  User,
  Briefcase,
  Building,
  CheckCircle,
  ArrowLeft,
  Save,
  X,
  Star,
  Calendar
} from 'lucide-vue-next'

interface Customer {
  id: number
  display_name: string
  customer_type: string
}

interface Contact {
  id: number
  customer_id: number
  first_name: string
  last_name: string
  email: string
  phone: string
  mobile: string
  job_title: string
  department: string
  is_primary: boolean
  is_active: boolean
  notes: string
  created_at: string
  updated_at: string
}

interface Props {
  customer: Customer
  contact: Contact
}

const props = defineProps<Props>()

const title = computed(() => `Edit Contact: ${props.contact.first_name} ${props.contact.last_name}`)

const form = useForm({
  first_name: props.contact.first_name,
  last_name: props.contact.last_name,
  email: props.contact.email,
  phone: props.contact.phone,
  mobile: props.contact.mobile,
  job_title: props.contact.job_title,
  department: props.contact.department,
  is_primary: props.contact.is_primary,
  is_active: props.contact.is_active,
  notes: props.contact.notes,
})

const departments = [
  'Executive',
  'Sales',
  'Marketing',
  'Finance',
  'Human Resources',
  'IT',
  'Operations',
  'Customer Service',
  'Research & Development',
  'Legal',
  'Administration',
  'Other'
]

const isCurrentlyPrimary = computed(() => props.contact.is_primary)
const isActive = computed(() => props.contact.is_active)

const submit = () => {
  form.put(route('contacts.update', [props.customer.id, props.contact.id]), {
    onSuccess: () => {
      router.visit(route('customers.show', props.customer.id))
    },
  })
}

const cancel = () => {
  router.visit(route('customers.show', props.customer.id))
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>
  <Head :title="title" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="space-y-2">
        <div class="flex items-center space-x-2">
          <Button
            variant="ghost"
            size="sm"
            @click="cancel"
            class="text-muted-foreground hover:text-foreground"
          >
            <ArrowLeft class="h-4 w-4 mr-1" />
            Back
          </Button>
        </div>

        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbLink :href="route('dashboard')">
                Dashboard
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbLink :href="route('customers.index')">
                Customers
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbLink :href="route('customers.show', customer.id)">
                {{ customer.display_name }}
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbLink :href="route('contacts.show', [customer.id, contact.id])">
                {{ contact.first_name }} {{ contact.last_name }}
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbPage>
              Edit Contact
            </BreadcrumbPage>
          </BreadcrumbList>
        </Breadcrumb>

        <div class="flex items-start justify-between">
          <div>
            <h1 class="text-3xl font-bold tracking-tight">{{ title }}</h1>
            <p class="text-muted-foreground">
              Update contact information for {{ contact.first_name }} {{ contact.last_name }}
            </p>
          </div>

          <div class="flex items-center space-x-2">
            <div v-if="isCurrentlyPrimary" class="flex items-center text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-3 py-1 rounded-full text-sm font-medium">
              <Star class="h-4 w-4 mr-1 fill-current" />
              Primary Contact
            </div>
            <div v-if="isActive" class="flex items-center text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-3 py-1 rounded-full text-sm font-medium">
              <CheckCircle class="h-4 w-4 mr-1" />
              Active
            </div>
            <div v-else class="flex items-center text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/20 px-3 py-1 rounded-full text-sm font-medium">
              <X class="h-4 w-4 mr-1" />
              Inactive
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Summary -->
      <Card class="border-blue-200 dark:border-blue-800">
        <CardHeader>
          <CardTitle class="text-blue-700 dark:text-blue-300">Contact Summary</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
              <span class="font-medium text-muted-foreground">Customer:</span>
              <div class="font-medium">{{ customer.display_name }}</div>
            </div>
            <div>
              <span class="font-medium text-muted-foreground">Created:</span>
              <div class="flex items-center">
                <Calendar class="h-4 w-4 mr-1 text-muted-foreground" />
                {{ formatDate(contact.created_at) }}
              </div>
            </div>
            <div>
              <span class="font-medium text-muted-foreground">Last Updated:</span>
              <div class="flex items-center">
                <Calendar class="h-4 w-4 mr-1 text-muted-foreground" />
                {{ formatDate(contact.updated_at) }}
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-8">
        <!-- Basic Information -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <User class="h-5 w-5 mr-2" />
              Basic Information
            </CardTitle>
            <CardDescription>
              Contact person's basic details
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Name Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <Label for="first_name">First Name *</Label>
                <Input
                  id="first_name"
                  v-model="form.first_name"
                  placeholder="John"
                  required
                  :disabled="form.processing"
                />
                <InputError :message="form.errors.first_name" />
              </div>

              <div class="space-y-2">
                <Label for="last_name">Last Name *</Label>
                <Input
                  id="last_name"
                  v-model="form.last_name"
                  placeholder="Doe"
                  required
                  :disabled="form.processing"
                />
                <InputError :message="form.errors.last_name" />
              </div>
            </div>

            <!-- Contact Information Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <Label for="email" class="flex items-center">
                  <Mail class="h-4 w-4 mr-1" />
                  Email Address
                </Label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="john.doe@company.com"
                  :disabled="form.processing"
                />
                <InputError :message="form.errors.email" />
              </div>

              <div class="space-y-2">
                <Label for="phone" class="flex items-center">
                  <Phone class="h-4 w-4 mr-1" />
                  Phone Number
                </Label>
                <Input
                  id="phone"
                  v-model="form.phone"
                  placeholder="+1 (555) 123-4567"
                  :disabled="form.processing"
                />
                <InputError :message="form.errors.phone" />
              </div>
            </div>

            <!-- Mobile Number -->
            <div class="space-y-2">
              <Label for="mobile" class="flex items-center">
                <Phone class="h-4 w-4 mr-1" />
                Mobile Number
              </Label>
              <Input
                id="mobile"
                v-model="form.mobile"
                placeholder="+1 (555) 987-6543"
                :disabled="form.processing"
              />
              <InputError :message="form.errors.mobile" />
            </div>

            <!-- Job Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <Label for="job_title" class="flex items-center">
                  <Briefcase class="h-4 w-4 mr-1" />
                  Job Title
                </Label>
                <Input
                  id="job_title"
                  v-model="form.job_title"
                  placeholder="Sales Manager"
                  :disabled="form.processing"
                />
                <InputError :message="form.errors.job_title" />
              </div>

              <div class="space-y-2">
                <Label for="department">Department</Label>
                <Select v-model="form.department">
                  <SelectTrigger :disabled="form.processing">
                    <SelectValue placeholder="Select department" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">No department</SelectItem>
                    <SelectItem v-for="dept in departments" :key="dept" :value="dept">
                      {{ dept }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.department" />
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Contact Settings -->
        <Card>
          <CardHeader>
            <CardTitle>Contact Settings</CardTitle>
            <CardDescription>
              Configure contact status and preferences
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Checkboxes -->
            <div class="flex flex-col sm:flex-row gap-6">
              <div class="flex items-center space-x-2">
                <Checkbox
                  id="is_primary"
                  v-model:checked="form.is_primary"
                  :disabled="form.processing"
                />
                <Label for="is_primary" class="text-sm font-medium cursor-pointer">
                  Set as Primary Contact
                </Label>
              </div>

              <div class="flex items-center space-x-2">
                <Checkbox
                  id="is_active"
                  v-model:checked="form.is_active"
                  :disabled="form.processing"
                />
                <Label for="is_active" class="text-sm font-medium cursor-pointer">
                  Active Contact
                </Label>
              </div>
            </div>

            <div class="rounded-lg border p-4 bg-muted/30">
              <h4 class="font-medium mb-2">Contact Settings Info</h4>
              <ul class="text-sm text-muted-foreground space-y-1">
                <li>• <strong>Primary Contact:</strong> This contact will be the main point of contact for the customer</li>
                <li>• <strong>Active Contact:</strong> Only active contacts appear in searches and lists</li>
                <li>• A customer can only have one primary contact at a time</li>
                <li v-if="!isCurrentlyPrimary && form.is_primary">⚠️ This will remove primary status from the current primary contact</li>
              </ul>
            </div>
          </CardContent>
        </Card>

        <!-- Additional Notes -->
        <Card>
          <CardHeader>
            <CardTitle>Additional Notes</CardTitle>
            <CardDescription>
              Any extra information about this contact
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-2">
              <Label for="notes">Notes</Label>
              <Textarea
                id="notes"
                v-model="form.notes"
                placeholder="Enter any additional notes, preferences, or important information about this contact..."
                rows="4"
                :disabled="form.processing"
              />
              <InputError :message="form.errors.notes" />
              <p class="text-sm text-muted-foreground">
                {{ form.notes.length }}/5000 characters
              </p>
            </div>
          </CardContent>
        </Card>

        <!-- Form Actions -->
        <div class="flex items-center justify-between bg-muted/30 rounded-lg p-4">
          <div class="text-sm text-muted-foreground">
            <span class="font-medium">Required fields</span> are marked with *
          </div>

          <div class="flex items-center space-x-3">
            <Button
              type="button"
              variant="outline"
              @click="cancel"
              :disabled="form.processing"
            >
              <X class="h-4 w-4 mr-2" />
              Cancel
            </Button>

            <Button
              type="submit"
              :disabled="form.processing"
            >
              <Save class="h-4 w-4 mr-2" />
              {{ form.processing ? 'Updating...' : 'Update Contact' }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>