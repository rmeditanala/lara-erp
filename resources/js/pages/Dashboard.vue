<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
  Users,
  Building2,
  TrendingUp,
  Package,
  ShoppingCart,
  DollarSign,
  Calendar,
  Plus,
  ArrowRight
} from 'lucide-vue-next'
import { type BreadcrumbItem } from '@/types'

const page = usePage()
const user = page.props.auth.user
const company = page.props.auth.user.company

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
]

const isNewCompany = company && company.subscription_status === 'trial' && company.trial_ends_at

const quickActions = [
    {
        title: 'Add Customer',
        description: 'Create your first customer record',
        icon: Users,
        href: '/customers/create',
        color: 'text-blue-600',
        bgColor: 'bg-blue-100'
    },
    {
        title: 'Create Quote',
        description: 'Send a quote to a customer',
        icon: ShoppingCart,
        href: '/quotes/create',
        color: 'text-green-600',
        bgColor: 'bg-green-100'
    },
    {
        title: 'Add Product',
        description: 'Add products to your inventory',
        icon: Package,
        href: '/products/create',
        color: 'text-purple-600',
        bgColor: 'bg-purple-100'
    },
    {
        title: 'Invite Team',
        description: 'Add team members to your company',
        icon: Users,
        href: '/users/invitations/create',
        color: 'text-orange-600',
        bgColor: 'bg-orange-100'
    }
]

const stats = [
    {
        title: 'Total Customers',
        value: '0',
        change: '+0%',
        icon: Users,
        color: 'text-blue-600',
        bgColor: 'bg-blue-100'
    },
    {
        title: 'Active Quotes',
        value: '0',
        change: '+0%',
        icon: ShoppingCart,
        color: 'text-green-600',
        bgColor: 'bg-green-100'
    },
    {
        title: 'Total Orders',
        value: '0',
        change: '+0%',
        icon: Package,
        color: 'text-purple-600',
        bgColor: 'bg-purple-100'
    },
    {
        title: 'Revenue',
        value: '$0',
        change: '+0%',
        icon: DollarSign,
        color: 'text-yellow-600',
        bgColor: 'bg-yellow-100'
    }
]
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Welcome Section -->
            <div v-if="isNewCompany" class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold mb-2">Welcome to {{ company?.name || 'Your Company' }}!</h1>
                        <p class="text-blue-100">
                            Your 30-day free trial has started. Get started by adding your first customer or exploring the features below.
                        </p>
                        <div class="mt-4 flex items-center gap-4">
                            <Badge class="bg-white/20 text-white border-white/30">
                                <Calendar class="w-4 h-4 mr-1" />
                                Trial ends: {{ company?.trial_ends_at ? new Date(company.trial_ends_at).toLocaleDateString() : '30 days from now' }}
                            </Badge>
                            <Link href="/company/subscription">
                                <Button variant="secondary" size="sm" class="bg-white/20 hover:bg-white/30 text-white border-white/30">
                                    Upgrade Plan
                                </Button>
                            </Link>
                        </div>
                    </div>
                    <Building2 class="w-16 h-16 text-white/50" />
                </div>
            </div>

            <div v-else class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-foreground">Welcome back, {{ user.name }}!</h1>
                    <p class="text-muted-foreground mt-1">Here's what's happening with {{ company?.name || 'your company' }} today.</p>
                </div>
                <div class="flex gap-3">
                    <Link href="/company/settings">
                        <Button variant="outline" size="sm">
                            <Building2 class="w-4 h-4 mr-2" />
                            Company Settings
                        </Button>
                    </Link>
                    <Button size="sm">
                        <Plus class="w-4 h-4 mr-2" />
                        Quick Add
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <Card v-for="stat in stats" :key="stat.title" class="relative overflow-hidden">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            {{ stat.title }}
                        </CardTitle>
                        <div :class="[stat.bgColor, 'p-2 rounded-lg']">
                            <component :is="stat.icon" :class="[stat.color, 'w-4 h-4']" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-foreground">{{ stat.value }}</div>
                        <p class="text-xs text-muted-foreground">
                            <span class="text-green-600 dark:text-green-400">{{ stat.change }}</span> from last month
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions & Getting Started -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Quick Actions -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <TrendingUp class="w-5 h-5" />
                            Quick Actions
                        </CardTitle>
                        <CardDescription>
                            Get started with these common tasks
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="action in quickActions" :key="action.title" class="group">
                                <Link :href="action.href" class="block">
                                    <div class="flex items-start gap-3 p-3 rounded-lg border border-border hover:border-primary/50 hover:shadow-sm transition-all">
                                        <div :class="[action.bgColor, 'p-2 rounded-lg group-hover:scale-105 transition-transform']">
                                            <component :is="action.icon" :class="[action.color, 'w-4 h-4']" />
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-medium text-foreground group-hover:text-primary transition-colors">
                                                {{ action.title }}
                                            </h3>
                                            <p class="text-sm text-muted-foreground">{{ action.description }}</p>
                                        </div>
                                        <ArrowRight class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors mt-1" />
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Activity / Getting Started -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Calendar class="w-5 h-5" />
                            Getting Started
                        </CardTitle>
                        <CardDescription>
                            Follow these steps to set up your ERP
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 p-3 rounded-lg bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800">
                                <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-medium">
                                    ✓
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-green-900 dark:text-green-100">Company Registered</h3>
                                    <p class="text-sm text-green-700 dark:text-green-300">Your company has been successfully set up</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 p-3 rounded-lg border border-border">
                                <div class="w-8 h-8 bg-muted text-muted-foreground rounded-full flex items-center justify-center text-sm font-medium">
                                    2
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-foreground">Add Your First Customer</h3>
                                    <p class="text-sm text-muted-foreground">Start building your customer database</p>
                                </div>
                                <Link href="/customers/create">
                                    <Button size="sm" variant="outline">Start</Button>
                                </Link>
                            </div>

                            <div class="flex items-center gap-3 p-3 rounded-lg border border-border">
                                <div class="w-8 h-8 bg-muted text-muted-foreground rounded-full flex items-center justify-center text-sm font-medium">
                                    3
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-foreground">Create Your First Quote</h3>
                                    <p class="text-sm text-muted-foreground">Send professional quotes to customers</p>
                                </div>
                                <Button size="sm" variant="outline" disabled>Coming Soon</Button>
                            </div>

                            <div class="flex items-center gap-3 p-3 rounded-lg border border-border">
                                <div class="w-8 h-8 bg-muted text-muted-foreground rounded-full flex items-center justify-center text-sm font-medium">
                                    4
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-foreground">Invite Team Members</h3>
                                    <p class="text-sm text-muted-foreground">Collaborate with your team</p>
                                </div>
                                <Link href="/users/invitations/create">
                                    <Button size="sm" variant="outline">Start</Button>
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Resources Section -->
            <Card>
                <CardHeader>
                    <CardTitle>Resources & Help</CardTitle>
                    <CardDescription>
                        Learn how to make the most of your ERP system
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Link href="#" class="block group">
                            <div class="p-4 border border-border rounded-lg hover:border-primary/50 hover:shadow-sm transition-all">
                                <div class="w-10 h-10 bg-primary/10 text-primary rounded-lg flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                                    📚
                                </div>
                                <h3 class="font-medium text-foreground group-hover:text-primary transition-colors">Documentation</h3>
                                <p class="text-sm text-muted-foreground mt-1">Browse our comprehensive guides</p>
                            </div>
                        </Link>

                        <Link href="#" class="block group">
                            <div class="p-4 border border-border rounded-lg hover:border-primary/50 hover:shadow-sm transition-all">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-lg flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                                    🎥
                                </div>
                                <h3 class="font-medium text-foreground group-hover:text-primary transition-colors">Video Tutorials</h3>
                                <p class="text-sm text-muted-foreground mt-1">Watch step-by-step video guides</p>
                            </div>
                        </Link>

                        <Link href="#" class="block group">
                            <div class="p-4 border border-border rounded-lg hover:border-primary/50 hover:shadow-sm transition-all">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400 rounded-lg flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                                    💬
                                </div>
                                <h3 class="font-medium text-foreground group-hover:text-primary transition-colors">Support Center</h3>
                                <p class="text-sm text-muted-foreground mt-1">Get help from our support team</p>
                            </div>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
