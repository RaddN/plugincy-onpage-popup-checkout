jQuery(document).ready(function ($) {
    "use strict";
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
        sections.each(function () {
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

    $(document).on('click change keydown keyup keypress input paste cut mousedown', '.disabled', function (e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    });

    // Run on page load
    updateActiveMenuItem();

    // Add smooth scrolling to TOC links
    $('.plugincy-toc-list a').on('click', function (e) {
        e.preventDefault();

        const target = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $(target).offset().top - 50
        }, 500);
    });

    // Run on scroll with throttling for performance
    let scrollTimer;
    $(window).on('scroll', function () {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(function () {
            updateActiveMenuItem();
        }, 50);
    });
});

jQuery(document).ready(function ($) {
    "use strict";

    if ($('.remove_checkout_fields').length) $('.remove_checkout_fields').select2({
        placeholder: 'Select fields to remove',
        allowClear: true,
        width: '100%'
    });
});

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const tabContainer = document.querySelector(".tab-container");
    if (!tabContainer) {
        return;
    }

    const tabs = tabContainer.querySelectorAll(":scope > .tabs > .tab");
    const contents = Array.prototype.map.call(tabs, tab => {
        return document.getElementById(`tab-${tab.dataset.tab}`);
    }).filter(Boolean);
    const STORAGE_KEY = 'active_tab'; // Key for localStorage

    // Function to activate a specific tab
    function activateTab(tabIndex) {
        // Remove active class from all tabs and tab contents
        tabs.forEach(t => t.classList.remove("active"));
        contents.forEach(c => c.classList.remove("active"));

        // Add active class to the tab with the matching data-tab attribute
        const targetTab = tabContainer.querySelector(`:scope > .tabs > .tab[data-tab="${tabIndex}"]`);
        if (targetTab) {
            targetTab.classList.add("active");
            const content = document.getElementById(`tab-${tabIndex}`);
            if (content) {
                content.classList.add("active");
            }
        } else {
            // activate the first tab as default
            const firstTab = tabs[0];
            if (firstTab) {
                const firstTabIndex = firstTab.dataset.tab;
                activateTab(firstTabIndex);
                saveActiveTab(firstTabIndex);
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
            const firstTab = tabs[0];
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



(function () {
    "use strict";

    // Create modal HTML
    const modalHTML = `
    <div class="modal-overlay-notice" id="modalOverlayNotice">
      <div class="modal-notice">
        <div class="modal-notice-header">
          <div class="modal-notice-icon"><svg width="18" height="18" viewBox="0 0 0.54 0.54" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M.27.332A.02.02 0 0 1 .253.315V.202Q.255.187.27.185C.285.183.287.193.287.202v.112A.02.02 0 0 1 .27.331m0 .074H.266L.262.404.258.402.255.399Q.248.392.248.383q0-.012.007-.016L.258.364.262.362.266.361h.009l.004.001.004.002.003.003q.007.007.007.016 0 .012-.007.016L.283.402.279.404.275.405H.271" fill="#fff"/><path d="M.406.499H.134Q.067.499.039.454.013.409.046.35L.182.104Q.217.042.27.041C.323.04.335.063.358.104l.136.245q.032.059.007.104Q.474.497.406.498M.27.075Q.237.075.212.12L.076.366Q.053.408.069.437c.016.029.034.028.065.028h.273q.049 0 .065-.028C.488.409.48.394.465.366L.328.121Q.303.077.27.076" fill="#fff"/></svg></div>
          <h3 class="modal-notice-title">Confirm Action</h3>
        </div>
        <div class="modal-notice-body">
          <p class="modal-notice-message" id="modalNoticeMessage"></p>
        </div>
        <div class="modal-notice-footer">
          <button class="modal-notice-btn modal-notice-btn-cancel" id="btnNoticeCancel">Cancel</button>
          <button class="modal-notice-btn modal-notice-btn-confirm" id="btnNoticeConfirm">Confirm</button>
        </div>
      </div>
    </div>
  `;

    // Inject modal into body
    document.addEventListener('DOMContentLoaded', function () {
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        let checkboxes = document.querySelectorAll('input[type="checkbox"][data-notice]');
        checkboxes = Array.from(checkboxes).filter(checkbox => checkbox.dataset.notice !== '');
        const modalOverlay = document.getElementById('modalOverlayNotice');
        const modalMessage = document.getElementById('modalNoticeMessage');
        const btnCancel = document.getElementById('btnNoticeCancel');
        const btnConfirm = document.getElementById('btnNoticeConfirm');

        let currentCheckbox = null;
        let previousState = {};

        // Initialize previous states
        checkboxes.forEach(checkbox => {
            previousState[checkbox.name] = checkbox.checked;
        });

        // Handle checkbox changes
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function (e) {
                if (previousState[this.name] === true && this.checked === false) {
                    const noticeMessage = this.getAttribute('data-notice');
                    currentCheckbox = this;

                    // Show modal
                    modalMessage.textContent = noticeMessage;
                    modalOverlay.classList.add('active');

                    // Prevent default behavior
                    this.checked = true;
                } else {
                    previousState[this.name] = this.checked;
                }
            });
        });

        // Cancel button
        btnCancel.addEventListener('click', function () {
            modalOverlay.classList.remove('active');
            if (currentCheckbox) {
                currentCheckbox.checked = true;
                previousState[currentCheckbox.name] = true;
            }
            currentCheckbox = null;
        });

        // Confirm button
        btnConfirm.addEventListener('click', function () {
            modalOverlay.classList.remove('active');
            if (currentCheckbox) {
                currentCheckbox.checked = false;
                previousState[currentCheckbox.name] = false;
            }
            currentCheckbox = null;
        });

        // Close on overlay click
        modalOverlay.addEventListener('click', function (e) {
            if (e.target === modalOverlay) {
                btnCancel.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
            }
        });
    });
})();
