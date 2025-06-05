jQuery(document).ready(function($) {
    // Function to update the active TOC item on scroll
    function updateActiveMenuItem() {
        // Get all section elements
        const sections = $('.plugincy-section');
        // Get all TOC links
        const tocLinks = $('.plugincy-toc-list a');
        
        // Variables to track the current section
        let currentSectionId = '';
        let scrollPosition = $(window).scrollTop();
        
        // Add some offset to improve accuracy (consider fixed headers etc.)
        const scrollOffset = 100;
        
        // Find the current section based on scroll position
        sections.each(function() {
            const sectionTop = $(this).offset().top - scrollOffset;
            const sectionBottom = sectionTop + $(this).outerHeight();
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                currentSectionId = $(this).attr('id');
                return false; // Break the loop once we found the current section
            }
        });
        
        // Remove active class from all links
        tocLinks.removeClass('plugincy-active');
        
        // Add active class to the current section's link
        if (currentSectionId) {
            $('.plugincy-toc-list a[href="#' + currentSectionId + '"]').addClass('plugincy-active');
            
            // If the active link is a child link, also highlight its parent
            const activeLink = $('.plugincy-toc-list a[href="#' + currentSectionId + '"]');
            const parentLi = activeLink.parent().parent().parent();
            if (parentLi.is('li')) {
                parentLi.children('a').addClass('plugincy-active-parent');
            }
        }
    }
    
    // Run on page load
    updateActiveMenuItem();
    
    // Add smooth scrolling to TOC links
    $('.plugincy-toc-list a').on('click', function(e) {
        e.preventDefault();
        
        const target = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $(target).offset().top - 50
        }, 500);
    });
    
    // Run on scroll with throttling for performance
    let scrollTimer;
    $(window).on('scroll', function() {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(function() {
            updateActiveMenuItem();
        }, 50);
    });
});

jQuery(document).ready(function($) {
    $('.remove_checkout_fields').select2({
        placeholder: 'Select fields to remove',
        allowClear: true,
        width: '100%'
    });
});

document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll(".tab");
    const contents = document.querySelectorAll(".tab-content");
    const STORAGE_KEY = 'active_tab'; // Key for localStorage

    // Function to activate a specific tab
    function activateTab(tabIndex) {
        // Remove active class from all tabs and tab contents
        tabs.forEach(t => t.classList.remove("active"));
        contents.forEach(c => c.classList.remove("active"));

        // Add active class to the tab with the matching data-tab attribute
        const targetTab = document.querySelector(`.tab[data-tab="${tabIndex}"]`);
        if (targetTab) {
            targetTab.classList.add("active");
            const content = document.querySelector(`#tab-${tabIndex}`);
            if (content) {
                content.classList.add("active");
            }
        }
    }

    // Function to save active tab to localStorage
    function saveActiveTab(tabIndex) {
        try {
            localStorage.setItem(STORAGE_KEY, tabIndex);
        } catch (error) {
            console.warn('Failed to save tab to localStorage:', error);
        }
    }

    // Function to get active tab from localStorage
    function getActiveTab() {
        try {
            return localStorage.getItem(STORAGE_KEY);
        } catch (error) {
            console.warn('Failed to retrieve tab from localStorage:', error);
            return null;
        }
    }

    // Add click event listeners to tabs
    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            const tabIndex = tab.dataset.tab;
            
            // Save the selected tab to localStorage
            saveActiveTab(tabIndex);
            
            // Activate the selected tab
            activateTab(tabIndex);
        });
    });

    // Initialize the active tab on page load
    function initializeActiveTab() {
        // Check URL parameters first (for backwards compatibility or direct links)
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        
        let activeTabIndex = null;
        
        if (tabParam) {
            // If a tab parameter exists in the URL, use that and save it to localStorage
            activeTabIndex = tabParam;
            saveActiveTab(activeTabIndex);
        } else {
            // Otherwise, check localStorage for the previously active tab
            activeTabIndex = getActiveTab();
        }
        
        if (activeTabIndex) {
            // If we have a stored tab index, activate that tab
            activateTab(activeTabIndex);
        } else {
            // If no stored tab, activate the first tab as default
            const firstTab = document.querySelector('.tab');
            if (firstTab) {
                const firstTabIndex = firstTab.dataset.tab;
                activateTab(firstTabIndex);
                saveActiveTab(firstTabIndex);
            }
        }
    }

    // Initialize the active tab
    initializeActiveTab();
});