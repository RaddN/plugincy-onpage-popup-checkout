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