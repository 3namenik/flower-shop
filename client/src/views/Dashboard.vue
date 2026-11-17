<template>
  <div v-if="stats" class="dashboard">
    <div class="stat-grid">
      <div class="stat-card stat-rose">
        <span class="stat-emoji">🌷</span>
        <div>
          <div class="stat-value">{{ stats.products }}</div>
          <div class="stat-label">товаров</div>
        </div>
      </div>
      <div class="stat-card stat-sage">
        <span class="stat-emoji">👥</span>
        <div>
          <div class="stat-value">{{ stats.customers }}</div>
          <div class="stat-label">клиентов</div>
        </div>
      </div>
      <div class="stat-card stat-blue">
        <span class="stat-emoji">🛒</span>
        <div>
          <div class="stat-value">{{ stats.orders }}</div>
          <div class="stat-label">заказов</div>
        </div>
      </div>
      <div class="stat-card stat-amber">
        <span class="stat-emoji">💰</span>
        <div>
          <div class="stat-value">{{ money(stats.revenue) }}</div>
          <div class="stat-label">выручка</div>
        </div>
      </div>
    </div>

    <section class="card">
      <h3 class="card-title" style="padding: 20px 22px 0">⚠️ Скоро закончится</h3>
      <table v-if="stats.lowStock.length" class="table">
        <thead>
          <tr><th>Товар</th><th>Цвет</th><th>Остаток</th></tr>
        </thead>
        <tbody>
          <tr v-for="p in stats.lowStock" :key="p.id">
            <td>{{ p.name }}</td>
            <td class="muted">{{ p.color || '—' }}</td>
            <td><span class="badge badge-danger">{{ p.stock_qty }} шт</span></td>
          </tr>
        </tbody>
      </table>
      <p v-else class="empty">Всё в порядке 🌿</p>
    </section>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { statsApi } from '../api.js';

const stats = ref(null);
const money = (n) => Number(n).toLocaleString('ru-RU') + ' ₽';

onMounted(async () => { stats.value = await statsApi.get(); });
</script>