<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="modal-backdrop" @click.self="$emit('close')">
        <div class="modal">
          <header class="modal-head">
            <h3>{{ title }}</h3>
            <button class="icon-btn" @click="$emit('close')">✕</button>
          </header>
          <div class="modal-body">
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({ show: Boolean, title: String });
defineEmits(['close']);
</script>