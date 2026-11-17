<template>
  <div class="app">
    <SideBar />
    <main class="main">
      <header class="topbar">
        <h2>{{ $route.meta.title }}</h2>
        <span class="topbar-date">{{ today }}</span>
      </header>
      <div class="page">
        <RouterView v-slot="{ Component }">
          <Transition name="fade" mode="out-in">
            <component :is="Component" />
          </Transition>
        </RouterView>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import SideBar from './components/SideBar.vue';

const today = computed(() =>
  new Date().toLocaleDateString('ru-RU', {
    weekday: 'long', day: 'numeric', month: 'long',
  })
);
</script>