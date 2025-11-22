<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { ChevronDown } from 'lucide-vue-next'

interface Option {
  value: string
  label: string
  disabled?: boolean
}

const props = withDefaults(defineProps<{
  modelValue?: string
  placeholder?: string
  disabled?: boolean
  required?: boolean
}>(), {
  placeholder: 'Select an option',
  disabled: false,
  required: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const isOpen = ref(false)
const options = ref<Option[]>([])

const selectedOption = computed(() => {
  return options.value.find(option => option.value === props.modelValue)
})

const selectOption = (option: Option) => {
  if (option.disabled) return
  emit('update:modelValue', option.value)
  isOpen.value = false
}

const toggleDropdown = () => {
  if (!props.disabled) {
    isOpen.value = !isOpen.value
  }
}

const closeDropdown = () => {
  isOpen.value = false
}

// Close dropdown when clicking outside
const handleClickOutside = (event: Event) => {
  const target = event.target as HTMLElement
  if (!target.closest('.select-container')) {
    isOpen.value = false
  }
}

// Add and remove event listener
watch(isOpen, (newValue) => {
  if (newValue) {
    document.addEventListener('click', handleClickOutside)
  } else {
    document.removeEventListener('click', handleClickOutside)
  }
})

// Expose methods for child components
defineExpose({
  options,
  isOpen,
  selectOption,
  closeDropdown,
  toggleDropdown
})
</script>

<template>
  <div class="select-container relative">
    <slot name="trigger" :selected-option="selectedOption" :is-open="isOpen" :toggle="toggleDropdown">
      <button
        type="button"
        :disabled="disabled"
        @click="toggleDropdown"
        class="flex h-10 w-full items-center justify-between rounded-md border border-border bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        :class="{ 'ring-2 ring-ring ring-offset-2': isOpen }"
      >
        <span :class="{ 'text-muted-foreground': !selectedOption }">
          {{ selectedOption ? selectedOption.label : placeholder }}
        </span>
        <ChevronDown
          class="h-4 w-4 opacity-50 transition-transform"
          :class="{ 'rotate-180': isOpen }"
        />
      </button>
    </slot>

    <slot name="content" :options="options" :select-option="selectOption" :is-open="isOpen">
      <div
        v-if="isOpen"
        class="absolute top-full left-0 right-0 z-50 mt-1 max-h-60 overflow-auto rounded-md border border-border bg-popover text-popover-foreground shadow-md"
      >
        <div v-if="options.length === 0" class="p-2 text-sm text-muted-foreground">
          No options available
        </div>
        <div v-else>
          <div
            v-for="option in options"
            :key="option.value"
            @click="selectOption(option)"
            class="relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 px-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground"
            :class="{
              'opacity-50 cursor-not-allowed': option.disabled,
              'bg-accent text-accent-foreground': modelValue === option.value
            }"
          >
            <span>{{ option.label }}</span>
          </div>
        </div>
      </div>
    </slot>
  </div>
</template>