<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Phone,
  Mail,
  User,
  Briefcase,
  Building,
  Star,
  Calendar,
  MapPin,
  Edit,
  MessageSquare,
  FileText,
  MoreHorizontal,
  ArrowLeft,
  CheckCircle,
  XCircle,
  Clock,
  UserPlus,
  Settings,
  Trash2
} from 'lucide-vue-next'

interface Customer {
  id: number
  display_name: string
  customer_type: string
  email: string
  phone: string
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

const title = computed(() => `${props.contact.first_name} ${props.contact.last_name}`)
const fullName = computed(() => `${props.contact.first_name} ${props.contact.last_name}`)

const isPrimary = computed(() => props.contact.is_primary)
const isActive = computed(() => props.contact.is_active)

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const formatDateTime = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const makePrimary = () => {
  if (confirm(`Are you sure you want to set ${fullName.value} as the primary contact for ${props.customer.display_name}?`)) {
    router.post(`/contacts/${props.customer.id}/${props.contact.id}/make-primary`, {}, {
      onSuccess: () => {
        // Contact will be refreshed automatically
      },
    })
  }
}

const toggleStatus = () => {
  const action = isActive.value ? 'deactivate' : 'activate'
  const confirmMessage = isActive.value
    ? `Are you sure you want to deactivate ${fullName.value}? They will no longer appear in searches and lists.`
    : `Are you sure you want to activate ${fullName.value}?`

  if (confirm(confirmMessage)) {
    router.delete(`/contacts/${props.customer.id}/${props.contact.id}`, {
      onSuccess: () => {
        router.visit(route('customers.show', props.customer.id))
      },
    })
  }
}

const editContact = () => {
  router.visit(route('contacts.edit', [props.customer.id, props.contact.id]))
}

const quickActions = [
  {
    label: 'Send Email',
    icon: Mail,
    action: () => {
      window.open(`mailto:${props.contact.email}`, '_blank')
    },
    disabled: !props.contact.email
  },
  {
    label: 'Create Quote',
    icon: FileText,
    action: () => {
      // TODO: Navigate to quote creation with this contact
      alert('Quote creation coming soon!')
    }
  },
  {
    label: 'Schedule Meeting',
    icon: Calendar,
    action: () => {
      // TODO: Navigate to meeting scheduling
      alert('Meeting scheduling coming soon!')
    }
  },
  {
    label: 'Add Note',
    icon: MessageSquare,
    action: () => {
      // TODO: Open note dialog
      alert('Note adding coming soon!')
    }
  }
]

// Activities from the database
const activities = computed(() => {
  return (props.contact.activities || []).map(activity => ({
    ...activity,
    icon: getActivityIcon(activity.type),
    iconColor: getActivityIconColor(activity.type)
  }))
})

// Helper functions for activity icons and colors
const getActivityIcon = (type: string) => {
  const iconMap: Record<string, any> = {
    'contact_added': UserPlus,
    'contact_updated': Edit,
    'contact_deactivated': XCircle,
    'contact_activated': CheckCircle,
    'primary_set': Star,
    'primary_removed': Star,
    'created': UserPlus,
    'updated': Edit,
    'deleted': Trash2,
    'status_changed': Settings,
  }
  return iconMap[type] || Activity
}

const getActivityIconColor = (type: string) => {
  const colorMap: Record<string, string> = {
    'contact_added': 'text-green-600 dark:text-green-400',
    'contact_activated': 'text-green-600 dark:text-green-400',
    'created': 'text-green-600 dark:text-green-400',
    'contact_updated': 'text-blue-600 dark:text-blue-400',
    'updated': 'text-blue-600 dark:text-blue-400',
    'contact_deactivated': 'text-red-600 dark:text-red-400',
    'deleted': 'text-red-600 dark:text-red-400',
    'primary_set': 'text-amber-600 dark:text-amber-400',
    'primary_removed': 'text-gray-600 dark:text-gray-400',
    'status_changed': 'text-yellow-600 dark:text-yellow-400',
  }
  return colorMap[type] || 'text-gray-600 dark:text-gray-400'
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
            @click="router.visit(route('customers.show', customer.id))"
            class="text-muted-foreground hover:text-foreground"
          >
            <ArrowLeft class="h-4 w-4 mr-1" />
            Back to Customer
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
            <BreadcrumbPage>
              {{ title }}
            </BreadcrumbPage>
          </BreadcrumbList>
        </Breadcrumb>

        <div class="flex items-start justify-between">
          <div>
            <div class="flex items-center space-x-3">
              <h1 class="text-3xl font-bold tracking-tight">{{ title }}</h1>
              <div v-if="isPrimary" class="flex items-center text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-3 py-1 rounded-full text-sm font-medium">
                <Star class="h-4 w-4 mr-1 fill-current" />
                Primary Contact
              </div>
              <div v-if="isActive" class="flex items-center text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-3 py-1 rounded-full text-sm font-medium">
                <CheckCircle class="h-4 w-4 mr-1" />
                Active
              </div>
              <div v-else class="flex items-center text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/20 px-3 py-1 rounded-full text-sm font-medium">
                <XCircle class="h-4 w-4 mr-1" />
                Inactive
              </div>
            </div>
            <p class="text-muted-foreground mt-1">
              Contact for {{ customer.display_name }} • {{ contact.job_title || 'Contact Person' }}
            </p>
          </div>

          <div class="flex items-center space-x-3">
            <!-- Quick Actions Dropdown -->
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" size="sm">
                  <MoreHorizontal class="h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuLabel>Quick Actions</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                  v-for="action in quickActions"
                  :key="action.label"
                  :disabled="action.disabled"
                  @click="action.action"
                >
                  <component :is="action.icon" class="h-4 w-4 mr-2" />
                  {{ action.label }}
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem @click="makePrimary" v-if="!isPrimary">
                  <Star class="h-4 w-4 mr-2" />
                  Make Primary Contact
                </DropdownMenuItem>
                <DropdownMenuItem @click="toggleStatus">
                  <Settings class="h-4 w-4 mr-2" />
                  {{ isActive ? 'Deactivate Contact' : 'Activate Contact' }}
                </DropdownMenuItem>
                <DropdownMenuItem @click="editContact">
                  <Edit class="h-4 w-4 mr-2" />
                  Edit Contact
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>

            <Button @click="editContact">
              <Edit class="h-4 w-4 mr-2" />
              Edit Contact
            </Button>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Contact Information -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <User class="h-5 w-5 mr-2" />
                Contact Information
              </CardTitle>
              <CardDescription>
                Personal and contact details
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <!-- Basic Info -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 class="text-sm font-medium text-muted-foreground mb-1">Full Name</h4>
                  <p class="font-medium">{{ fullName }}</p>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-muted-foreground mb-1">Job Title</h4>
                  <p>{{ contact.job_title || 'Not specified' }}</p>
                </div>
              </div>

              <!-- Department -->
              <div>
                <h4 class="text-sm font-medium text-muted-foreground mb-1">Department</h4>
                <p>{{ contact.department || 'Not specified' }}</p>
              </div>

              <Separator />

              <!-- Contact Methods -->
              <div class="space-y-4">
                <h4 class="text-sm font-medium text-muted-foreground">Contact Methods</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Email -->
                  <div v-if="contact.email" class="flex items-center space-x-3">
                    <Mail class="h-4 w-4 text-muted-foreground" />
                    <div>
                      <p class="text-sm font-medium">Email</p>
                      <Button
                        variant="link"
                        class="h-auto p-0 text-blue-600 dark:text-blue-400"
                        @click="window.open(`mailto:${contact.email}`, '_blank')"
                      >
                        {{ contact.email }}
                      </Button>
                    </div>
                  </div>

                  <!-- Phone -->
                  <div v-if="contact.phone" class="flex items-center space-x-3">
                    <Phone class="h-4 w-4 text-muted-foreground" />
                    <div>
                      <p class="text-sm font-medium">Phone</p>
                      <Button
                        variant="link"
                        class="h-auto p-0 text-blue-600 dark:text-blue-400"
                        @click="window.open(`tel:${contact.phone}`, '_blank')"
                      >
                        {{ contact.phone }}
                      </Button>
                    </div>
                  </div>

                  <!-- Mobile -->
                  <div v-if="contact.mobile" class="flex items-center space-x-3">
                    <Phone class="h-4 w-4 text-muted-foreground" />
                    <div>
                      <p class="text-sm font-medium">Mobile</p>
                      <Button
                        variant="link"
                        class="h-auto p-0 text-blue-600 dark:text-blue-400"
                        @click="window.open(`tel:${contact.mobile}`, '_blank')"
                      >
                        {{ contact.mobile }}
                      </Button>
                    </div>
                  </div>
                </div>

                <div v-if="!contact.email && !contact.phone && !contact.mobile" class="text-muted-foreground text-sm">
                  No contact methods specified
                </div>
              </div>

              <!-- Notes -->
              <div v-if="contact.notes">
                <Separator />
                <div>
                  <h4 class="text-sm font-medium text-muted-foreground mb-2">Notes</h4>
                  <div class="bg-muted/30 rounded-lg p-4 text-sm">
                    {{ contact.notes }}
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Activity Timeline -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <Clock class="h-5 w-5 mr-2" />
                Activity Timeline
              </CardTitle>
              <CardDescription>
                Recent activities and changes
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div
                  v-for="activity in activities"
                  :key="activity.id"
                  class="flex items-start space-x-4"
                >
                  <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-muted">
                      <component
                        :is="activity.icon"
                        class="h-4 w-4"
                        :class="activity.iconColor"
                      />
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2">
                      <p class="text-sm font-medium">{{ activity.title }}</p>
                      <span class="text-xs text-muted-foreground">
                        {{ formatDateTime(activity.created_at) }}
                      </span>
                    </div>
                    <p class="text-sm text-muted-foreground">{{ activity.description }}</p>
                    <div v-if="activity.user" class="flex items-center space-x-1 text-xs text-muted-foreground">
                      <span>by</span>
                      <span class="font-medium">{{ activity.user.name }}</span>
                    </div>
                  </div>
                </div>

                <div v-if="activities.length === 0" class="text-center py-8 text-muted-foreground">
                  No recent activities to show
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar (1/3 width) -->
        <div class="space-y-6">
          <!-- Customer Information -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center">
                <Building class="h-5 w-5 mr-2" />
                Customer Information
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div>
                <h4 class="text-sm font-medium text-muted-foreground mb-1">Customer</h4>
                <Button
                  variant="link"
                  class="h-auto p-0 text-blue-600 dark:text-blue-400"
                  @click="router.visit(route('customers.show', customer.id))"
                >
                  {{ customer.display_name }}
                </Button>
              </div>

              <div v-if="customer.email">
                <h4 class="text-sm font-medium text-muted-foreground mb-1">Customer Email</h4>
                <p class="text-sm">{{ customer.email }}</p>
              </div>

              <div v-if="customer.phone">
                <h4 class="text-sm font-medium text-muted-foreground mb-1">Customer Phone</h4>
                <p class="text-sm">{{ customer.phone }}</p>
              </div>

              <div>
                <h4 class="text-sm font-medium text-muted-foreground mb-1">Customer Type</h4>
                <Badge :variant="customer.customer_type === 'individual' ? 'default' : 'secondary'">
                  {{ customer.customer_type }}
                </Badge>
              </div>
            </CardContent>
          </Card>

          <!-- Quick Stats -->
          <Card>
            <CardHeader>
              <CardTitle>Contact Status</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Status</span>
                <Badge :variant="isActive ? 'default' : 'secondary'">
                  {{ isActive ? 'Active' : 'Inactive' }}
                </Badge>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Primary Contact</span>
                <Badge :variant="isPrimary ? 'default' : 'secondary'">
                  {{ isPrimary ? 'Yes' : 'No' }}
                </Badge>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Member Since</span>
                <span class="text-sm font-medium">{{ formatDate(contact.created_at) }}</span>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Last Updated</span>
                <span class="text-sm font-medium">{{ formatDate(contact.updated_at) }}</span>
              </div>
            </CardContent>
          </Card>

          <!-- Quick Actions -->
          <Card>
            <CardHeader>
              <CardTitle>Quick Actions</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <Button
                v-for="action in quickActions"
                :key="action.label"
                variant="outline"
                size="sm"
                class="w-full justify-start"
                :disabled="action.disabled"
                @click="action.action"
              >
                <component :is="action.icon" class="h-4 w-4 mr-2" />
                {{ action.label }}
              </Button>

              <Separator />

              <Button
                v-if="!isPrimary"
                variant="outline"
                size="sm"
                class="w-full justify-start"
                @click="makePrimary"
              >
                <Star class="h-4 w-4 mr-2" />
                Make Primary Contact
              </Button>

              <Button
                variant="outline"
                size="sm"
                class="w-full justify-start"
                @click="editContact"
              >
                <Edit class="h-4 w-4 mr-2" />
                Edit Contact
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>