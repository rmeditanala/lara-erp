<template>
  <AuthLayout>
    <div class="mx-auto max-w-2xl">
      <div class="bg-card rounded-xl shadow-sm border border-border p-8">
        <div class="mb-8 text-center">
          <h1 class="text-3xl font-bold text-foreground">Start Your Free Trial</h1>
          <p class="mt-2 text-muted-foreground">Register your company and get 30 days free access to all features</p>
        </div>

        <form @submit.prevent="submit" class="space-y-8">
          <!-- Company Information Section -->
          <div>
            <h2 class="text-lg font-semibold text-foreground mb-4">Company Information</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <div class="sm:col-span-2">
                <Label for="company_name">Company Name *</Label>
                <Input
                  id="company_name"
                  v-model="form.company_name"
                  type="text"
                  required
                  :disabled="form.processing"
                  placeholder="Acme Corporation"
                  class="mt-1"
                />
                <InputError :message="form.errors.company_name" class="mt-1" />
              </div>

              <div class="sm:col-span-2">
                <Label for="company_email">Company Email *</Label>
                <Input
                  id="company_email"
                  v-model="form.company_email"
                  type="email"
                  required
                  :disabled="form.processing"
                  placeholder="contact@company.com"
                  class="mt-1"
                />
                <InputError :message="form.errors.company_email" class="mt-1" />
              </div>

              <div>
                <Label for="company_phone">Phone Number</Label>
                <Input
                  id="company_phone"
                  v-model="form.company_phone"
                  type="tel"
                  :disabled="form.processing"
                  placeholder="+1 (555) 123-4567"
                  class="mt-1"
                />
                <InputError :message="form.errors.company_phone" class="mt-1" />
              </div>

              <div>
                <Label for="company_domain">Domain (Optional)</Label>
                <Input
                  id="company_domain"
                  v-model="form.company_domain"
                  type="text"
                  :disabled="form.processing"
                  placeholder="company-name"
                  class="mt-1"
                />
                <InputError :message="form.errors.company_domain" class="mt-1" />
              </div>

              <div class="sm:col-span-2">
                <Label for="company_address">Address</Label>
                <textarea
                  id="company_address"
                  v-model="form.company_address"
                  :disabled="form.processing"
                  placeholder="123 Business St, Suite 100"
                  rows="2"
                  class="mt-1 flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                ></textarea>
                <InputError :message="form.errors.company_address" class="mt-1" />
              </div>

              <div>
                <Label for="company_city">City</Label>
                <Input
                  id="company_city"
                  v-model="form.company_city"
                  type="text"
                  :disabled="form.processing"
                  placeholder="New York"
                  class="mt-1"
                />
                <InputError :message="form.errors.company_city" class="mt-1" />
              </div>

              <div>
                <Label for="company_country">Country</Label>
                <Input
                  id="company_country"
                  v-model="form.company_country"
                  type="text"
                  :disabled="form.processing"
                  placeholder="United States"
                  class="mt-1"
                />
                <InputError :message="form.errors.company_country" class="mt-1" />
              </div>
            </div>
          </div>

          <!-- Admin User Information Section -->
          <div>
            <h2 class="text-lg font-semibold text-foreground mb-4">Your Information (Company Owner)</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <div class="sm:col-span-2">
                <Label for="user_name">Full Name *</Label>
                <Input
                  id="user_name"
                  v-model="form.user_name"
                  type="text"
                  required
                  :disabled="form.processing"
                  placeholder="John Doe"
                  class="mt-1"
                />
                <InputError :message="form.errors.user_name" class="mt-1" />
              </div>

              <div class="sm:col-span-2">
                <Label for="user_email">Email Address *</Label>
                <Input
                  id="user_email"
                  v-model="form.user_email"
                  type="email"
                  required
                  :disabled="form.processing"
                  placeholder="john.doe@company.com"
                  class="mt-1"
                />
                <InputError :message="form.errors.user_email" class="mt-1" />
              </div>

              <div>
                <Label for="user_phone">Phone Number</Label>
                <Input
                  id="user_phone"
                  v-model="form.user_phone"
                  type="tel"
                  :disabled="form.processing"
                  placeholder="+1 (555) 123-4567"
                  class="mt-1"
                />
                <InputError :message="form.errors.user_phone" class="mt-1" />
              </div>

              <div>
                <Label for="job_title">Job Title</Label>
                <Input
                  id="job_title"
                  v-model="form.job_title"
                  type="text"
                  :disabled="form.processing"
                  placeholder="CEO, Founder, etc."
                  class="mt-1"
                />
                <InputError :message="form.errors.job_title" class="mt-1" />
              </div>

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

          <!-- Terms and Conditions -->
          <div>
            <div class="flex items-start">
              <Checkbox
                id="terms_accepted"
                v-model:checked="form.terms_accepted"
                :disabled="form.processing"
                required
                class="mt-1"
              />
              <div class="ml-3">
                <Label for="terms_accepted" class="text-sm text-gray-600">
                  I agree to the
                  <a href="/terms" target="_blank" class="text-blue-600 hover:text-blue-500 underline">Terms of Service</a>
                  and
                  <a href="/privacy" target="_blank" class="text-blue-600 hover:text-blue-500 underline">Privacy Policy</a>
                </Label>
                <InputError :message="form.errors.terms_accepted" class="mt-1" />
              </div>
            </div>
          </div>

          <!-- Trial Information -->
          <div class="bg-primary/10 border border-primary/20 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-primary" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-primary">30-Day Free Trial</h3>
                <div class="mt-1 text-sm text-foreground">
                  No credit card required. Full access to all features during your trial period.
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div>
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
              {{ form.processing ? 'Creating Your Account...' : 'Start Free Trial' }}
            </Button>
          </div>

          <div class="text-center">
            <p class="text-sm text-muted-foreground">
              Already have an account?
              <Link href="/login" class="font-medium text-primary hover:text-primary/80">
                Sign in here
              </Link>
            </p>
          </div>
        </form>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { InputError } from '@/components/ui/input-error'

const form = useForm({
  // Company Information
  company_name: '',
  company_email: '',
  company_phone: '',
  company_address: '',
  company_city: '',
  company_country: '',
  company_domain: '',

  // User Information
  user_name: '',
  user_email: '',
  user_phone: '',
  job_title: '',
  password: '',
  password_confirmation: '',

  // Terms
  terms_accepted: false,
})

const submit = () => {
  form.post('/company/register', {
    onFinish: () => {
      form.reset('password', 'password_confirmation')
    },
  })
}
</script>