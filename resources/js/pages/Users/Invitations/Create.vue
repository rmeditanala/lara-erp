<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { InputError } from '@/components/ui/input-error'
import {
  Users,
  Mail,
  ArrowLeft,
  Shield,
  Info
} from 'lucide-vue-next'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
  availableRoles: Record<string, string>
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
    {
        title: 'Invite User',
        href: '/users/invitations/create',
    },
]

const form = useForm({
  email: '',
  role: '',
})

const submit = () => {
  form.post('/users/invitations')
}

const getRoleDescription = (role: string): string => {
  const descriptions: Record<string, string> = {
    'company-owner': 'Full access to all company features and settings',
    'admin': 'Manage users, settings, and most company features',
    'manager': 'Manage team, customers, quotes, and orders',
    'sales-rep': 'Manage customers, quotes, and sales activities',
    'employee': 'Basic access to company operations',
    'read-only': 'View-only access to company information'
  }
  return descriptions[role] || ''
}
</script>

<template>
    <Head title="Invite User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link href="/users/invitations">
                    <Button variant="outline" size="sm">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Invitations
                    </Button>
                </Link>
                <div>
                    <h1 class="text-3xl font-bold text-foreground">Invite User</h1>
                    <p class="text-muted-foreground mt-1">Send an invitation to join your team</p>
                </div>
            </div>

            <!-- Form -->
            <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Mail class="w-5 h-5" />
                            Send Invitation
                        </CardTitle>
                        <CardDescription>
                            The invited user will receive an email with a link to create their account.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Email Field -->
                            <div>
                                <Label for="email">Email Address *</Label>
                                <Input
                                  id="email"
                                  v-model="form.email"
                                  type="email"
                                  required
                                  :disabled="form.processing"
                                  placeholder="colleague@company.com"
                                  class="mt-1"
                                />
                                <InputError :message="form.errors.email" class="mt-1" />
                                <p class="text-sm text-muted-foreground mt-1">
                                    This email address will be used to create the user account.
                                </p>
                            </div>

                            <!-- Role Field -->
                            <div>
                                <Label for="role">Role *</Label>
                                <select
                                  id="role"
                                  v-model="form.role"
                                  :disabled="form.processing"
                                  required
                                  class="flex h-10 w-full items-center justify-between rounded-md border border-border bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 mt-1"
                                >
                                    <option value="" disabled>Select a role</option>
                                    <option
                                      v-for="(name, role) in props.availableRoles"
                                      :key="role"
                                      :value="role"
                                    >
                                        {{ name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.role" class="mt-1" />

                                <!-- Role Description -->
                                <div v-if="form.role" class="mt-3 p-3 bg-muted/50 rounded-lg">
                                    <div class="flex items-start gap-2">
                                        <Info class="w-4 h-4 text-muted-foreground mt-0.5" />
                                        <div>
                                            <h4 class="font-medium text-foreground">{{ props.availableRoles[form.role] }}</h4>
                                            <p class="text-sm text-muted-foreground mt-1">
                                                {{ getRoleDescription(form.role) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Information -->
                            <div class="bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <Shield class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                                    <div>
                                        <h4 class="font-medium text-blue-900 dark:text-blue-100">Security Information</h4>
                                        <ul class="text-sm text-blue-800 dark:text-blue-200 mt-2 space-y-1">
                                            <li>• Invitations expire after 7 days for security</li>
                                            <li>• Users must verify their email to activate the account</li>
                                            <li>• You can revoke pending invitations at any time</li>
                                            <li>• Each user can only have one active role</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center gap-3">
                                <Button
                                  type="submit"
                                  :disabled="form.processing"
                                  size="lg"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ form.processing ? 'Sending Invitation...' : 'Send Invitation' }}
                                </Button>
                                <Link href="/users/invitations">
                                    <Button variant="outline" size="lg" :disabled="form.processing">
                                        Cancel
                                    </Button>
                                </Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>