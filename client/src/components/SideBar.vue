<template>
  <aside class="sidebar">
    <div class="logo">
      <span class="logo-icon">🌸</span>
      <div>
        <h1>Flora</h1>
        <p>Илья Мазяров ЗКТ-44-25</p>
      </div>
    </div>

    <nav class="nav">
      <RouterLink to="/"          class="nav-link"><span class="nav-emoji">📊</span> Дашборд</RouterLink>
      <RouterLink to="/products"  class="nav-link"><span class="nav-emoji">🌷</span> Товары</RouterLink>
      <RouterLink to="/orders"    class="nav-link"><span class="nav-emoji">🛒</span> Заказы</RouterLink>
      <RouterLink to="/customers" class="nav-link"><span class="nav-emoji">👥</span> Клиенты</RouterLink>
    </nav>

    <div class="sidebar-footer">
      <span>🐝</span>
      <small>Flora © 2026</small>
    </div>
  </aside>
</template>