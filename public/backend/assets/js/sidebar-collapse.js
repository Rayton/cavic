/**
 * Microfinance App - Enhanced Sidebar Collapse
 * Handles sidebar collapse with icons and labels
 */

(function($) {
    'use strict';

    const SidebarCollapse = {
        // Initialize
        init: function() {
            this.loadState();
            this.setupToggle();
            this.initializeTooltips();
            this.handleResize();
        },

        // Setup toggle button
        setupToggle: function() {
            $('.nav-btn').off('click').on('click', function() {
                $('.page-container').toggleClass('sbar_collapsed');
                
                // Add tooltip data attributes for collapsed state
                if ($('.page-container').hasClass('sbar_collapsed')) {
                    SidebarCollapse.addTooltips();
                }
                
                // Save sidebar state
                SidebarCollapse.saveState();
            });
        },

        // Add tooltip data attributes
        addTooltips: function() {
            $('#menu li > a').each(function() {
                var menuText = $(this).find('span').first().text().trim();
                if (menuText) {
                    $(this).attr('data-tooltip', menuText);
                }
            });
        },

        // Initialize tooltips on page load
        initializeTooltips: function() {
            if ($('.page-container').hasClass('sbar_collapsed')) {
                this.addTooltips();
            }
        },

        // Handle window resize
        handleResize: function() {
            var resizeTimer;
            $(window).on('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    // Auto-collapse on smaller screens
                    if (window.innerWidth <= 1364) {
                        if (!$('.page-container').hasClass('sbar_collapsed')) {
                            $('.page-container').addClass('sbar_collapsed');
                            SidebarCollapse.addTooltips();
                        }
                    }
                }, 250);
            });
        },

        // Save sidebar state to localStorage
        saveState: function() {
            const isCollapsed = $('.page-container').hasClass('sbar_collapsed');
            localStorage.setItem('mf-sidebar-collapsed', isCollapsed);
        },

        // Load sidebar state from localStorage
        loadState: function() {
            const savedState = localStorage.getItem('mf-sidebar-collapsed');
            
            // Only apply saved state on desktop
            if (window.innerWidth > 1364) {
                if (savedState === 'true') {
                    $('.page-container').addClass('sbar_collapsed');
                    this.addTooltips();
                } else if (savedState === 'false') {
                    $('.page-container').removeClass('sbar_collapsed');
                }
            } else {
                // Auto-collapse on mobile/tablet
                $('.page-container').addClass('sbar_collapsed');
                this.addTooltips();
            }
        }
    };

    // Initialize on DOM ready
    $(document).ready(function() {
        SidebarCollapse.init();
    });

    // Expose globally
    window.MFSidebarCollapse = SidebarCollapse;

})(jQuery);

