import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from './views/Dashboard.vue';
import Products  from './views/Products.vue';
import Orders    from './views/Orders.vue';
import Customers from './views/Customers.vue';

export default createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/',          component: Dashboard, meta: { title: 'Дашборд' } },
    { path: '/products',  component: Products,  meta: { title: 'Товары'  } },
    { path: '/orders',    component: Orders,    meta: { title: 'Заказы'  } },
    { path: '/customers', component: Customers, meta: { title: 'Клиенты' } },
  ],
});