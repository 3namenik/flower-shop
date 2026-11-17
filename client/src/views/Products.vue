<template>
  <div>
    <div class="toolbar">
      <div class="chips">
        <button class="chip" :class="{ active: !filterCategory }" @click="filterCategory = null">Все</button>
        <button
          v-for="c in categories"
          :key="c.id"
          class="chip"
          :class="{ active: filterCategory === c.id }"
          @click="filterCategory = c.id"
        >{{ c.name }}</button>
      </div>
      <button class="btn btn-primary" @click="showForm = true">＋ Добавить товар</button>
    </div>

    <div v-if="loading" class="grid">
      <div v-for="i in 6" :key="i" class="card skeleton-card" />
    </div>

    <div v-else-if="filtered.length === 0" class="empty-state">
      <div class="empty-emoji">🌵</div>
      <p>Товаров пока нет</p>
    </div>

    <div v-else class="grid">
      <div
        v-for="p in filtered"
        :key="p.id"
        class="card product-card"
        :class="`tint-${tintOf(p.color)}`"
      >
        <div class="product-emoji">{{ p.category_name === 'Букеты' ? '💐' : '🌸' }}</div>
        <div class="product-body">
          <span class="badge badge-soft">{{ p.category_name || 'Без категории' }}</span>
          <h3>{{ p.name }}</h3>
          <p v-if="p.color" class="muted small">Цвет: {{ p.color }}</p>
          <p v-if="p.description" class="muted small desc">{{ p.description }}</p>
        </div>
        <footer class="product-foot">
          <span class="price">{{ money(p.price) }}</span>
          <span class="stock" :class="{ low: p.stock_qty < 30 }">{{ p.stock_qty }} шт</span>
          <button class="icon-btn" title="Редактировать" @click="edit(p)">✏️</button>
          <button class="icon-btn" title="Удалить" @click="remove(p)">🗑</button>
        </footer>
      </div>
    </div>

    <Modal :show="showForm" :title="editingId ? 'Редактировать товар' : 'Новый товар'" @close="closeForm">
      <form class="form" @submit.prevent="save">
        <label>Название *<input v-model="form.name" required /></label>
        <label>Категория
          <select v-model="form.category_id">
            <option :value="null">—</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </label>
        <label>Цвет<input v-model="form.color" placeholder="розовый" /></label>
        <label>Описание<textarea v-model="form.description" rows="2" /></label>
        <div class="row">
          <label>Цена *<input v-model.number="form.price" type="number" step="0.01" required /></label>
          <label>Остаток<input v-model.number="form.stock_qty" type="number" min="0" /></label>
        </div>
        <div class="form-actions">
          <button type="button" class="btn" @click="closeForm">Отмена</button>
          <button class="btn btn-primary">Сохранить</button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import Modal from '../components/Modal.vue';
import { productsApi } from '../api.js';

const editingId = ref(null);

function edit(p) {
  editingId.value = p.id;
  Object.assign(form, {
    name:        p.name,
    category_id: p.category_id,
    color:       p.color       ?? '',
    description: p.description ?? '',
    price:       Number(p.price),
    stock_qty:   p.stock_qty,
  });
  showForm.value = true;
}

function closeForm() {
  showForm.value = false;
  editingId.value = null;
  Object.assign(form, {
    name: '', category_id: null, color: '', description: '', price: 0, stock_qty: 0,
  });
}

const products   = ref([]);
const categories = ref([]);
const filterCategory = ref(null);
const loading    = ref(true);
const showForm   = ref(false);
const form = reactive({
  name: '', category_id: null, color: '', description: '', price: 0, stock_qty: 0,
});

const money = (n) => Number(n).toLocaleString('ru-RU') + ' ₽';
const tintOf = (color = '') => {
  const c = color.toLowerCase();
  if (c.includes('красн') || c.includes('розов')) return 'rose';
  if (c.includes('жёлт') || c.includes('желт'))   return 'amber';
  if (c.includes('бел'))                          return 'cream';
  if (c.includes('син') || c.includes('фиолет'))  return 'blue';
  return 'sage';
};

const filtered = computed(() =>
  filterCategory.value
    ? products.value.filter(p => p.category_id === filterCategory.value)
    : products.value
);

async function load() {
  loading.value = true;
  [products.value, categories.value] = await Promise.all([
    productsApi.list(), productsApi.categories(),
  ]);
  loading.value = false;
}

async function save() {
  if (editingId.value) {
    await productsApi.update(editingId.value, form);
  } else {
    await productsApi.create(form);
  }
  closeForm();
  await load();
}

async function remove(p) {
  if (!confirm(`Удалить «${p.name}»?`)) return;
  try { await productsApi.remove(p.id); await load(); }
  catch { alert('Нельзя удалить: товар используется в заказах'); }
}

onMounted(load);
</script>