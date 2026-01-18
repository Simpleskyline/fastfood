/**
 * Authentication & Session Management Utility
 * Handles user authentication state
 */

const Auth = {
  STORAGE_KEY: 'fastfood_current',
  USERS_KEY: 'fastfood_users',

  /**
   * Get current logged-in user
   */
  getCurrentUser() {
    const username = localStorage.getItem(this.STORAGE_KEY);
    if (!username) return null;

    const users = this.getAllUsers();
    return users[username] || null;
  },

  /**
   * Get all users (for localStorage-based auth)
   */
  getAllUsers() {
    try {
      return JSON.parse(localStorage.getItem(this.USERS_KEY) || '{}');
    } catch (e) {
      console.error('Failed to parse users', e);
      return {};
    }
  },

  /**
   * Check if user is logged in
   */
  isLoggedIn() {
    return this.getCurrentUser() !== null;
  },

  /**
   * Check if user is admin
   */
  isAdmin() {
    const user = this.getCurrentUser();
    return user && user.role === 'admin';
  },

  /**
   * Require authentication - redirect if not logged in
   */
  requireAuth(redirectUrl = 'auth.html') {
    if (!this.isLoggedIn()) {
      window.location.href = redirectUrl;
      return false;
    }
    return true;
  },

  /**
   * Require admin role - redirect if not admin
   */
  requireAdmin(redirectUrl = 'dashboard.html') {
    if (!this.isAdmin()) {
      alert('Access denied. Admin privileges required.');
      window.location.href = redirectUrl;
      return false;
    }
    return true;
  },

  /**
   * Login user
   */
  login(username, password) {
    const users = this.getAllUsers();
    const user = users[username];

    if (!user || user.password !== password) {
      return { success: false, message: 'Invalid username or password' };
    }

    localStorage.setItem(this.STORAGE_KEY, username);
    return { success: true, user };
  },

  /**
   * Register new user
   */
  register(userData) {
    const users = this.getAllUsers();

    if (users[userData.username]) {
      return { success: false, message: 'Username already taken' };
    }

    users[userData.username] = userData;
    localStorage.setItem(this.USERS_KEY, JSON.stringify(users));
    localStorage.setItem(this.STORAGE_KEY, userData.username);

    return { success: true, user: userData };
  },

  /**
   * Logout current user
   */
  logout() {
    localStorage.removeItem(this.STORAGE_KEY);
    window.location.href = 'auth.html';
  },

  /**
   * Update user profile
   */
  updateProfile(updates) {
    const username = localStorage.getItem(this.STORAGE_KEY);
    if (!username) return { success: false, message: 'Not logged in' };

    const users = this.getAllUsers();
    if (!users[username]) return { success: false, message: 'User not found' };

    users[username] = { ...users[username], ...updates };
    localStorage.setItem(this.USERS_KEY, JSON.stringify(users));

    return { success: true, user: users[username] };
  }
};

// Make Auth available globally
window.Auth = Auth;
