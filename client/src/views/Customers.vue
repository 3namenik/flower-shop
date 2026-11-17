<template>
  <div>
    <div class="toolbar">
      <span class="muted">Всего: {{ customers.length }}</span>
      <button class="btn btn-primary" @click="showForm = true">＋ Новый клиент</button>
    </div>

    <div v-if="!customers.length" class="empty-state">
      <div class="empty-emoji">🌿</div>
      <p>Клиентов пока нет</p>
    </div>

    <table v-else class="table card">
      <thead>
        <tr>
          <th>Клиент</th><th>Телефон</th><th>Email</th>
          <th>Заказов</th><th>Потратил</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="c in customers" :key="c.id">
          <td><strong>{{ c.full_name }}</strong></td>
          <td class="muted">{{ c.phone || '—' }}</td>
          <td class="muted">{{ c.email || '—' }}</td>
          <td><span class="badge badge-soft">{{ c.orders_count }}</span></td>
          <td><strong>{{ money(c.total_spent) }}</strong></td>
        </tr>
      </tbody>
    </table>

    <Modal :show="showForm" title="Новый клиент" @close="showForm = false">
      <form class="form" @submit.prevent="save">
        <label>Имя *<input v-model="form.full_name" required /></label>
        <label>Телефон<input v-model="form.phone" /></label>
        <label>Email<input v-model="form.email" type="email" /></label>
        <label>День рождения<input v-model="form.birth_date" type="date" /></label>
        <div class="form-actions">
          <button type="button" class="btn" @click="showForm = false">Отмена</button>
          <button class="btn btn-primary">Сохранить</button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import Modal from '../components/Modal.vue';
import { customersApi } from '../api.js';

const customers = ref([]);
const showForm  = ref(false);
const form = reactive({ full_name: '', phone: '', email: '', birth_date: '' });

const money = (n) => Number(n).toLocaleString('ru-RU') + ' ₽';

async function load() { customers.value = await customersApi.list(); }
async function save() {
  await customersApi.create(form);
  showForm.value = false;
  Object.assign(form, { full_name: '', phone: '', email: '', birth_date: '' });
  await load();
}
onMounted(load);
</script>