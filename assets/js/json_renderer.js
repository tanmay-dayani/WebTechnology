/**
 * Dynamic JSON Data Rendering using Fetch API
 * Practical 6: External JSON Rendering, Search, Filter, Sort, Pagination, and Cache
 */

class JsonDataRenderer {
  constructor(config) {
    this.jsonUrl = config.jsonUrl;
    this.containerId = config.containerId;
    this.paginationId = config.paginationId;
    this.searchInputId = config.searchInputId;
    this.filterSelectId = config.filterSelectId;
    this.sortSelectId = config.sortSelectId;
    this.itemsPerPage = config.itemsPerPage || 6;
    this.storageKey = 'cached_' + (config.cacheKey || 'json_data');

    this.rawData = [];
    this.filteredData = [];
    this.currentPage = 1;

    this.init();
  }

  async init() {
    this.setupListeners();
    await this.fetchData();
  }

  setupListeners() {
    const searchInput = document.getElementById(this.searchInputId);
    const filterSelect = document.getElementById(this.filterSelectId);
    const sortSelect = document.getElementById(this.sortSelectId);

    if (searchInput) {
      searchInput.addEventListener('input', () => {
        this.currentPage = 1;
        this.applyFilters();
      });
    }

    if (filterSelect) {
      filterSelect.addEventListener('change', () => {
        this.currentPage = 1;
        this.applyFilters();
      });
    }

    if (sortSelect) {
      sortSelect.addEventListener('change', () => {
        this.applyFilters();
      });
    }
  }

  async fetchData() {
    const container = document.getElementById(this.containerId);
    if (container) {
      container.innerHTML = '<div style="text-align:center; padding: 20px;">Loading records...</div>';
    }

    try {
      const response = await fetch(this.jsonUrl);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      this.rawData = await response.json();

      // Advanced Extension: Cache response in localStorage
      try {
        localStorage.setItem(this.storageKey, JSON.stringify(this.rawData));
      } catch (e) {
        // LocalStorage quota or privacy mode fallback
      }

      this.applyFilters();
    } catch (error) {
      console.warn('Network fetch failed, attempting cache recovery:', error);
      const cached = localStorage.getItem(this.storageKey);
      if (cached) {
        this.rawData = JSON.parse(cached);
        this.applyFilters();
        if (container) {
          const banner = document.createElement('div');
          banner.className = 'alert alert-warning';
          banner.textContent = 'Showing cached offline records.';
          container.prepend(banner);
        }
      } else {
        if (container) {
          container.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
        }
      }
    }
  }

  applyFilters() {
    const searchVal = (document.getElementById(this.searchInputId)?.value || '').toLowerCase().trim();
    const filterVal = document.getElementById(this.filterSelectId)?.value || 'All';
    const sortVal = document.getElementById(this.sortSelectId)?.value || 'title-asc';

    // 1. Search & Filter
    this.filteredData = this.rawData.filter(item => {
      const titleMatch = (item.title || item.name || item.question || '').toLowerCase().includes(searchVal);
      const descMatch = (item.description || item.course || item.answer || '').toLowerCase().includes(searchVal);
      const matchesSearch = titleMatch || descMatch;

      const itemCategory = item.category || item.course || 'General';
      const matchesCategory = (filterVal === 'All' || itemCategory === filterVal);

      return matchesSearch && matchesCategory;
    });

    // 2. Sort
    this.filteredData.sort((a, b) => {
      const titleA = (a.title || a.name || a.question || '').toLowerCase();
      const titleB = (b.title || b.name || b.question || '').toLowerCase();

      if (sortVal === 'title-asc') return titleA.localeCompare(titleB);
      if (sortVal === 'title-desc') return titleB.localeCompare(titleA);
      if (sortVal === 'date-asc') return new Date(a.date || 0) - new Date(b.date || 0);
      if (sortVal === 'date-desc') return new Date(b.date || 0) - new Date(a.date || 0);
      return 0;
    });

    this.render();
    this.renderPagination();
  }

  render() {
    const container = document.getElementById(this.containerId);
    if (!container) return;

    if (this.filteredData.length === 0) {
      container.innerHTML = '<div class="alert alert-info" style="grid-column: 1 / -1; text-align: center;">No matching records found.</div>';
      return;
    }

    const start = (this.currentPage - 1) * this.itemsPerPage;
    const end = start + this.itemsPerPage;
    const pageItems = this.filteredData.slice(start, end);

    container.innerHTML = pageItems.map(item => this.template(item)).join('');
  }

  template(item) {
    // Generic renderer card supporting events, students, or FAQs
    if (item.title && item.date) {
      // Event Item
      return `
        <article class="card">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <span class="badge badge-info">${item.category}</span>
            <span class="badge ${item.status === 'Open' ? 'badge-success' : 'badge-danger'}">${item.status}</span>
          </div>
          <h3>${item.title}</h3>
          <p style="font-size:0.85rem; color:var(--primary-color);">Date: ${item.date} | Location: ${item.location}</p>
          <p>${item.description}</p>
          <div class="card-footer">
            <span style="font-size:0.8rem; color:var(--secondary-color);">Seats: ${item.seats || 'Open'}</span>
            <button class="btn btn-primary btn-sm" onclick="quickRegisterEvent(${item.id}, '${item.title.replace(/'/g, "\\'")}')">Register</button>
          </div>
        </article>
      `;
    } else if (item.name && item.course) {
      // Student Item
      return `
        <article class="card">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <span class="badge badge-info">${item.code}</span>
            <span class="badge badge-success">${item.status}</span>
          </div>
          <h3>${item.name}</h3>
          <p><strong>Course:</strong> ${item.course} (${item.year})</p>
          <p><strong>Email:</strong> ${item.email}</p>
          <p><strong>Phone:</strong> ${item.phone}</p>
        </article>
      `;
    } else {
      // Default generic card
      return `
        <div class="card">
          <h3>${item.question || item.title || 'Record'}</h3>
          <p>${item.answer || item.description || ''}</p>
        </div>
      `;
    }
  }

  renderPagination() {
    const pagContainer = document.getElementById(this.paginationId);
    if (!pagContainer) return;

    const totalPages = Math.ceil(this.filteredData.length / this.itemsPerPage);
    if (totalPages <= 1) {
      pagContainer.innerHTML = '';
      return;
    }

    let html = '';
    // Previous button
    html += `<button ${this.currentPage === 1 ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''} onclick="window.activeJsonRenderer.goToPage(${this.currentPage - 1})">« Prev</button>`;

    for (let p = 1; p <= totalPages; p++) {
      html += `<button class="${p === this.currentPage ? 'active' : ''}" onclick="window.activeJsonRenderer.goToPage(${p})">${p}</button>`;
    }

    // Next button
    html += `<button ${this.currentPage === totalPages ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''} onclick="window.activeJsonRenderer.goToPage(${this.currentPage + 1})">Next »</button>`;

    pagContainer.innerHTML = html;
  }

  goToPage(page) {
    const totalPages = Math.ceil(this.filteredData.length / this.itemsPerPage);
    if (page < 1 || page > totalPages) return;
    this.currentPage = page;
    this.render();
    this.renderPagination();
    window.scrollTo({ top: document.getElementById(this.containerId).offsetTop - 80, behavior: 'smooth' });
  }
}

