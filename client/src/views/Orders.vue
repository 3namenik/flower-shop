<template>
  <div>
    <div class="toolbar">
      <span class="muted">Всего: {{ orders.length }}</span>
      <button class="btn btn-primary" @click="openForm">＋ Новый заказ</button>
    </div>

    <div v-if="!orders.length" class="empty-state">
      <div class="empty-emoji">🕊</div>
      <p>Заказов пока нет</p>
    </div>

    <div v-else class="card-list">
      <article v-for="o in orders" :key="o.id" class="card order-card">
        <header class="order-head">
          <div>
            <h3>Заказ #{{ o.id }} · {{ o.customer_name }}</h3>
            <p class="muted small">{{ formatDate(o.order_date) }}</p>
          </div>
          <select
            v-model="o.status"
            class="status-select"
            :class="`status-${statusClass(o.status)}`"
            @change="updateStatus(o)"
          >
            <option>новый</option>
            <option>в работе</option>
            <option>доставлен</option>
            <option>отменён</option>
          </select>
        </header>

        <ul class="order-items">
          <li v-for="it in o.items" :key="it.id">
            <span>{{ it.product_name }} <span class="muted small">× {{ it.quantity }}</span></span>
            <span>{{ money(it.quantity * it.unit_price) }}</span>
          </li>
        </ul>

        <footer class="order-foot">
          <span class="muted small">📍 {{ o.delivery_addr || '—' }}</span>
          <strong>{{ money(o.total_price) }}</strong>
        </footer>
      </article>
    </div>

    <Modal :show="showForm" title="Новый заказ" @close="closeForm">
      <form class="form" @submit.prevent="save">
        <label>Клиент *
          <select v-model="form.customer_id" required>
            <option :value="null" disabled>—</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.full_name }}</option>
          </select>
        </label>
        <label>Адрес доставки<input v-model="form.delivery_addr" /></label>
        <label>Комментарий<textarea v-model="form.comment" rows="2" /></label>

        <div class="items-editor">
          <div class="muted small">Товары:</div>
          <div v-for="(row, i) in form.items" :key="i" class="item-row">
            <select v-model="row.product_id" required @change="fillPrice(row)">
              <option :value="null" disabled>—</option>
              <option v-for="p in products" :key="p.id" :value="p.id">
                {{ p.name }} · {{ money(p.price) }}
              </option>
            </select>
            <input v-model.number="row.quantity" type="number" min="1" style="width:80px" />
            <button type="button" class="icon-btn" @click="form.items.splice(i,1)">✕</button>
          </div>
          <button type="button" class="btn btn-ghost" @click="addRow">＋ Добавить товар</button>
        </div>

        <div class="form-total">Итого: <strong>{{ money(total) }}</strong></div>

        <div class="form-actions">
          <button type="button" class="btn" @click="closeForm">Отмена</button>
          <button class="btn btn-primary" :disabled="!form.items.length">Оформить</button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import Modal from '../components/Modal.vue';
import { ordersApi, productsApi, customersApi } from '../api.js';

const orders    = ref([]);
const products  = ref([]);
const customers = ref([]);
const showForm  = ref(false);

const form = reactive({
  customer_id: null, delivery_addr: '', comment: '',
  items: [{ product_id: null, quantity: 1, unit_price: 0 }],
});

const money = (n) => Number(n).toLocaleString('ru-RU') + ' ₽';
const formatDate = (s) => new Date(s).toLocaleString('ru-RU');
const statusClass = (s) => ({ 'новый':'new','в работе':'work','доставлен':'done','отменён':'cancel' })[s];

const total = computed(() =>
  form.items.reduce((sum, r) => sum + (r.unit_price || 0) * (r.quantity || 0), 0)
);

function fillPrice(row) {
  const p = products.value.find(p => p.id === row.product_id);
  row.unit_price = p ? Number(p.price) : 0;
}

async function load() {
  [orders.value, products.value, customers.value] = await Promise.all([
    ordersApi.list(), productsApi.list(), customersApi.list(),
  ]);
}

function openForm() { showForm.value = true; }
function closeForm() {
  showForm.value = false;
  Object.assign(form, {
    customer_id: null, delivery_addr: '', comment: '',
    items: [{ product_id: null, quantity: 1, unit_price: 0 }],
  });
}
function addRow() {
  form.items.push({ product_id: null, quantity: 1, unit_price: 0 });
}

async function save() {
  try {
    await ordersApi.create(form);
    closeForm();
    await load();
  } catch (e) {
    alert(e.response?.data?.error || 'Не удалось создать заказ');
  }
}

async function updateStatus(o) {
  try {
    await ordersApi.updateStatus(o.id, o.status);
    await load();                       // обновит и остатки на других экранах
  } catch (e) {
    alert(e.response?.data?.error || 'Не удалось сменить статус');
    await load();                       // откатит селект назад к реальному статусу
  }
}

onMounted(load);
</script>