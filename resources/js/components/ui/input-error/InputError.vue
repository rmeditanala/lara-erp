<template>
  <div v-if="message" class="text-sm text-destructive">
    {{ message }}
  </div>
</template>

<script setup lang="ts">
interface Props {
  message?: string
}

withDefaults(defineProps<Props>(), {
  message: '',
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'InputError',
  inheritAttrs: false
})
</script>