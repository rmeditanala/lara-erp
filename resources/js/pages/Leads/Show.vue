<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Textarea } from '@/components/ui/textarea'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { toast } from '@/components/ui/use-toast'
import {
  User,
  Building,
  Mail,
  Phone,
  Globe,
  Calendar,
  TrendingUp,
  MessageSquare,
  Edit,
  Trash2,
  ArrowLeft,
  CheckCircle,
  XCircle,
  Clock,
  Star,
  Tag,
  Users,
  Target,
  Activity,
  FileText,
  DollarSign,
  MapPin,
  Briefcase
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
    score: number
    rating: string | null
    follow_up_date: string | null
    notes: string | null
    converted_at: string | null
    converted_by: {
      id: number
      name: string
      email: string
    } | null
    lost_reason: string | null
    lost_details: string | null
    tags: string[] | null
    created_at: string
    updated_at: string
    owner: {
      id: number
      name: string
      email: string
    } | null
    customer: {
      id: number
      display_name: string
      customer_type: string
    } | null
    activities: Array<{
      id: number
      description: string
      details: string | null
      user: {
        name: string
        email: string
      }
      created_at: string
    }>
  }
}>()

// Form for converting lead
const convertForm = useForm({})

// Form for adding notes
const notesForm = useForm({
  notes: props.lead.notes || ''
})

// Dialog states
const isConvertDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)

// Computed properties
const fullName = computed(() => `${props.lead.first_name} ${props.lead.last_name}`)

const statusColor = computed(() => {
  return {
    new: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
    contacted: 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200',
    qualified: 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200',
    proposal: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200',
    negotiation: 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-200',
    converted: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-200',
    lost: 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200',
    recycled: 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-200',
  }[props.lead.status] || 'bg-gray-100 text-gray-800'
})

const ratingColor = computed(() => {
  return {
    hot: 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200',
    warm: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200',
    cold: 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200',
  }[props.lead.rating || ''] || 'bg-gray-100 text-gray-800'
})

const priorityColor = computed(() => {
  const colors = ['bg-gray-500', 'bg-blue-500', 'bg-yellow-500', 'bg-orange-500', 'bg-red-500']
  return colors[Math.min(props.lead.priority - 1, 4)] || 'bg-gray-500'
})

const priorityLabel = computed(() => {
  const labels = ['Low', 'Normal', 'Medium', 'High', 'Critical']
  return labels[Math.min(props.lead.priority - 1, 4)] || 'Normal'
})

const formattedEstimatedValue = computed(() => {
  if (!props.lead.estimated_value) return 'N/A'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: props.lead.currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(props.lead.estimated_value)
})

const isConverted = computed(() => props.lead.status === 'converted' && props.lead.customer_id !== null)
const isLost = computed(() => props.lead.status === 'lost')

// Actions
const convertToCustomer = () => {
  convertForm.post(route('leads.convert', props.lead.id), {
    onSuccess: (response) => {
      toast({
        title: "Success!",
        description: "Lead has been converted to a customer.",
      })
      isConvertDialogOpen.value = false

      // Redirect to the new customer page
      if (response.props.customer_id) {
        router.visit(route('customers.show', response.props.customer_id))
      }
    },
    onError: (errors) => {
      toast({
        title: "Error",
        description: "Failed to convert lead. Please try again.",
        variant: "destructive",
      })
      isConvertDialogOpen.value = false
    },
  })
}

const deleteLead = () => {
  router.delete(route('leads.destroy', props.lead.id), {
    onSuccess: () => {
      toast({
        title: "Lead deleted",
        description: "Lead has been successfully deleted.",
      })
      router.visit(route('leads.index'))
    },
    onError: () => {
      toast({
        title: "Error",
        description: "Failed to delete lead. Please try again.",
        variant: "destructive",
      })
      isDeleteDialogOpen.value = false
    },
  })
}

const updateNotes = () => {
  notesForm.put(route('leads.update', props.lead.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Notes updated",
        description: "Lead notes have been updated successfully.",
      })
    },
    onError: () => {
      toast({
        title: "Error",
        description: "Failed to update notes. Please try again.",
        variant: "destructive",
      })
    },
  })
}

const formatDate = (dateString: string | null) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'converted': return CheckCircle
    case 'lost': return XCircle
    default: return Clock
  }
}
</script>

<template>
  <Head :title="`${fullName} - Lead Details`" />

  <AuthenticatedLayout>
    <div class="flex h-full flex-1 flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between border-b bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-3">
          <Button
            variant="ghost"
            size="sm"
            :href="route('leads.index')"
            class="text-gray-500 hover:text-gray-700"
          >
            <ArrowLeft class="h-4 w-4 mr-2" />
            Back to Leads
          </Button>
          <div class="h-6 w-px bg-gray-300 dark:bg-gray-600" />
          <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ fullName }}</h1>
          <Badge :class="statusColor" class="text-sm">
            {{ lead.status.charAt(0).toUpperCase() + lead.status.slice(1) }}
          </Badge>
        </div>

        <div class="flex items-center gap-2">
          <!-- Lead Score and Rating -->
          <div class="flex items-center gap-3 mr-4">
            <div class="text-right">
              <div class="text-sm font-medium text-gray-900 dark:text-white">Score</div>
              <div class="text-lg font-bold">{{ lead.score }}</div>
            </div>
            <Badge v-if="lead.rating" :class="ratingColor" class="text-sm">
              {{ lead.rating.toUpperCase() }}
            </Badge>
          </div>

          <!-- Action Buttons -->
          <Button
            variant="outline"
            size="sm"
            :href="route('leads.edit', lead.id)"
          >
            <Edit class="h-4 w-4 mr-2" />
            Edit
          </Button>

          <Dialog v-model:open="isConvertDialogOpen">
            <DialogTrigger as-child>
              <Button
                size="sm"
                :disabled="isConverted"
                class="bg-green-600 hover:bg-green-700"
              >
                <Target class="h-4 w-4 mr-2" />
                Convert to Customer
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Convert Lead to Customer</DialogTitle>
                <DialogDescription>
                  Are you sure you want to convert {{ fullName }} to a customer? This will create a customer record and mark the lead as converted.
                </DialogDescription>
              </DialogHeader>
              <DialogFooter>
                <Button
                  variant="outline"
                  @click="isConvertDialogOpen = false"
                >
                  Cancel
                </Button>
                <Button
                  @click="convertToCustomer"
                  :disabled="convertForm.processing"
                  class="bg-green-600 hover:bg-green-700"
                >
                  <div v-if="convertForm.processing" class="h-4 w-4 mr-2 animate-spin rounded-full border-2 border-white border-t-transparent" />
                  <CheckCircle v-else class="h-4 w-4 mr-2" />
                  Convert
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>

          <Dialog v-model:open="isDeleteDialogOpen">
            <DialogTrigger as-child>
              <Button
                variant="destructive"
                size="sm"
              >
                <Trash2 class="h-4 w-4 mr-2" />
                Delete
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Delete Lead</DialogTitle>
                <DialogDescription>
                  Are you sure you want to delete {{ fullName }}? This action cannot be undone.
                </DialogDescription>
              </DialogHeader>
              <DialogFooter>
                <Button
                  variant="outline"
                  @click="isDeleteDialogOpen = false"
                >
                  Cancel
                </Button>
                <Button
                  variant="destructive"
                  @click="deleteLead"
                >
                  <Trash2 class="h-4 w-4 mr-2" />
                  Delete Lead
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto bg-gray-50 p-6 dark:bg-gray-900">
        <div class="mx-auto max-w-6xl">
          <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="space-y-6 lg:col-span-2">
              <!-- Contact Information -->
              <Card>
                <CardHeader>
                  <CardTitle class="flex items-center gap-2">
                    <User class="h-5 w-5" />
                    Contact Information
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Basic Info -->
                    <div class="space-y-4">
                      <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</label>
                        <div class="mt-1 text-gray-900 dark:text-white">{{ fullName }}</div>
                      </div>

                      <div v-if="lead.job_title">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Job Title</label>
                        <div class="mt-1 text-gray-900 dark:text-white">{{ lead.job_title }}</div>
                      </div>

                      <div v-if="lead.company_name">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Company</label>
                        <div class="mt-1 text-gray-900 dark:text-white">{{ lead.company_name }}</div>
                      </div>

                      <div v-if="lead.industry">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Industry</label>
                        <div class="mt-1 text-gray-900 dark:text-white">{{ lead.industry }}</div>
                      </div>
                    </div>

                    <!-- Contact Details -->
                    <div class="space-y-4">
                      <div v-if="lead.email">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                          <Mail class="h-4 w-4" />
                          Email
                        </label>
                        <div class="mt-1">
                          <a :href="`mailto:${lead.email}`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            {{ lead.email }}
                          </a>
                        </div>
                      </div>

                      <div v-if="lead.phone">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                          <Phone class="h-4 w-4" />
                          Phone
                        </label>
                        <div class="mt-1">
                          <a :href="`tel:${lead.phone}`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            {{ lead.phone }}
                          </a>
                        </div>
                      </div>

                      <div v-if="lead.mobile">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                          <Phone class="h-4 w-4" />
                          Mobile
                        </label>
                        <div class="mt-1">
                          <a :href="`tel:${lead.mobile}`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            {{ lead.mobile }}
                          </a>
                        </div>
                      </div>

                      <div v-if="lead.website">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                          <Globe class="h-4 w-4" />
                          Website
                        </label>
                        <div class="mt-1">
                          <a
                            :href="lead.website.startsWith('http') ? lead.website : `https://${lead.website}`"
                            target="_blank"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                          >
                            {{ lead.website }}
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>

                  <Separator v-if="lead.description" class="my-6" />

                  <div v-if="lead.description">
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</label>
                    <div class="mt-2 text-gray-900 dark:text-white whitespace-pre-wrap">{{ lead.description }}</div>
                  </div>
                </CardContent>
              </Card>

              <!-- Lead Details -->
              <Card>
                <CardHeader>
                  <CardTitle class="flex items-center gap-2">
                    <TrendingUp class="h-5 w-5" />
                    Lead Details
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                      <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                      <div class="mt-1">
                        <div class="flex items-center gap-2">
                          <component :is="getStatusIcon(lead.status)" class="h-4 w-4" />
                          <Badge :class="statusColor">
                            {{ lead.status.charAt(0).toUpperCase() + lead.status.slice(1) }}
                          </Badge>
                        </div>
                      </div>
                    </div>

                    <div>
                      <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Priority</label>
                      <div class="mt-1">
                        <div class="flex items-center gap-2">
                          <div :class="`w-3 h-3 rounded-full ${priorityColor}`" />
                          <span class="text-gray-900 dark:text-white">{{ priorityLabel }}</span>
                        </div>
                      </div>
                    </div>

                    <div>
                      <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Source</label>
                      <div class="mt-1 text-gray-900 dark:text-white">{{ lead.source || 'N/A' }}</div>
                    </div>

                    <div>
                      <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Estimated Value</label>
                      <div class="mt-1 flex items-center gap-1">
                        <DollarSign class="h-4 w-4 text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-medium">{{ formattedEstimatedValue }}</span>
                      </div>
                    </div>

                    <div v-if="lead.employees">
                      <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Company Size</label>
                      <div class="mt-1 flex items-center gap-1">
                        <Building class="h-4 w-4 text-gray-400" />
                        <span class="text-gray-900 dark:text-white">{{ lead.employees.toLocaleString() }} employees</span>
                      </div>
                    </div>

                    <div v-if="lead.follow_up_date">
                      <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Follow-up Date</label>
                      <div class="mt-1 flex items-center gap-1">
                        <Calendar class="h-4 w-4 text-gray-400" />
                        <span class="text-gray-900 dark:text-white">{{ formatDate(lead.follow_up_date) }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Tags -->
                  <div v-if="lead.tags && lead.tags.length > 0" class="mt-6">
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                      <Tag class="h-4 w-4" />
                      Tags
                    </label>
                    <div class="mt-2 flex flex-wrap gap-2">
                      <Badge
                        v-for="tag in lead.tags"
                        :key="tag"
                        variant="secondary"
                      >
                        {{ tag }}
                      </Badge>
                    </div>
                  </div>

                  <!-- Conversion Info -->
                  <div v-if="isConverted" class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-2">
                      <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                      <div>
                        <div class="font-medium text-green-900 dark:text-green-100">Converted to Customer</div>
                        <div class="text-sm text-green-700 dark:text-green-300">
                          {{ formatDate(lead.converted_at) }} by {{ lead.converted_by?.name }}
                        </div>
                        <div v-if="lead.customer" class="mt-1">
                          <Link
                            :href="route('customers.show', lead.customer.id)"
                            class="text-sm text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
                          >
                            View Customer: {{ lead.customer.display_name }}
                          </Link>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Lost Info -->
                  <div v-if="isLost" class="mt-6 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                    <div class="flex items-center gap-2">
                      <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                      <div>
                        <div class="font-medium text-red-900 dark:text-red-100">Lead Lost</div>
                        <div class="text-sm text-red-700 dark:text-red-300">
                          Reason: {{ lead.lost_reason || 'Not specified' }}
                        </div>
                        <div v-if="lead.lost_details" class="mt-1 text-sm text-red-600 dark:text-red-400">
                          {{ lead.lost_details }}
                        </div>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <!-- Activity Timeline -->
              <Card>
                <CardHeader>
                  <CardTitle class="flex items-center gap-2">
                    <Activity class="h-5 w-5" />
                    Activity Timeline
                  </CardTitle>
                  <CardDescription>
                    Recent activities and changes for this lead
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div v-if="lead.activities && lead.activities.length > 0" class="space-y-4">
                    <div
                      v-for="activity in lead.activities"
                      :key="activity.id"
                      class="flex gap-3 pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0"
                    >
                      <Avatar class="h-8 w-8">
                        <AvatarImage :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(activity.user.name)}&background=random`" />
                        <AvatarFallback>{{ activity.user.name.charAt(0) }}</AvatarFallback>
                      </Avatar>
                      <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                          <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                              {{ activity.description }}
                            </div>
                            <div v-if="activity.details" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                              {{ activity.details }}
                            </div>
                          </div>
                          <div class="flex-shrink-0 ml-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                              {{ formatDateTime(activity.created_at) }}
                            </span>
                          </div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                          by {{ activity.user.name }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <Activity class="h-12 w-12 mx-auto mb-3 opacity-50" />
                    <div>No activities yet</div>
                  </div>
                </CardContent>
              </Card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
              <!-- Owner Information -->
              <Card>
                <CardHeader>
                  <CardTitle class="flex items-center gap-2">
                    <Users class="h-5 w-5" />
                    Lead Owner
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div v-if="lead.owner" class="flex items-center gap-3">
                    <Avatar class="h-10 w-10">
                      <AvatarImage :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(lead.owner.name)}&background=random`" />
                      <AvatarFallback>{{ lead.owner.name.charAt(0) }}</AvatarFallback>
                    </Avatar>
                    <div>
                      <div class="font-medium text-gray-900 dark:text-white">{{ lead.owner.name }}</div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">{{ lead.owner.email }}</div>
                    </div>
                  </div>
                  <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                    No lead owner assigned
                  </div>
                </CardContent>
              </Card>

              <!-- Internal Notes -->
              <Card>
                <CardHeader>
                  <CardTitle class="flex items-center gap-2">
                    <FileText class="h-5 w-5" />
                    Internal Notes
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <form @submit.prevent="updateNotes" class="space-y-4">
                    <Textarea
                      v-model="notesForm.notes"
                      placeholder="Add internal notes about this lead..."
                      rows="6"
                      class="resize-none"
                    />
                    <div class="flex justify-end">
                      <Button
                        type="submit"
                        size="sm"
                        :disabled="notesForm.processing"
                      >
                        <div v-if="notesForm.processing" class="h-4 w-4 mr-2 animate-spin rounded-full border-2 border-white border-t-transparent" />
                        <Save v-else class="h-4 w-4 mr-2" />
                        Save Notes
                      </Button>
                    </div>
                  </form>
                </CardContent>
              </Card>

              <!-- Metadata -->
              <Card>
                <CardHeader>
                  <CardTitle>Metadata</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                  <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</label>
                    <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDateTime(lead.created_at) }}</div>
                  </div>
                  <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</label>
                    <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDateTime(lead.updated_at) }}</div>
                  </div>
                  <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Lead ID</label>
                    <div class="mt-1 text-sm text-gray-900 dark:text-white">#{{ lead.id }}</div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>