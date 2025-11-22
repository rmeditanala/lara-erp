<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
  Users,
  ShoppingCart,
  DollarSign,
  TrendingUp,
  Calendar,
  Activity,
  Mail,
  Building2,
  ArrowUp,
  ArrowDown,
  Minus
} from 'lucide-vue-next'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
  kpiData: {
    totalCustomers: number
    customerGrowth: number
    newCustomersThisMonth: number
    activeQuotes: number
    quoteGrowth: number
    totalRevenue: number
    revenueGrowth: number
    revenueThisMonth: number
    totalUsers: number
    activeUsers: number
  }
  recentActivity: Array<{
    id: number
    type: string
    title: string
    description: string
    status?: string
    customer?: string
    inviter?: string
    created_at: string
    icon: string
    color: string
  }>
  salesChartData: {
    months: string[]
    revenue: number[]
    quotes: number[]
  }
  customerGrowthData: {
    months: string[]
    customers: number[]
  }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
]

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount)
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getGrowthIcon = (growth: number) => {
  if (growth > 0) return ArrowUp
  if (growth < 0) return ArrowDown
  return Minus
}

const getGrowthColor = (growth: number) => {
  if (growth > 0) return 'text-green-600 dark:text-green-400'
  if (growth < 0) return 'text-red-600 dark:text-red-400'
  return 'text-gray-600 dark:text-gray-400'
}

const getIconComponent = (iconName: string) => {
  const icons: Record<string, any> = {
    Users,
    ShoppingCart,
    Mail,
    DollarSign,
    TrendingUp,
    Activity
  }
  return icons[iconName] || Activity
}

const getActivityIcon = (color: string) => {
  const colors: Record<string, string> = {
    blue: 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
    green: 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400',
    orange: 'bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-400',
  }
  return colors[color] || colors.blue
}
</script>

<template>
    <Head title="Executive Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-foreground">Executive Dashboard</h1>
                <p class="text-muted-foreground mt-1">Real-time insights into your business performance</p>
            </div>

            <!-- KPI Metrics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Customers -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            Total Customers
                        </CardTitle>
                        <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <Users class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-foreground">{{ props.kpiData.totalCustomers }}</div>
                        <div class="flex items-center text-xs text-muted-foreground">
                            <component :is="getGrowthIcon(props.kpiData.customerGrowth)"
                                      :class="['w-3 h-3 mr-1', getGrowthColor(props.kpiData.customerGrowth)]" />
                            <span :class="getGrowthColor(props.kpiData.customerGrowth)">
                                {{ Math.abs(props.kpiData.customerGrowth) }}% from last month
                            </span>
                            <span class="ml-2">({{ props.kpiData.newCustomersThisMonth }} new)</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Active Quotes -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            Active Quotes
                        </CardTitle>
                        <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                            <ShoppingCart class="w-4 h-4 text-green-600 dark:text-green-400" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-foreground">{{ props.kpiData.activeQuotes }}</div>
                        <div class="flex items-center text-xs text-muted-foreground">
                            <component :is="getGrowthIcon(props.kpiData.quoteGrowth)"
                                      :class="['w-3 h-3 mr-1', getGrowthColor(props.kpiData.quoteGrowth)]" />
                            <span :class="getGrowthColor(props.kpiData.quoteGrowth)">
                                {{ Math.abs(props.kpiData.quoteGrowth) }}% from last month
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Revenue -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            Total Revenue
                        </CardTitle>
                        <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                            <DollarSign class="w-4 h-4 text-yellow-600 dark:text-yellow-400" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-foreground">{{ formatCurrency(props.kpiData.totalRevenue) }}</div>
                        <div class="flex items-center text-xs text-muted-foreground">
                            <component :is="getGrowthIcon(props.kpiData.revenueGrowth)"
                                      :class="['w-3 h-3 mr-1', getGrowthColor(props.kpiData.revenueGrowth)]" />
                            <span :class="getGrowthColor(props.kpiData.revenueGrowth)">
                                {{ Math.abs(props.kpiData.revenueGrowth) }}% from last month
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Active Users -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            Team Members
                        </CardTitle>
                        <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                            <Building2 class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-foreground">{{ props.kpiData.activeUsers }}/{{ props.kpiData.totalUsers }}</div>
                        <div class="text-xs text-muted-foreground">
                            Active users this month
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts and Activity Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Sales Chart -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <TrendingUp class="w-5 h-5" />
                            Sales Performance
                        </CardTitle>
                        <CardDescription>
                            Revenue and quote volume over the last 6 months
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-6">
                            <!-- Simple chart representation -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Monthly Revenue</span>
                                    <span class="font-medium">{{ formatCurrency(Math.max(...props.salesChartData.revenue)) }}</span>
                                </div>
                                <!-- Revenue bars -->
                                <div class="flex items-end justify-between h-32 gap-2">
                                    <div v-for="(revenue, index) in props.salesChartData.revenue" :key="index"
                                         class="flex-1 bg-blue-500 dark:bg-blue-600 rounded-t hover:bg-blue-600 dark:hover:bg-blue-500 transition-colors relative group"
                                         :style="{ height: `${(revenue / Math.max(...props.salesChartData.revenue)) * 100}%` }">
                                        <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 dark:bg-gray-200 text-gray-200 dark:text-gray-800 px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                            {{ formatCurrency(revenue) }}
                                        </div>
                                    </div>
                                </div>
                                <!-- Month labels -->
                                <div class="flex justify-between text-xs text-muted-foreground">
                                    <span v-for="month in props.salesChartData.months" :key="month">{{ month }}</span>
                                </div>
                            </div>

                            <!-- Quote count bars -->
                            <div class="space-y-4 pt-4 border-t border-border">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Quotes Created</span>
                                    <span class="font-medium">{{ Math.max(...props.salesChartData.quotes) }} quotes</span>
                                </div>
                                <div class="flex items-end justify-between h-20 gap-2">
                                    <div v-for="(quotes, index) in props.salesChartData.quotes" :key="index"
                                         class="flex-1 bg-green-500 dark:bg-green-600 rounded-t hover:bg-green-600 dark:hover:bg-green-500 transition-colors relative group"
                                         :style="{ height: `${(quotes / Math.max(...props.salesChartData.quotes)) * 100}%` }">
                                        <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 dark:bg-gray-200 text-gray-200 dark:text-gray-800 px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                            {{ quotes }} quotes
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Customer Growth -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Users class="w-5 h-5" />
                            Customer Growth
                        </CardTitle>
                        <CardDescription>
                            New customers acquired monthly
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">New Customers</span>
                                <span class="font-medium">{{ Math.max(...props.customerGrowthData.customers) }}</span>
                            </div>
                            <div class="flex items-end justify-between h-32 gap-2">
                                <div v-for="(customers, index) in props.customerGrowthData.customers" :key="index"
                                     class="flex-1 bg-purple-500 dark:bg-purple-600 rounded-t hover:bg-purple-600 dark:hover:bg-purple-500 transition-colors relative group"
                                     :style="{ height: `${(customers / Math.max(...props.customerGrowthData.customers)) * 100}%` }">
                                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 dark:bg-gray-200 text-gray-200 dark:text-gray-800 px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                        {{ customers }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs text-muted-foreground">
                                <span v-for="month in props.customerGrowthData.months" :key="month">{{ month.slice(0, 3) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Activity -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Activity class="w-5 h-5" />
                        Recent Activity
                    </CardTitle>
                    <CardDescription>
                        Latest updates across your organization
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-for="activity in props.recentActivity" :key="activity.id"
                             class="flex items-start gap-4 p-3 rounded-lg border border-border hover:bg-muted/50 transition-colors">
                            <div :class="['p-2 rounded-lg', getActivityIcon(activity.color)]">
                                <component :is="getIconComponent(activity.icon)" class="w-4 h-4" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-foreground">{{ activity.title }}</h4>
                                <p class="text-sm text-muted-foreground mt-1">{{ activity.description }}</p>
                                <div class="flex items-center gap-4 mt-2 text-xs text-muted-foreground">
                                    <span>{{ formatDate(activity.created_at) }}</span>
                                    <Badge v-if="activity.status" variant="secondary" class="text-xs">
                                        {{ activity.status }}
                                    </Badge>
                                    <span v-if="activity.customer">{{ activity.customer }}</span>
                                    <span v-if="activity.inviter">by {{ activity.inviter }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="props.recentActivity.length === 0" class="text-center py-8">
                        <Activity class="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                        <h3 class="text-lg font-medium text-foreground mb-2">No recent activity</h3>
                        <p class="text-muted-foreground">Start by adding customers, creating quotes, or inviting team members</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>