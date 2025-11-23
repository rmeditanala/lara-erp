<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { Separator } from '@/components/ui/separator'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { toast } from '@/components/ui/use-toast'
import {
  User,
  Building,
  Mail,
  Phone,
  Globe,
  MessageSquare,
  Tag,
  Calendar,
  TrendingUp,
  AlertCircle,
  Save,
  ArrowLeft
} from 'lucide-vue-next'

const props = defineProps<{
  lead: {
    id: number
    first_name: string
    last_name: string
    email: string | null
    phone: string | null
    mobile: string | null
    company_name: string | null
    job_title: string | null
    website: string | null
    description: string | null
    status: string
    source: string | null
    industry: string | null
    employees: number | null
    estimated_value: number | null
    currency: string
    priority: number
    follow_up_date: string | null
    notes: string | null
    lost_reason: string | null
    lost_details: string | null
    tags: string[] | null
  }
  users: Array<{
    id: number
    name: string
  }>
}>()

// Form state
const form = useForm({
  // Basic Information
  first_name: props.lead.first_name,
  last_name: props.lead.last_name,
  email: props.lead.email || '',
  phone: props.lead.phone || '',
  mobile: props.lead.mobile || '',
  company_name: props.lead.company_name || '',
  job_title: props.lead.job_title || '',
  website: props.lead.website || '',
  description: props.lead.description || '',

  // Lead Details
  status: props.lead.status,
  source: props.lead.source || '',
  industry: props.lead.industry || '',
  employees: props.lead.employees,
  estimated_value: props.lead.estimated_value,
  currency: props.lead.currency,
  priority: props.lead.priority,
  follow_up_date: props.lead.follow_up_date || '',
  notes: props.lead.notes || '',
  user_id: null,

  // Lost reason fields (only shown when status is 'lost')
  lost_reason: props.lead.lost_reason || '',
  lost_details: props.lead.lost_details || '',

  // Tags
  tags: [...(props.lead.tags || [])],
})

// Form tabs
const activeTab = ref('basic')

// Formatted estimated value
const formattedEstimatedValue = computed({
  get: () => {
    if (!form.estimated_value) return ''
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: form.currency,
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(form.estimated_value)
  },
  set: (value: string) => {
    // Remove currency symbols and formatting, convert to number
    const numeric = value.replace(/[^0-9.]/g, '')
    form.estimated_value = numeric ? parseFloat(numeric) : null
  }
})

// Lead score calculation
const leadScore = computed(() => {
  let score = 0

  if (form.first_name) score += 2
  if (form.last_name) score += 2
  if (form.email) score += 10
  if (form.phone) score += 10
  if (form.company_name) score += 15
  if (form.job_title) score += 5
  if (form.website) score += 5
  if (form.estimated_value) {
    if (form.estimated_value >= 100000) score += 25
    else if (form.estimated_value >= 50000) score += 20
    else if (form.estimated_value >= 10000) score += 15
    else if (form.estimated_value >= 1000) score += 10
  }
  if (form.employees) {
    if (form.employees >= 1000) score += 20
    else if (form.employees >= 100) score += 15
    else if (form.employees >= 50) score += 10
    else if (form.employees >= 10) score += 5
  }

  score += (form.priority - 1) * 5

  const highValueIndustries = ['technology', 'finance', 'healthcare', 'manufacturing']
  if (form.industry && highValueIndustries.includes(form.industry.toLowerCase())) {
    score += 15
  }

  return score
})

const leadRating = computed(() => {
  const score = leadScore.value
  if (score >= 70) return { value: 'hot', color: 'text-red-600 bg-red-100', label: 'Hot Lead' }
  if (score >= 40) return { value: 'warm', color: 'text-yellow-600 bg-yellow-100', label: 'Warm Lead' }
  if (score >= 20) return { value: 'cold', color: 'text-blue-600 bg-blue-100', label: 'Cold Lead' }
  return { value: 'cold', color: 'text-gray-600 bg-gray-100', label: 'Cold Lead' }
})

// Available options
const statusOptions = [
  { value: 'new', label: 'New', description: 'Initial contact' },
  { value: 'contacted', label: 'Contacted', description: 'Attempted contact made' },
  { value: 'qualified', label: 'Qualified', description: 'Qualified prospect' },
  { value: 'proposal', label: 'Proposal', description: 'Proposal sent' },
  { value: 'negotiation', label: 'Negotiation', description: 'In negotiation' },
  { value: 'converted', label: 'Converted', description: 'Successfully converted' },
  { value: 'lost', label: 'Lost', description: 'Lead was lost' },
  { value: 'recycled', label: 'Recycled', description: 'Lead recycled for re-engagement' },
]

const sourceOptions = [
  'Website',
  'Referral',
  'Cold Call',
  'Email',
  'Social Media',
  'Trade Show',
  'Advertisement',
  'Partner',
  'Other'
]

const industryOptions = [
  'Technology',
  'Healthcare',
  'Finance',
  'Manufacturing',
  'Retail',
  'Construction',
  'Consulting',
  'Real Estate',
  'Education',
  'Government',
  'Other'
]

const currencyOptions = [
  { value: 'USD', label: 'USD - US Dollar' },
  { value: 'EUR', label: 'EUR - Euro' },
  { value: 'GBP', label: 'GBP - British Pound' },
  { value: 'CAD', label: 'CAD - Canadian Dollar' },
  { value: 'AUD', label: 'AUD - Australian Dollar' },
]

const priorityLevels = [
  { value: 1, label: 'Low', color: 'bg-gray-500' },
  { value: 2, label: 'Normal', color: 'bg-blue-500' },
  { value: 3, label: 'Medium', color: 'bg-yellow-500' },
  { value: 4, label: 'High', color: 'bg-orange-500' },
  { value: 5, label: 'Critical', color: 'bg-red-500' },
]

const lostReasonOptions = [
  'Budget constraints',
  'Competitor chosen',
  'Timing not right',
  'No response',
  'Requirements changed',
  'Solution mismatch',
  'Poor fit',
  'Other'
]

// Form validation
const validationErrors = computed(() => form.errors)

const isValid = computed(() => {
  return form.first_name &&
         form.last_name &&
         form.status &&
         form.currency &&
         form.priority &&
         (form.status !== 'lost' || (form.lost_reason && form.lost_reason.trim()))
})

const isLostStatus = computed(() => form.status === 'lost')

// Form submission
const submit = () => {
  if (!isValid.value) {
    toast({
      title: "Validation Error",
      description: "Please fill in all required fields.",
      variant: "destructive",
    })
    return
  }

  form.put(route('leads.update', props.lead.id), {
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Lead updated successfully.",
      })
    },
    onError: (errors) => {
      toast({
        title: "Error",
        description: "Failed to update lead. Please check the form.",
        variant: "destructive",
      })
    },
  })
}

const cancel = () => {
  router.visit(route('leads.show', props.lead.id))
}

// Utility functions
const addTag = (event: KeyboardEvent) => {
  const target = event.target as HTMLInputElement
  if (event.key === 'Enter' && target.value.trim()) {
    event.preventDefault()
    if (!form.tags.includes(target.value.trim())) {
      form.tags.push(target.value.trim())
    }
    target.value = ''
  }
}

const removeTag = (index: number) => {
  form.tags.splice(index, 1)
}

const getTodayDate = () => {
  return new Date().toISOString().split('T')[0]
}
</script>

<template>
  <Head :title="`Edit ${lead.first_name} ${lead.last_name} - Lead`" />

  <AuthenticatedLayout>
    <div class="flex h-full flex-1 flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between border-b bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-3">
          <Button
            variant="ghost"
            size="sm"
            @click="cancel"
            class="text-gray-500 hover:text-gray-700"
          >
            <ArrowLeft class="h-4 w-4 mr-2" />
            Back to Lead
          </Button>
          <div class="h-6 w-px bg-gray-300 dark:bg-gray-600" />
          <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Lead</h1>
        </div>

        <!-- Lead Score Display -->
        <div class="flex items-center gap-4">
          <div class="text-right">
            <div class="text-sm font-medium text-gray-900 dark:text-white">Lead Score</div>
            <div class="text-2xl font-bold" :class="leadRating.color.split(' ')[0]">
              {{ leadScore }}
            </div>
          </div>
          <Badge :class="leadRating.color" class="text-sm font-medium">
            {{ leadRating.label }}
          </Badge>
        </div>
      </div>

      <!-- Form Content -->
      <div class="flex-1 overflow-y-auto bg-gray-50 p-6 dark:bg-gray-900">
        <div class="mx-auto max-w-4xl">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Basic Information Tab -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <User class="h-5 w-5" />
                  Basic Information
                </CardTitle>
                <CardDescription>
                  Contact details and basic lead information
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
                <!-- Name Fields -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <Label for="first_name" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      First Name <span class="text-red-500">*</span>
                    </Label>
                    <Input
                      id="first_name"
                      v-model="form.first_name"
                      type="text"
                      placeholder="John"
                      class="mt-1"
                      :class="{ 'border-red-500': validationErrors.first_name }"
                      required
                    />
                    <p v-if="validationErrors.first_name" class="mt-1 text-sm text-red-600">
                      {{ validationErrors.first_name }}
                    </p>
                  </div>

                  <div>
                    <Label for="last_name" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Last Name <span class="text-red-500">*</span>
                    </Label>
                    <Input
                      id="last_name"
                      v-model="form.last_name"
                      type="text"
                      placeholder="Doe"
                      class="mt-1"
                      :class="{ 'border-red-500': validationErrors.last_name }"
                      required
                    />
                    <p v-if="validationErrors.last_name" class="mt-1 text-sm text-red-600">
                      {{ validationErrors.last_name }}
                    </p>
                  </div>
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <Label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      <Mail class="h-4 w-4 inline mr-1" />
                      Email
                    </Label>
                    <Input
                      id="email"
                      v-model="form.email"
                      type="email"
                      placeholder="john.doe@example.com"
                      class="mt-1"
                      :class="{ 'border-red-500': validationErrors.email }"
                    />
                    <p v-if="validationErrors.email" class="mt-1 text-sm text-red-600">
                      {{ validationErrors.email }}
                    </p>
                  </div>

                  <div>
                    <Label for="phone" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      <Phone class="h-4 w-4 inline mr-1" />
                      Phone
                    </Label>
                    <Input
                      id="phone"
                      v-model="form.phone"
                      type="tel"
                      placeholder="+1 (555) 123-4567"
                      class="mt-1"
                      :class="{ 'border-red-500': validationErrors.phone }"
                    />
                    <p v-if="validationErrors.phone" class="mt-1 text-sm text-red-600">
                      {{ validationErrors.phone }}
                    </p>
                  </div>
                </div>

                <!-- Mobile Phone -->
                <div>
                  <Label for="mobile" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Mobile Phone
                  </Label>
                  <Input
                    id="mobile"
                    v-model="form.mobile"
                    type="tel"
                    placeholder="+1 (555) 987-6543"
                    class="mt-1"
                  />
                </div>

                <!-- Company Information -->
                <div class="space-y-4">
                  <div>
                    <Label for="company_name" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      <Building class="h-4 w-4 inline mr-1" />
                      Company Name
                    </Label>
                    <Input
                      id="company_name"
                      v-model="form.company_name"
                      type="text"
                      placeholder="Acme Corporation"
                      class="mt-1"
                    />
                  </div>

                  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                      <Label for="job_title" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Job Title
                      </Label>
                      <Input
                        id="job_title"
                        v-model="form.job_title"
                        type="text"
                        placeholder="CEO, Manager, etc."
                        class="mt-1"
                      />
                    </div>

                    <div>
                      <Label for="website" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        <Globe class="h-4 w-4 inline mr-1" />
                        Website
                      </Label>
                      <Input
                        id="website"
                        v-model="form.website"
                        type="url"
                        placeholder="https://example.com"
                        class="mt-1"
                      />
                    </div>
                  </div>
                </div>

                <!-- Description -->
                <div>
                  <Label for="description" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    <MessageSquare class="h-4 w-4 inline mr-1" />
                    Description
                  </Label>
                  <Textarea
                    id="description"
                    v-model="form.description"
                    placeholder="Brief description of the lead..."
                    class="mt-1"
                    rows="3"
                  />
                </div>
              </CardContent>
            </Card>

            <!-- Lead Details Tab -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <TrendingUp class="h-5 w-5" />
                  Lead Details
                </CardTitle>
                <CardDescription>
                  Status, qualification, and lead scoring information
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
                <!-- Status and Source -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <Label for="status" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Status <span class="text-red-500">*</span>
                    </Label>
                    <Select v-model="form.status">
                      <SelectTrigger class="mt-1">
                        <SelectValue placeholder="Select status" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="option in statusOptions"
                          :key="option.value"
                          :value="option.value"
                        >
                          <div>
                            <div class="font-medium">{{ option.label }}</div>
                            <div class="text-sm text-gray-500">{{ option.description }}</div>
                          </div>
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label for="source" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Lead Source
                    </Label>
                    <Select v-model="form.source">
                      <SelectTrigger class="mt-1">
                        <SelectValue placeholder="Select source" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="source in sourceOptions"
                          :key="source"
                          :value="source"
                        >
                          {{ source }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <!-- Lost Reason (only shown when status is 'lost') -->
                <div v-if="isLostStatus" class="space-y-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                  <div>
                    <Label for="lost_reason" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Lost Reason <span class="text-red-500">*</span>
                    </Label>
                    <Select v-model="form.lost_reason">
                      <SelectTrigger class="mt-1">
                        <SelectValue placeholder="Select reason for losing this lead" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="reason in lostReasonOptions"
                          :key="reason"
                          :value="reason"
                        >
                          {{ reason }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <p v-if="validationErrors.lost_reason" class="mt-1 text-sm text-red-600">
                      {{ validationErrors.lost_reason }}
                    </p>
                  </div>

                  <div>
                    <Label for="lost_details" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Lost Details
                    </Label>
                    <Textarea
                      id="lost_details"
                      v-model="form.lost_details"
                      placeholder="Additional details about why this lead was lost..."
                      class="mt-1"
                      rows="3"
                    />
                    <p v-if="validationErrors.lost_details" class="mt-1 text-sm text-red-600">
                      {{ validationErrors.lost_details }}
                    </p>
                  </div>
                </div>

                <!-- Industry and Company Size -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <Label for="industry" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Industry
                    </Label>
                    <Select v-model="form.industry">
                      <SelectTrigger class="mt-1">
                        <SelectValue placeholder="Select industry" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="industry in industryOptions"
                          :key="industry"
                          :value="industry"
                        >
                          {{ industry }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label for="employees" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Number of Employees
                    </Label>
                    <Input
                      id="employees"
                      v-model.number="form.employees"
                      type="number"
                      min="1"
                      placeholder="100"
                      class="mt-1"
                    />
                  </div>
                </div>

                <!-- Value and Priority -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                  <div>
                    <Label for="estimated_value" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Estimated Value
                    </Label>
                    <Input
                      id="estimated_value"
                      v-model="formattedEstimatedValue"
                      type="text"
                      placeholder="$50,000"
                      class="mt-1"
                    />
                  </div>

                  <div>
                    <Label for="currency" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Currency <span class="text-red-500">*</span>
                    </Label>
                    <Select v-model="form.currency">
                      <SelectTrigger class="mt-1">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="currency in currencyOptions"
                          :key="currency.value"
                          :value="currency.value"
                        >
                          {{ currency.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                      Priority <span class="text-red-500">*</span>
                    </Label>
                    <RadioGroup v-model="form.priority" class="mt-2 flex gap-2">
                      <div
                        v-for="priority in priorityLevels"
                        :key="priority.value"
                        class="flex items-center space-x-1"
                      >
                        <RadioGroupItem :id="`priority-${priority.value}`" :value="priority.value" />
                        <Label :for="`priority-${priority.value}`" class="text-sm cursor-pointer">
                          {{ priority.label }}
                        </Label>
                      </div>
                    </RadioGroup>
                  </div>
                </div>

                <!-- Follow-up Date -->
                <div>
                  <Label for="follow_up_date" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    <Calendar class="h-4 w-4 inline mr-1" />
                    Follow-up Date
                  </Label>
                  <Input
                    id="follow_up_date"
                    v-model="form.follow_up_date"
                    type="date"
                    :min="getTodayDate()"
                    class="mt-1"
                  />
                </div>

                <!-- Lead Assignment -->
                <div>
                  <Label for="user_id" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Assign To
                  </Label>
                  <Select v-model="form.user_id">
                    <SelectTrigger class="mt-1">
                      <SelectValue placeholder="Select user to assign this lead" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem :value="null">Unassigned</SelectItem>
                      <SelectItem
                        v-for="user in props.users"
                        :key="user.id"
                        :value="user.id"
                      >
                        {{ user.name }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </CardContent>
            </Card>

            <!-- Additional Information Tab -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Tag class="h-5 w-5" />
                  Additional Information
                </CardTitle>
                <CardDescription>
                  Notes, tags, and other information
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
                <!-- Notes -->
                <div>
                  <Label for="notes" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Internal Notes
                  </Label>
                  <Textarea
                    id="notes"
                    v-model="form.notes"
                    placeholder="Internal notes about this lead..."
                    class="mt-1"
                    rows="4"
                  />
                </div>

                <!-- Tags -->
                <div>
                  <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tags
                  </Label>
                  <div class="mt-2">
                    <Input
                      type="text"
                      placeholder="Add tags and press Enter..."
                      class="mb-2"
                      @keydown="addTag"
                    />
                    <div v-if="form.tags.length > 0" class="flex flex-wrap gap-2">
                      <Badge
                        v-for="(tag, index) in form.tags"
                        :key="index"
                        variant="secondary"
                        class="flex items-center gap-1"
                      >
                        {{ tag }}
                        <button
                          type="button"
                          @click="removeTag(index)"
                          class="ml-1 text-gray-500 hover:text-gray-700"
                        >
                          ×
                        </button>
                      </Badge>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 border-t bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
              <Button
                type="button"
                variant="outline"
                @click="cancel"
                :disabled="form.processing"
              >
                Cancel
              </Button>
              <Button
                type="submit"
                :disabled="!isValid || form.processing"
                class="min-w-[120px]"
              >
                <Save v-if="!form.processing" class="h-4 w-4 mr-2" />
                <div v-else class="h-4 w-4 mr-2 animate-spin rounded-full border-2 border-white border-t-transparent" />
                {{ form.processing ? 'Saving...' : 'Save Changes' }}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>