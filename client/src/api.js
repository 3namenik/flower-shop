import axios from 'axios';

const api = axios.create({ baseURL: '/api' });

export const productsApi = {
  list:       ()         => api.get('/products').then(r => r.data),
  categories: ()         => api.get('/products/categories').then(r => r.data),
  create:     (data)     => api.post('/products', data),
  update:     (id, data) => api.patch(`/products/${id}`, data),   // ← новое
  remove:     (id)       => api.delete(`/products/${id}`),
};


export const ordersApi = {
  list:         ()      => api.get('/orders').then(r => r.data),
  create:       (data)  => api.post('/orders', data),
  updateStatus: (id, s) => api.patch(`/orders/${id}/status`, { status: s }),
};

export const customersApi = {
  list:   ()     => api.get('/customers').then(r => r.data),
  create: (data) => api.post('/customers', data),
};

export const statsApi = {
  get: () => api.get('/stats').then(r => r.data),
};