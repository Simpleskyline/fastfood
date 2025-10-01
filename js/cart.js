/**
 * Shared Cart Management Utility
 * Centralizes all cart operations across the application
 */

const Cart = {
  STORAGE_KEY: 'ronz_cart',

  /**
   * Get all items in cart
   */
  getItems() {
    try {
      return JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '[]');
    } catch (e) {
      console.error('Failed to parse cart from localStorage', e);
      return [];
    }
  },

  /**
   * Save cart to localStorage
   */
  saveItems(items) {
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(items));
  },

  /**
   * Add item to cart
   */
  addItem(item) {
    if (!item.id || !item.name || typeof item.price === 'undefined') {
      console.error('Invalid item passed to addItem', item);
      return false;
    }

    const items = this.getItems();
    const existing = items.find(i => i.id === item.id);

    if (existing) {
      existing.quantity = (existing.quantity || 1) + 1;
    } else {
      items.push({ ...item, quantity: 1 });
    }

    this.saveItems(items);
    this.notifyUpdate();
    return true;
  },

  /**
   * Update item quantity
   */
  updateQuantity(itemId, quantity) {
    const items = this.getItems();
    const item = items.find(i => i.id === itemId);
    
    if (item) {
      if (quantity <= 0) {
        this.removeItem(itemId);
      } else {
        item.quantity = quantity;
        this.saveItems(items);
        this.notifyUpdate();
      }
    }
  },

  /**
   * Remove item from cart
   */
  removeItem(itemId) {
    const items = this.getItems().filter(i => i.id !== itemId);
    this.saveItems(items);
    this.notifyUpdate();
  },

  /**
   * Clear entire cart
   */
  clear() {
    localStorage.removeItem(this.STORAGE_KEY);
    this.notifyUpdate();
  },

  /**
   * Get total quantity
   */
  getTotalQuantity() {
    return this.getItems().reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
  },

  /**
   * Get total price
   */
  getTotalPrice() {
    return this.getItems().reduce((sum, item) => {
      return sum + (Number(item.price) * Number(item.quantity));
    }, 0);
  },

  /**
   * Notify listeners of cart updates
   */
  notifyUpdate() {
    window.dispatchEvent(new CustomEvent('cartUpdated', {
      detail: {
        items: this.getItems(),
        quantity: this.getTotalQuantity(),
        total: this.getTotalPrice()
      }
    }));
  }
};

// Make Cart available globally
window.Cart = Cart;
