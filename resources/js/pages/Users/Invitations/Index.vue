<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
  Mail,
  Users,
  Plus,
  Calendar,
  Clock,
  CheckCircle,
  XCircle,
  MoreHorizontal,
  Trash2
} from 'lucide-vue-next'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
  invitations: {
    data: Array<{
      id: number
      email: string
      role: string
      created_at: string
      expires_at: string
      accepted_at: string | null
      inviter: {
        name: string
        email: string
      }
    }>
    links: Array<{
      url: string | null
      label: string
      active: boolean
    }>
  }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'User Management',
        href: '#',
    },
    {
        title: 'Invitations',
        href: '/users/invitations',
    },
]

const getRoleDisplayName = (role: string): string => {
  const roleNames: Record<string, string> = {
    'company-owner': 'Company Owner',
    'admin': 'Administrator',
    'manager': 'Manager',
    'sales-rep': 'Sales Representative',
    'employee': 'Employee',
    'read-only': 'Read Only'
  }
  return roleNames[role] || role
}

const getStatusBadge = (invitation: any) => {
  if (invitation.accepted_at) {
    return { variant: 'default', text: 'Accepted', icon: CheckCircle }
  }

  const isExpired = new Date(invitation.expires_at) < new Date()
  if (isExpired) {
    return { variant: 'destructive', text: 'Expired', icon: XCircle }
  }

  return { variant: 'secondary', text: 'Pending', icon: Clock }
}
</script>

<template>
    <Head title="User Invitations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-foreground">User Invitations</h1>
                    <p class="text-muted-foreground mt-1">Manage invitations for your team members</p>
                </div>
                <Link href="/users/invitations/create">
                    <Button>
                        <Plus class="w-4 h-4 mr-2" />
                        Invite User
                    </Button>
                </Link>
            </div>

            <!-- Invitations List -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Users class="w-5 h-5" />
                        Invitation History
                    </CardTitle>
                    <CardDescription>
                        Track all user invitations sent to your team
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-for="invitation in props.invitations.data" :key="invitation.id"
                             class="flex items-center justify-between p-4 border border-border rounded-lg hover:bg-muted/50 transition-colors">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-primary/10 text-primary rounded-full flex items-center justify-center">
                                    <Mail class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="font-medium text-foreground">{{ invitation.email }}</h3>
                                    <div class="flex items-center gap-4 mt-1">
                                        <span class="text-sm text-muted-foreground">
                                            Role: {{ getRoleDisplayName(invitation.role) }}
                                        </span>
                                        <span class="text-sm text-muted-foreground">
                                            Invited by {{ invitation.inviter.name }}
                                        </span>
                                        <span class="text-sm text-muted-foreground">
                                            {{ new Date(invitation.created_at).toLocaleDateString() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <component :is="getStatusBadge(invitation).icon" class="w-4 h-4" />
                                    <Badge :variant="getStatusBadge(invitation).variant">
                                        {{ getStatusBadge(invitation).text }}
                                    </Badge>
                                </div>
                                <div v-if="!invitation.accepted_at" class="flex items-center gap-2">
                                    <span class="text-sm text-muted-foreground">
                                        Expires {{ new Date(invitation.expires_at).toLocaleDateString() }}
                                    </span>
                                    <Button variant="outline" size="sm" @click="deleteInvitation(invitation.id)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="props.invitations.data.length === 0" class="text-center py-12">
                        <Mail class="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                        <h3 class="text-lg font-medium text-foreground mb-2">No invitations sent yet</h3>
                        <p class="text-muted-foreground mb-6">Start by inviting your first team member</p>
                        <Link href="/users/invitations/create">
                            <Button>
                                <Plus class="w-4 h-4 mr-2" />
                                Send First Invitation
                            </Button>
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.invitations.links.length > 3" class="mt-6 flex justify-center">
                        <nav class="flex items-center space-x-1">
                            <template v-for="(link, index) in props.invitations.links" :key="index">
                                <Link v-if="link.url"
                                      :href="link.url"
                                      :class="[
                                          'px-3 py-2 text-sm rounded-md transition-colors',
                                          link.active
                                              ? 'bg-primary text-primary-foreground'
                                              : 'text-muted-foreground hover:text-foreground hover:bg-muted'
                                      ]"
                                      v-html="link.label" />
                                <span v-else
                                      class="px-3 py-2 text-sm text-muted-foreground"
                                      v-html="link.label" />
                            </template>
                        </nav>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script lang="ts">
import { useForm } from '@inertiajs/vue3'

export default {
  methods: {
    deleteInvitation(id: number) {
      if (confirm('Are you sure you want to delete this invitation?')) {
        const form = useForm({})
        form.delete(`/users/invitations/${id}`)
      }
    }
  }
}
</script>