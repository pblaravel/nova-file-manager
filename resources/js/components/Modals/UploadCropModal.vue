<template>
  <InputModal :name="name" :on-submit="submit" :title="__('NovaFileManager.uploadCropTitle')">
    <template v-slot:inputs>
      <div class="rounded-md overflow-auto">
        <div class="relative rounded-md text-center overflow-hidden w-full">
          <div class="absolute inset-0 opacity-50 bg-stripes bg-stripes-gray-400"></div>
          <img class="relative z-10 object-contain h-48 w-full" :src="image" />
        </div>
      </div>
      <div>
        <div
          :class="[
            'w-full border rounded-md space-y-2 px-3 py-2 bg-gray-100 dark:bg-gray-900 shadow-sm focus-within:ring-1 focus-within:ring-blue-600 focus-within:border-blue-600',
            !hasErrors
              ? 'border-gray-400 dark:border-gray-700'
              : 'border-red-400 dark:border-red-700',
          ]"
        >
          <label class="block text-xs font-medium text-gray-700 dark:text-gray-200" for="name">
            {{ __('Name') }}
          </label>
          <input
            id="name"
            v-model="value"
            :placeholder="__('NovaFileManager.actions.uploadCrop')"
            class="block w-full border-0 p-0 bg-gray-100 dark:bg-gray-900 placeholder-gray-400 sm:text-sm text-black dark:text-white focus:outline-none focus:ring-0"
            name="name"
            type="text"
          />
        </div>
        <p class="mt-2 text-xs text-gray-400" id="name-description">
          {{ __('NovaFileManager.edit.originalName', { name: file.name }) }}
        </p>
      </div>
    </template>
    <template v-slot:submitButton>
      <Button :disabled="!value" class="w-full sm:w-auto" type="submit" variant="primary">
        {{ __('NovaFileManager.actions.upload') }}
      </Button>
    </template>
    <template v-slot:cancelButton>
      <Button class="w-full sm:w-auto" type="reset" variant="secondary" @click="closeModal(name)">
        {{ __('Cancel') }}
      </Button>
    </template>
  </InputModal>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import Button from '@/components/Elements/Button'
import InputModal from '@/components/Modals/InputModal'
import { useStore } from '@/store'
import Entity from '@/types/Entity'

const props = defineProps({
  file: {
    type: Entity,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  onSubmit: {
    type: Function,
    required: true,
  },
  destFile: {
    type: File,
    required: true,
  },
  destName: {
    type: String,
    required: false,
  },
})

const store = useStore()

const value = ref(null)

onMounted(() => {
  value.value = props.destName ?? ''
})

const image = computed(() => URL.createObjectURL(props.destFile))

// ACTIONS
const closeModal = name => store.closeModal({ name })
const submit = () => props.onSubmit(value.value)
</script>
