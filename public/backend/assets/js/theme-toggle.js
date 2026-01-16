/**
 * Microfinance App - Theme Toggle System
 * Handles dark/light theme switching with persistence
 */

(function($) {
    'use strict';

    const ThemeToggle = {
        // Theme options
        themes: {
            light: 'light',
            dark: 'dark',
            system: 'system'
        },

        // Current theme
        currentTheme: null,

        // Initialize
        init: function() {
            this.loadTheme();
            this.setupToggleButton();
            this.watchSystemPreference();
        },

        // Load saved theme preference
        loadTheme: function() {
            // Check localStorage first (for guest users)
            const savedTheme = localStorage.getItem('mf-theme') || 'light';
            
            // Check user preference from database (if logged in)
            const userTheme = window.mfUserTheme || null;
            
            // Use user preference if available, otherwise use saved theme
            const theme = userTheme || savedTheme;
            
            this.applyTheme(theme);
        },

        // Apply theme to document
        applyTheme: function(theme) {
            const html = document.documentElement;
            
            if (theme === 'system') {
                // Follow OS preference
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                theme = prefersDark ? 'dark' : 'light';
            }
            
            // Remove existing theme classes
            html.classList.remove('light-theme', 'dark-theme');
            
            // Add new theme class
            html.classList.add(theme + '-theme');
            html.setAttribute('data-theme', theme);
            
            this.currentTheme = theme;
            
            // Update toggle button icon
            this.updateToggleIcon(theme);
            
            // Save to localStorage
            localStorage.setItem('mf-theme', theme);
            
            // Save to database (if logged in)
            this.saveToDatabase(theme);
        },

        // Setup toggle button
        setupToggleButton: function() {
            const toggleBtn = document.getElementById('theme-toggle-btn');
            
            if (!toggleBtn) return;
            
            toggleBtn.addEventListener('click', () => {
                this.toggleTheme();
            });
        },

        // Toggle between themes
        toggleTheme: function() {
            const current = this.currentTheme === 'dark' ? 'light' : 'dark';
            this.applyTheme(current);
        },

        // Update toggle button icon
        updateToggleIcon: function(theme) {
            const toggleBtn = document.getElementById('theme-toggle-btn');
            if (!toggleBtn) return;
            
            const icon = toggleBtn.querySelector('i');
            if (!icon) return;
            
            // Remove existing icon classes
            icon.classList.remove('fa-sun', 'fa-moon', 'fa-adjust');
            
            // Add appropriate icon
            if (theme === 'dark') {
                icon.classList.add('fa-sun'); // Show sun icon in dark mode (to switch to light)
            } else {
                icon.classList.add('fa-moon'); // Show moon icon in light mode (to switch to dark)
            }
        },

        // Watch system preference changes
        watchSystemPreference: function() {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            
            mediaQuery.addEventListener('change', (e) => {
                // Only auto-switch if user has selected 'system' preference
                if (this.currentTheme === 'system' || localStorage.getItem('mf-theme') === 'system') {
                    this.applyTheme('system');
                }
            });
        },

        // Save theme preference to database
        saveToDatabase: function(theme) {
            // Only save if user is logged in
            if (typeof window.mfUserId === 'undefined') return;
            
            // Use AJAX to save preference
            $.ajax({
                url: '/api/user/theme-preference',
                method: 'POST',
                data: {
                    theme: theme,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Theme preference saved');
                },
                error: function(xhr) {
                    console.error('Failed to save theme preference');
                }
            });
        }
    };

    // Initialize on DOM ready
    $(document).ready(function() {
        ThemeToggle.init();
    });

    // Expose globally for manual control
    window.MFThemeToggle = ThemeToggle;

})(jQuery);

