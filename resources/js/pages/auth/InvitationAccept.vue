<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { InputError } from '@/components/ui/input-error'
import { Badge } from '@/components/ui/badge'
import {
  Building2,
  Mail,
  Users,
  CheckCircle,
  AlertCircle,
  User,
  Phone,
  Briefcase
} from 'lucide-vue-next'

const props = defineProps<{
  invitation: {
    id: number
    email: string
    role: string
    company: {
      name: string
      email?: string
      phone?: string
      address?: string
    }
    inviter: {
      name: string
      email: string
    }
    expires_at: string
  }
}>()

const form = useForm({
  name: '',
  password: '',
  password_confirmation: '',
  phone: '',
  job_title: '',
})

const submit = () => {
  form.post(`/invitation/${props.invitation.id}/accept`)
}

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
</script>

<template>
    <Head title="Accept Invitation" />

    <AuthLayout>
        <div class="mx-auto max-w-4xl">
            <!-- Invitation Details Card -->
            <div class="bg-card rounded-xl shadow-sm border border-border p-8 mb-6">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <Mail class="w-8 h-8" />
                    </div>
                    <h1 class="text-3xl font-bold text-foreground mb-2">You've Been Invited!</h1>
                    <p class="text-muted-foreground text-lg">
                        You've been invited to join {{ invitation.company.name }} as a team member
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Company Information -->
                    <div>
                        <h3 class="font-semibold text-foreground mb-4 flex items-center gap-2">
                            <Building2 class="w-5 h-5" />
                            Company Details
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-foreground">Company Name</p>
                                <p class="text-foreground">{{ invitation.company.name }}</p>
                            </div>
                            <div v-if="invitation.company.email">
                                <p class="text-sm font-medium text-foreground">Company Email</p>
                                <p class="text-foreground">{{ invitation.company.email }}</p>
                            </div>
                            <div v-if="invitation.company.phone">
                                <p class="text-sm font-medium text-foreground">Company Phone</p>
                                <p class="text-foreground">{{ invitation.company.phone }}</p>
                            </div>
                            <div v-if="invitation.company.address">
                                <p class="text-sm font-medium text-foreground">Address</p>
                                <p class="text-foreground">{{ invitation.company.address }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Invitation Details -->
                    <div>
                        <h3 class="font-semibold text-foreground mb-4 flex items-center gap-2">
                            <Users class="w-5 h-5" />
                            Invitation Details
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-foreground">Your Role</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <Badge variant="secondary">{{ getRoleDisplayName(invitation.role) }}</Badge>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-foreground">Invited By</p>
                                <p class="text-foreground">{{ invitation.inviter.name }} ({{ invitation.inviter.email }})</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-foreground">Invitation Email</p>
                                <p class="text-foreground">{{ invitation.email }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-foreground">Expires</p>
                                <p class="text-foreground">{{ new Date(invitation.expires_at).toLocaleDateString() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Creation Form -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <User class="w-5 h-5" />
                        Create Your Account
                    </CardTitle>
                    <CardDescription>
                        Complete your profile to accept the invitation and join the team.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Personal Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-foreground mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <Label for="name">Full Name *</Label>
                                    <Input
                                      id="name"
                                      v-model="form.name"
                                      type="text"
                                      required
                                      :disabled="form.processing"
                                      placeholder="John Doe"
                                      class="mt-1"
                                    />
                                    <InputError :message="form.errors.name" class="mt-1" />
                                </div>

                                <div>
                                    <Label for="phone">Phone Number</Label>
                                    <Input
                                      id="phone"
                                      v-model="form.phone"
                                      type="tel"
                                      :disabled="form.processing"
                                      placeholder="+1 (555) 123-4567"
                                      class="mt-1"
                                    />
                                    <InputError :message="form.errors.phone" class="mt-1" />
                                </div>

                                <div>
                                    <Label for="job_title">Job Title</Label>
                                    <Input
                                      id="job_title"
                                      v-model="form.job_title"
                                      type="text"
                                      :disabled="form.processing"
                                      placeholder="Sales Manager, Developer, etc."
                                      class="mt-1"
                                    />
                                    <InputError :message="form.errors.job_title" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Password Setup -->
                        <div>
                            <h3 class="text-lg font-semibold text-foreground mb-4">Set Your Password</h3>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <Label for="password">Password *</Label>
                                    <Input
                                      id="password"
                                      v-model="form.password"
                                      type="password"
                                      required
                                      :disabled="form.processing"
                                      placeholder="Create a strong password"
                                      class="mt-1"
                                    />
                                    <InputError :message="form.errors.password" class="mt-1" />
                                    <p class="text-sm text-muted-foreground mt-1">
                                        Must be at least 8 characters long
                                    </p>
                                </div>

                                <div>
                                    <Label for="password_confirmation">Confirm Password *</Label>
                                    <Input
                                      id="password_confirmation"
                                      v-model="form.password_confirmation"
                                      type="password"
                                      required
                                      :disabled="form.processing"
                                      placeholder="Confirm your password"
                                      class="mt-1"
                                    />
                                    <InputError :message="form.errors.password_confirmation" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Terms Confirmation -->
                        <div class="bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <CheckCircle class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                                <div>
                                    <h4 class="font-medium text-blue-900 dark:text-blue-100">Account Setup</h4>
                                    <p class="text-sm text-blue-800 dark:text-blue-200 mt-1">
                                        By creating this account, you agree to join {{ invitation.company.name }} and will have access based on your assigned role. Your email will be automatically verified since you were invited directly.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center gap-3">
                            <Button
                              type="submit"
                              class="w-full"
                              :disabled="form.processing"
                              size="lg"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Creating Account...' : 'Accept Invitation & Create Account' }}
                            </Button>
                            <Link href="/login">
                                <Button variant="outline" size="lg" class="w-full" :disabled="form.processing">
                                    Cancel
                                </Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthLayout>
</template>