<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
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
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import {
  Alert,
  AlertDescription,
  AlertTitle,
} from '@/components/ui/alert'
import {
  Users,
  TrendingUp,
  UserCheck,
  AlertTriangle,
  Star,
  Calendar,
  Search,
  Filter,
  Plus,
  ChevronDown,
  MoreHorizontal,
  Edit,
  Trash2,
  UserPlus,
  ArrowUpDown,
  X,
  Check
} from 'lucide-vue-next'

interface User {
  id: number
  name: string
  email: string
}

interface Lead {
  id: number
  first_name: string
  last_name: string
  email: string
  phone: string
  company_name: string
  job_title: string
  status: string
  source: string
  rating: string
  priority: number
  score: number
  estimated_value: number
  currency: string
  follow_up_date: string
  created_at: string
  owner?: User
  customer?: {
    id: number
    display_name: string
  }
}

interface Props {
  leads: {
    data: Lead[]
    links: object
    meta: object
  }
  stats: {
    total: number
    active: number
    converted: number
    lost: number
    high_priority: number
    needs_follow_up: number
    hot: number
  }
  filters: {
    search: string
    status: string
    owner_id: string
    rating: string
    source: string
    high_priority: boolean
    needs_follow_up: boolean
    sort: string
    direction: string
  }
  users: User[]
}

const props = defineProps<Props>()

const processing = ref(false)
const showBulkActionModal = ref(false)
const selectedLeads = ref<number[]>([])
const bulkAction = ref('')
const assignToUser = ref('')

// Reactive filters
const search = ref(props.filters.search)
const status = ref(props.filters.status)
const ownerId = ref(props.filters.owner_id)
const rating = ref(props.filters.rating)
const source = ref(props.filters.source)
const highPriority = ref(props.filters.high_priority)
const needsFollowUp = ref(props.filters.needs_follow_up)
const sort = ref(props.filters.sort)
const direction = ref(props.filters.direction)

// Computed properties
const hasSelectedLeads = computed(() => selectedLeads.value.length > 0)

const statusOptions = [
  { value: '', label: 'All Statuses' },
  { value: 'new', label: 'New' },
  { value: 'contacted', label: 'Contacted' },
  { value: 'qualified', label: 'Qualified' },
  { value: 'proposal', label: 'Proposal' },
  { value: 'negotiation', label: 'Negotiation' },
  { value: 'converted', label: 'Converted' },
  { value: 'lost', label: 'Lost' },
  { value: 'recycled', label: 'Recycled' },
]

const ratingOptions = [
  { value: '', label: 'All Ratings' },
  { value: 'hot', label: 'Hot 🔥' },
  { value: 'warm', label: 'Warm' },
  { value: 'cold', label: 'Cold' },
]

const sourceOptions = [
  { value: '', label: 'All Sources' },
  { value: 'website', label: 'Website' },
  { value: 'referral', label: 'Referral' },
  { value: 'cold_call', label: 'Cold Call' },
  { value: 'email', label: 'Email' },
  { value: 'social', label: 'Social Media' },
  { value: 'trade_show', label: 'Trade Show' },
  { value: 'advertisement', label: 'Advertisement' },
  { value: 'other', label: 'Other' },
]

const sortOptions = [
  { value: 'created_at', label: 'Created Date' },
  { value: 'first_name', label: 'First Name' },
  { value: 'last_name', label: 'Last Name' },
  { value: 'company_name', label: 'Company' },
  { value: 'score', label: 'Score' },
  { value: 'estimated_value', label: 'Value' },
  { value: 'priority', label: 'Priority' },
  { value: 'follow_up_date', label: 'Follow-up Date' },
]

const priorityLevels = [
  { value: 1, label: 'Low', color: 'gray' },
  { value: 2, label: 'Medium-Low', color: 'blue' },
  { value: 3, label: 'Medium', color: 'yellow' },
  { value: 4, label: 'Medium-High', color: 'orange' },
  { value: 5, label: 'High', color: 'red' },
]

// Helper functions
const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    new: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
    contacted: 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200',
    qualified: 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200',
    proposal: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200',
    negotiation: 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-200',
    converted: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-200',
    lost: 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200',
    recycled: 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-200',
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

const getRatingColor = (rating: string) => {
  const colors: Record<string, string> = {
    hot: 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200',
    warm: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200',
    cold: 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200',
  }
  return colors[rating] || 'bg-gray-100 text-gray-800'
}

const getPriorityColor = (priority: number) => {
  const color = priorityLevels.find(p => p.value === priority)
  const colors: Record<string, string> = {
    gray: 'border-gray-200 dark:border-gray-700',
    blue: 'border-blue-200 dark:border-blue-700',
    yellow: 'border-yellow-200 dark:border-yellow-700',
    orange: 'border-orange-200 dark:border-orange-700',
    red: 'border-red-200 dark:border-red-700',
  }
  return colors[color?.color || 'gray']
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const formatValue = (value: number, currency: string) => {
  if (!value) return 'N/A'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency,
  }).format(value)
}

// Event handlers
const toggleLeadSelection = (leadId: number) => {
  const index = selectedLeads.value.indexOf(leadId)
  if (index > -1) {
    selectedLeads.value.splice(index, 1)
  } else {
    selectedLeads.value.push(leadId)
  }
}

const toggleAllLeads = () => {
  if (hasSelectedLeads.value) {
    selectedLeads.value = []
  } else {
    selectedLeads.value = props.leads.data.map(lead => lead.id)
  }
}

const applyFilters = () => {
  router.get(route('leads.index'), {
    search: search.value,
    status: status.value,
    owner_id: ownerId.value,
    rating: rating.value,
    source: source.value,
    high_priority: highPriority.value,
    needs_follow_up: needsFollowUp.value,
    sort: sort.value,
    direction: direction.value,
  }, {
    preserveState: true,
  })
}

const clearFilters = () => {
  search.value = ''
  status.value = ''
  ownerId.value = ''
  rating.value = ''
  source.value = ''
  highPriority.value = false
  needsFollowUp.value = false
  sort.value = 'created_at'
  direction.value = 'desc'
  applyFilters()
}

const convertLead = (lead: Lead) => {
  if (confirm(`Are you sure you want to convert ${lead.first_name} ${lead.last_name} to a customer? This will create a customer record and update the lead status.`)) {
    processing.value = true

    router.post(`/leads/${lead.id}/convert`, {}, {
      onSuccess: (response) => {
        // Redirect to the newly created customer
        const customerUrl = response.props.customer_url
        window.location.href = customerUrl
      },
      onError: () => {
        processing.value = false
      },
    })
  }
}

const executeBulkAction = () => {
  if (!bulkAction.value) return
  if (selectedLeads.value.length === 0) {
    alert('Please select at least one lead')
    return
  }

  const data: any = {
    action: bulkAction.value,
    lead_ids: selectedLeads.value,
  }

  if (bulkAction.value === 'assign') {
    if (!assignToUser.value) {
      alert('Please select a user to assign leads to')
      return
    }
    data.user_id = assignToUser.value
  }

  if (bulkAction.value === 'status') {
    // TODO: Add status selection modal
    return
  }

  processing.value = true

  router.post('/leads/bulk-action', data, {
    onSuccess: () => {
      showBulkActionModal.value = false
      selectedLeads.value = []
      assignToUser.value = ''
      bulkAction.value = ''
      router.reload({ only: ['leads', 'stats'] })
    },
    onFinish: () => {
      processing.value = false
    },
  })
}

const deleteLead = (lead: Lead) => {
  if (confirm(`Are you sure you want to delete ${lead.first_name} ${lead.last_name}? This action cannot be undone.`)) {
    router.delete(`/leads/${lead.id}`)
  }
}
</script>

<template>
  <Head title="Leads" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="space-y-2">
        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbLink :href="route('dashboard')">
                Dashboard
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbPage>
              Leads
            </BreadcrumbPage>
          </BreadcrumbList>
        </Breadcrumb>

        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold tracking-tight">Leads</h1>
            <p class="text-muted-foreground">
              Manage your sales leads and track conversion pipeline
            </p>
          </div>

          <Button :href="route('leads.create')">
            <Plus class="h-4 w-4 mr-2" />
            Add Lead
          </Button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20">
                <Users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Total Leads</p>
                <p class="text-2xl font-bold">{{ stats.total }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                <UserCheck class="h-6 w-6 text-green-600 dark:text-green-400" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Active</p>
                <p class="text-2xl font-bold">{{ stats.active }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/20">
                <TrendingUp class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Converted</p>
                <p class="text-2xl font-bold">{{ stats.converted }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Lost</p>
                <p class="text-2xl font-bold">{{ stats.lost }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center">
            <Filter class="h-5 w-5 mr-2" />
            Filters
          </CardTitle>
          <Button variant="outline" size="sm" @click="clearFilters">
            Clear
          </Button>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
              <label class="text-sm font-medium">Search</label>
              <div class="relative">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  v-model="search"
                  placeholder="Search leads..."
                  class="pl-10"
                />
              </div>
            </div>

            <div>
              <label class="text-sm font-medium">Status</label>
              <Select v-model="status">
                <SelectTrigger>
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div>
              <label class="text-sm font-medium">Owner</label>
              <Select v-model="ownerId">
                <SelectTrigger>
                  <SelectValue placeholder="Select owner" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All Owners</SelectItem>
                  <SelectItem v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div>
              <label class="text-sm font-medium">Rating</label>
              <Select v-model="rating">
                <SelectTrigger>
                  <SelectValue placeholder="Select rating" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="option in ratingOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div>
              <label class="text-sm font-medium">Source</label>
              <Select v-model="source">
                <SelectTrigger>
                  <SelectValue placeholder="Select source" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="option in sourceOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="flex items-end space-x-2">
              <div class="flex-1">
                <label class="text-sm font-medium">Sort</label>
                <Select v-model="sort" @update:model-value="applyFilters">
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in sortOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <Button @click="applyFilters">Apply</Button>
            </div>
          </div>

          <div class="flex flex-wrap gap-4 mt-4">
            <label class="flex items-center space-x-2 text-sm">
              <input
                type="checkbox"
                v-model="highPriority"
                class="rounded"
                @change="applyFilters"
              />
              <span>High Priority Only</span>
            </label>

            <label class="flex items-center space-x-2 text-sm">
              <input
                type="checkbox"
                v-model="needsFollowUp"
                class="rounded"
                @change="applyFilters"
              />
              <span>Needs Follow-up</span>
            </label>
          </div>
        </CardContent>
      </Card>

      <!-- Bulk Actions -->
      <div v-if="hasSelectedLeads" class="flex items-center justify-between bg-muted/50 rounded-lg p-4">
        <div class="flex items-center space-x-4">
          <span class="text-sm font-medium">
            {{ selectedLeads.length }} lead{{ selectedLeads.length > 1 ? 's' : '' }} selected
          </span>
          <Button variant="outline" size="sm" @click="toggleAllLeads">
            {{ hasSelectedLeads ? 'Clear Selection' : 'Select All' }}
          </Button>
        </div>

        <div class="flex items-center space-x-2">
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" size="sm">
                <MoreHorizontal class="h-4 w-4" />
                Bulk Actions
                <ChevronDown class="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuLabel>Bulk Actions</DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem @click="showBulkActionModal = true; bulkAction = 'assign'">
                Assign to User
              </DropdownMenuItem>
              <DropdownMenuItem @click="bulkAction = 'status'">
                Update Status
              </DropdownMenuItem>
              <DropdownMenuItem @click="bulkAction = 'delete'" class="text-red-600">
                Delete Selected
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

      <!-- Leads Table -->
      <Card>
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-12">
                  <input
                    type="checkbox"
                    :checked="hasSelectedLeads"
                    @change="toggleAllLeads"
                    class="rounded"
                  />
                </TableHead>
                <TableHead>Lead</TableHead>
                <TableHead>Company</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Owner</TableHead>
                <TableHead>Value</TableHead>
                <TableHead>Score</TableHead>
                <TableHead>Priority</TableHead>
                <TableHead>Follow-up</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="lead in leads.data" :key="lead.id">
                <TableCell>
                  <input
                    type="checkbox"
                    :checked="selectedLeads.includes(lead.id)"
                    @change="toggleLeadSelection(lead.id)"
                    class="rounded"
                  />
                </TableCell>
                <TableCell>
                  <div>
                    <div class="font-medium">{{ lead.first_name }} {{ lead.last_name }}</div>
                    <div v-if="lead.email" class="text-sm text-muted-foreground">{{ lead.email }}</div>
                    <div v-if="lead.phone" class="text-sm text-muted-foreground">{{ lead.phone }}</div>
                  </div>
                </TableCell>
                <TableCell>
                  <div v-if="lead.company_name">
                    <div class="font-medium">{{ lead.company_name }}</div>
                    <div v-if="lead.job_title" class="text-sm text-muted-foreground">{{ lead.job_title }}</div>
                  </div>
                  <div v-else class="text-muted-foreground">Individual</div>
                </TableCell>
                <TableCell>
                  <Badge :class="getStatusColor(lead.status)">
                    {{ lead.status }}
                  </Badge>
                  <Badge v-if="lead.rating" :class="getRatingColor(lead.rating)" class="ml-2">
                    {{ lead.rating }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div v-if="lead.owner" class="text-sm">
                    {{ lead.owner.name }}
                  </div>
                  <div v-else class="text-muted-foreground text-sm">Unassigned</div>
                </TableCell>
                <TableCell>
                  <div class="text-sm font-medium">
                    {{ formatValue(lead.estimated_value, lead.currency) }}
                  </div>
                </TableCell>
                <TableCell>
                  <div class="flex items-center space-x-1">
                    <div class="text-sm font-medium">{{ lead.score }}</div>
                    <Star v-if="lead.rating === 'hot'" class="h-3 w-3 text-yellow-500 fill-current" />
                  </div>
                </TableCell>
                <TableCell>
                  <div class="flex items-center space-x-2">
                    <div class="flex">
                      <div
                        v-for="i in 5"
                        :key="i"
                        class="w-2 h-2 rounded-full border-2"
                        :class="getPriorityColor(lead.priority)"
                        :class="{ 'bg-current': i <= lead.priority }"
                      />
                    </div>
                    <span class="text-xs text-muted-foreground">
                      {{ priorityLevels.find(p => p.value === lead.priority)?.label }}
                    </span>
                  </div>
                </TableCell>
                <TableCell>
                  <div v-if="lead.follow_up_date" class="text-sm">
                    {{ formatDate(lead.follow_up_date) }}
                  </div>
                  <div v-else class="text-muted-foreground text-sm">-</div>
                </TableCell>
                <TableCell class="text-right">
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" size="sm">
                        <MoreHorizontal class="h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuItem :href="route('leads.show', lead.id)">
                        View Details
                      </DropdownMenuItem>
                      <DropdownMenuItem :href="route('leads.edit', lead.id)">
                        <Edit class="h-4 w-4 mr-2" />
                        Edit
                      </DropdownMenuItem>
                      <DropdownMenuSeparator />
                      <DropdownMenuItem
                        v-if="!lead.customer"
                        @click="convertLead(lead)"
                        class="text-green-600"
                      >
                        <UserPlus class="h-4 w-4 mr-2" />
                        Convert
                      </DropdownMenuItem>
                      <DropdownMenuItem
                        @click="deleteLead(lead)"
                        class="text-red-600"
                      >
                        <Trash2 class="h-4 w-4 mr-2" />
                        Delete
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <!-- Empty State -->
          <div v-if="leads.data.length === 0" class="text-center py-12">
            <div class="text-muted-foreground">
              <Users class="mx-auto h-12 w-12 mb-4" />
              <h3 class="text-lg font-medium">No leads found</h3>
              <p class="mt-2">Get started by adding your first lead.</p>
              <Button :href="route('leads.create')" class="mt-4">
                <Plus class="h-4 w-4 mr-2" />
                Add Lead
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Bulk Action Modal -->
      <Dialog v-model:open="showBulkActionModal">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Bulk Action</DialogTitle>
            <DialogDescription>
              Perform bulk action on {{ selectedLeads.length }} selected lead{{ selectedLeads.length > 1 ? 's' : '' }}
            </DialogDescription>
          </DialogHeader>

          <div v-if="bulkAction === 'assign'" class="space-y-4">
            <div>
              <label class="text-sm font-medium">Assign to User</label>
              <Select v-model="assignToUser">
                <SelectTrigger>
                  <SelectValue placeholder="Select user" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" @click="showBulkActionModal = false">
              Cancel
            </Button>
            <Button @click="executeBulkAction" :disabled="processing">
              <Check class="h-4 w-4 mr-2" />
              {{ processing ? 'Processing...' : 'Execute' }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AuthenticatedLayout>
</template>