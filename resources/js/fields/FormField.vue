<template>
  <DefaultField :errors="errors" :field="field" :show-help-text="showHelpText">
    <template #field>
      <div class="nova-file-manager">
        <div :class="{ dark }">
          <div v-if="value?.length > 0" class="flex flex-row gap-2 flex-wrap w-full">
            <draggable
              v-model="value"
              class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-2 w-full"
              ghost-class="opacity-0"
              item-key="id"
              @end="drag = false"
              @start="drag = true"
              tag="ul"
              v-bind="dragOptions"
            >
              <template #item="{ element }">
                <FieldCard
                  :field="field"
                  :file="element"
                  class="cursor-grab"
                  :on-deselect="deselectFile"
                />
              </template>
            </draggable>
          </div>

          <div class="flex flex-row gap-2">
            <button
              class="relative flex flex-row shrink-0 items-center px-4 py-2 rounded-md border border-gray-300 dark:hover:border-blue-500 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 focus:z-10 focus:outline-none"
              type="button"
              @click="openBrowserModal"
            >
              <CloudIcon
                aria-hidden="true"
                class="-ml-1 mr-2 h-5 w-5 text-gray-400 dark:text-gray-200"
              />
              {{ __('NovaFileManager.openBrowser') }}
            </button>
          </div>
        </div>
      </div>
    </template>
  </DefaultField>

  <TransitionRoot
    v-if="displayModal"
    :show="isBrowserOpen"
    as="template"
    class="nova-file-manager w-full"
  >
    <DialogModal as="div" class="relative" @close="closeBrowserModal">
      <TransitionChild
        as="template"
        class="z-[60]"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-800/20 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div :class="['fixed z-[60] inset-0 overflow-y-auto w-full', { dark }]">
        <div class="flex items-start justify-center min-h-full">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              class="relative bg-transparent md:rounded-lg overflow-hidden shadow-xl transition-all w-full border border-gray-300 dark:border-gray-800 md:m-8 m-0"
            >
              <Browser />
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </DialogModal>
  </TransitionRoot>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { mapActions, mapState } from 'pinia'
import { CloudIcon } from '@heroicons/vue/24/outline'
import {
  Dialog as DialogModal,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'
import Browser from '@/components/Browser'
import draggable from 'vuedraggable'
import FieldCard from '@/components/Cards/FieldCard'
import Entity from '@/types/Entity'
import { useStore } from '@/store'

export default {
  mixins: [FormField, HandlesValidationErrors],

  components: {
    FieldCard,
    Browser,
    CloudIcon,
    DialogModal,
    DialogPanel,
    TransitionChild,
    TransitionRoot,
    draggable,
  },

  props: ['resourceName', 'resourceId', 'field'],

  data: () => ({
    drag: false,
    displayModal: false,
    value: [],
    flexibleGroup: [],
  }),

  mounted() {
    this.init()

    this.value = (this.field.value || []).map(file => this.mapEntity(file))
    this.flexibleGroup = this.resolveFlexible(this)
  },

  computed: {
    ...mapState(useStore, ['dark', 'disk', 'isBrowserOpen']),

    dragOptions() {
      return {
        animation: 200,
        disabled: !this.field?.multiple,
        ghostClass: 'opacity-0',
      }
    },
  },

  methods: {
    ...mapActions(useStore, ['init', 'closeBrowser', 'openBrowser']),

    fill(formData) {
      if (this.value?.length) {
        formData.append(
          this.field.attribute,
          JSON.stringify(
            this.value?.map(file => ({
              path: file.path,
              disk: file.disk,
            }))
          )
        )
      }
    },

    openBrowserModal() {
      this.displayModal = true

      this.openBrowser({
        initialFiles: this.value,
        multiple: this.field.multiple ?? false,
        limit: this.field.limit ?? null,
        resource: this.resourceName ?? null,
        resourceId: this.resourceId ?? null,
        attribute: this.flexibleGroup.length ? this.field.sortableUriKey : this.field.attribute,
        singleDisk: this.field.customDisk ?? false,
        permissions: this.field.permissions ?? {},
        flexibleGroup: this.flexibleGroup,
        callback: selection => {
          this.value = selection.map(f => this.mapEntity(f))
        },
        usePintura: this.field.usePintura ?? false,
        pinturaOptions: this.field.pinturaOptions ?? {},
      })
    },

    closeBrowserModal() {
      this.displayModal = false
      this.closeBrowser()
    },

    deselectFile(file) {
      this.value = this.value.filter(f => f.id !== file.id)
    },

    mapEntity: file =>
      new Entity(
        file.id,
        file.name,
        file.path,
        file.size,
        file.extension,
        file.mime,
        file.url,
        file.lastModifiedAt,
        file.type,
        file.exists,
        file.disk
      ),

    resolveFlexible(component) {
      let elements = []

      let group = component.$parent
      let parent = component.$parent?.$parent?.$parent?.$parent

      if (parent?.field?.component === 'nova-flexible-content') {
        elements.unshift(...this.resolveFlexible(parent))
        elements.push(`${group?.group?.name}:${parent.field.sortableUriKey}`)
      }

      return elements
    },
  },

  watch: {
    isBrowserOpen(newValue, oldValue) {
      if (!newValue && oldValue) {
        this.displayModal = false
      }
    },
  },
}
</script>
