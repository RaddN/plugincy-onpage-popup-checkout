/**
 * WooCommerce Quick View JavaScript
 * 
 * Handles the frontend functionality for the Quick View feature.
 */
(function ($) {
    'use strict';

    // Quick View Object
    var RMenuQuickView = {
        modal: null,
        overlay: null,
        content: null,
        closeBtn: null,
        loading: null,
        prevBtn: null,
        nextBtn: null,
        currentProductId: null,
        isLoading: false,
        settings: rmenu_quick_view_params,


        /**
         * Initialize the Quick View functionality
         */
        init: function () {
            // Cache DOM elements
            this.modal = $('.rmenu-quick-view-modal-container');
            this.overlay = this.modal.find('.rmenu-quick-view-modal-overlay');
            this.content = this.modal.find('.rmenu-quick-view-inner');
            this.closeBtn = this.modal.find('.rmenu-quick-view-close');
            this.loading = this.modal.find('.rmenu-quick-view-loading');
            this.prevBtn = this.modal.find('.rmenu-quick-view-prev');
            this.nextBtn = this.modal.find('.rmenu-quick-view-next');


            // Bind events
            this.bindEvents();

            // Mobile optimization
            if (this.settings.mobile_optimize) {
                this.mobileOptimize();
            }

            // Trigger init event
            $(document.body).trigger('rmenu_quick_view_init');
        },

        /**
         * Bind all necessary events
         */
        bindEvents: function () {
            var self = this;

            // Quick view button click
            $(document.body).on('click', '.rmenu-quick-view-btn', function (e) {
                e.preventDefault();
                var productId = $(this).data('product-id');
                self.openQuickView(productId);
                return false;
            });

            // Close quick view
            this.closeBtn.on('click', function (e) {
                e.preventDefault();
                self.closeQuickView();
                return false;
            });

            // Close on overlay click
            this.overlay.on('click', function (e) {
                if (e.target === this) {
                    self.closeQuickView();
                    return false;
                }
            });

            // Previous/Next buttons
            this.prevBtn.on('click', function (e) {
                e.preventDefault();
                self.navigateProduct('prev');
                return false;
            });

            this.nextBtn.on('click', function (e) {
                e.preventDefault();
                self.navigateProduct('next');
                return false;
            });

            // Keyboard navigation
            if (this.settings.keyboard_nav) {
                $(document).on('keydown', function (e) { // Changed from keyup to keydown for better responsiveness
                    if (!self.modal.hasClass('active')) {
                        return;
                    }

                    // ESC key
                    if (e.keyCode === 27) {
                        self.closeQuickView();
                    }
                    // Left arrow
                    else if (e.keyCode === 37) {
                        self.navigateProduct('prev');
                    }
                    // Right arrow
                    else if (e.keyCode === 39) {
                        self.navigateProduct('next');
                    }
                });
            }

            // Gallery thumbnails
            this.modal.on('click', '.rmenu-quick-view-thumbnail', function () {
                var $this = $(this);
                var imageId = $this.data('image-id');
                var fullImage = $this.data('full-image');

                // Update active thumbnail
                self.modal.find('.rmenu-quick-view-thumbnail').removeClass('active');
                $this.addClass('active');

                // Update main image
                var $mainImage = self.modal.find('.rmenu-quick-view-main-image img');
                var $lightboxLink = self.modal.find('.rmenu-quick-view-lightbox');

                // If we're using lightbox and have a full image URL
                if (self.settings.lightbox && fullImage) {
                    $lightboxLink.attr('href', fullImage);
                }

                // Fade out/in the image for smooth transition
                $mainImage.fadeOut(100, function () {
                    // Find the new image in data attributes
                    var newImage = $this.data('large-image') || $this.find('img').attr('src').replace('-thumbnail', '');
                    $(this).attr('src', newImage).fadeIn(100);
                });
            });

            // Initialize lightbox if enabled
            if (this.settings.lightbox) {
                this.initLightbox();
            }

            // Variation select handling
            this.modal.on('show_variation', function (event, variation) {
                if (variation && variation.image && variation.image.src) {
                    var $mainImage = self.modal.find('.rmenu-quick-view-main-image img');
                    var $lightboxLink = self.modal.find('.rmenu-quick-view-lightbox');

                    $mainImage.attr('src', variation.image.src).attr('srcset', '');

                    if (self.settings.lightbox && variation.image.full_src) {
                        $lightboxLink.attr('href', variation.image.full_src);
                    }
                }
            });
        },

        /**
         * Open the quick view modal using product data already stored in DOM
         */
        openQuickView: function (productId) {
            var self = this;

            if (self.isLoading) {
                return;
            }

            self.isLoading = true;
            self.currentProductId = productId;

            // Show modal with loading indicator
            self.showModal();
            self.loading.show();
            self.content.empty();

            // Find the product data from the DOM
            var $productElement = $('.rmenu-product-data[data-product-info]').filter(function () {
                var productInfo = $(this).data('product-info');
                return productInfo && productInfo.id == productId;
            }).first();

            if ($productElement.length) {
                // Use the embedded product data
                var productData = $productElement.data('product-info');
                self.renderProductContent(productData);
            } else {
                // Fallback to AJAX load if we don't have the data
                self.loadProductContent(productId);
            }
        },

        /**
         * Render product content based on JSON data
         */
        renderProductContent: function (productData) {
            var self = this;
            var html = '';

            // Get the elements to display
            var elements = self.settings.elements_in_popup || ['image', 'title', 'rating', 'price', 'excerpt', 'add_to_cart', 'meta'];

            // Start building the HTML
            html += '<div class="rmenu-quick-view-product">';

            // Left column (image)
            html += '<div class="rmenu-quick-view-left">';
            if ($.inArray('image', elements) !== -1 && productData.images.length > 0) {
                html += '<div class="rmenu-quick-view-images">';
                // Main image
                html += '<div class="rmenu-quick-view-main-image">';
                if (productData.images.length > 1) {
                    html += '<a href="' + productData.images[0].full + '" class="rmenu-quick-view-lightbox">';
                } else {
                    html += '<a href="' + productData.images[0].full + '" class="rmenu-quick-view-image">';
                }
                html += '<img src="' + productData.images[0].src + '" alt="' + productData.images[0].alt + '">';
                html += '</a>';
                html += '</div>';

                // Thumbnails (if more than one image)
                if ($.inArray('gallery', elements) !== -1 && productData.images.length > 1) {
                    html += '<div class="rmenu-quick-view-thumbnails">';
                    $.each(productData.images, function (index, image) {
                        var activeClass = index === 0 ? ' active' : '';
                        html += '<div class="rmenu-quick-view-thumbnail' + activeClass + '" data-image-id="' + image.id + '" data-large-image="' + image.src + '" data-full-image="' + image.full + '">';
                        html += '<img src="' + image.thumb + '" alt="' + image.alt + '">';
                        html += '</div>';
                    });
                    html += '</div>';
                }
                html += '</div>';
            } else {
                // Fallback if no images are available
                html += '<div class="rmenu-quick-view-images">';
                html += '<div class="rmenu-quick-view-main-image">';
                html += '<img src="/wp-content/uploads/woocommerce-placeholder-300x300.png" alt="' + self.settings.i18n.no_image + '">';
                html += '</div>';
                html += '</div>';
            }
            html += '</div>';

            // Right column (information)
            html += '<div class="rmenu-quick-view-right">';

            // Title
            if ($.inArray('title', elements) !== -1) {
                html += '<h2 class="product_title">' + productData.title + '</h2>';
            }

            // Rating
            if ($.inArray('rating', elements) !== -1 && productData.rating_html) {
                html += '<div class="woocommerce-product-rating">' + productData.rating_html + '</div>';
            }

            // Price
            if ($.inArray('price', elements) !== -1) {
                html += '<div class="price">' + productData.price_html + '</div>';
            }

            // Excerpt
            if ($.inArray('excerpt', elements) !== -1 && productData.excerpt) {
                html += '<div class="woocommerce-product-details__short-description">' + productData.excerpt + '</div>';
            }

            // Add to cart form
            if ($.inArray('add_to_cart', elements) !== -1) {
                if (productData.type === 'simple') {
                    // Simple product add to cart
                    html += '<form class="cart rmenu-add-to-cart-form" method="post" enctype="multipart/form-data">';
                    html += '<input type="hidden" name="add-to-cart" value="' + productData.id + '">';

                    // Quantity field
                    if ($.inArray('quantity', elements) !== -1) {
                        html += '<div class="quantity">';
                        html += '<label class="screen-reader-text" for="quantity_' + productData.id + '">' + self.settings.i18n.quantity + '</label>';
                        html += '<input type="number" id="quantity_' + productData.id + '" class="input-text qty text" step="1" min="1" max="' + (productData.max_purchase_quantity || '') + '" name="quantity" value="' + (productData.min_purchase_quantity || '1') + '" title="Qty" size="4">';
                        html += '</div>';
                    }

                    // Add to cart button based on stock status
                    if (productData.is_in_stock && productData.is_purchasable) {
                        html += '<a href="?add-to-cart=' + productData.id + '" ' +
                            'data-quantity="1" ' +
                            'class="button product_type_simple add_to_cart_button ajax_add_to_cart rmenu-ajax-add-to-cart" ' +
                            'data-product_id="' + productData.id + '" ' +
                            'data-product_sku="' + (productData.sku || '') + '" ' +
                            'data-default_qty="1" ' +
                            'aria-label="Add to cart: &ldquo;' + productData.title.replace(/"/g, '&quot;') + '&rdquo;" ' +
                            'rel="nofollow" ' +
                            'style="display: inline-flex; align-items: center; justify-content: center;">' +
                            '<span class="rmenu-btn-icon" style="margin-right: 8px;">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block;">' +
                            '<circle cx="9" cy="21" r="1"></circle>' +
                            '<circle cx="20" cy="21" r="1"></circle>' +
                            '<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>' +
                            '</svg>' +
                            '</span>' +
                            '<span class="rmenu-btn-text">' + self.settings.i18n.add_to_cart + '</span>' +
                            '</a>';
                    } else {
                        html += '<button type="button" class="button alt disabled">' + self.settings.i18n.out_of_stock + '</button>';
                    }

                    html += '</form>';
                } else if (productData.type === 'variable') {
                    // For variable products we should show a button to redirect to product page
                    html += '<a href="' + productData.permalink + '" class="button alt">' + self.settings.i18n.select_options + '</a>';
                }
            }

            // Meta information
            if ($.inArray('meta', elements) !== -1) {
                html += '<div class="product_meta">';

                // SKU
                if (productData.sku) {
                    html += '<span class="sku_wrapper"> SKU: <span class="sku">' + productData.sku + '</span></span>';
                }

                // Categories
                if (productData.categories) {
                    html += '<span class="posted_in"> Categories: ' + productData.categories + '</span>';
                }

                // Tags
                if (productData.tags) {
                    html += '<span class="tagged_as"> Tags: ' + productData.tags + '</span>';
                }

                html += '</div>';
            }
            if ($.inArray('view_details', elements) !== -1) {
                // View full details link
                html += '<div class="rmenu-quick-view-details-button">';
                html += '<a href="' + productData.permalink + '" class="button">' + self.settings.i18n.view_details + '</a>';
                html += '</div>';
            }

            html += '</div>'; // End right column
            html += '</div>'; // End product container

            // Render the content
            self.content.html(html);
            self.loading.hide();
            self.updateNavigation();

            // Trigger event
            $(document.body).trigger('rmenu_quick_view_opened', [productData.id]);

            self.isLoading = false;
        },

        /**
         * Load product content via AJAX (fallback)
         */
        loadProductContent: function (productId) {
            var self = this;

            // Add AJAX fallback implementation if needed
            self.content.html('<div class="rmenu-quick-view-error">Error loading product information. Please refresh and try again.</div>');
            self.loading.hide();
            self.isLoading = false;
        },

        /**
         * Show the modal with animation effect
         */
        showModal: function () {
            var self = this;

            // Apply effect based on settings
            switch (self.settings.effect) {
                case 'slide':
                    self.modal.addClass('active slide-in');
                    break;

                case 'zoom':
                    self.modal.addClass('active zoom-in');
                    break;

                case 'none':
                    self.modal.addClass('active');
                    break;

                default: // fade
                    self.modal.addClass('active fade-in');
                    break;
            }

            // Add body class
            $('body').addClass('rmenu-quick-view-active');
        },

        /**
         * Close the quick view modal
         */
        closeQuickView: function () {
            var self = this;

            // Remove effect classes
            self.modal.removeClass('active slide-in zoom-in fade-in');

            // Clear content after animation
            setTimeout(function () {
                self.content.empty();
                self.currentProductId = null;
                $('body').removeClass('rmenu-quick-view-active');

                // Trigger closed event
                $(document.body).trigger('rmenu_quick_view_closed');
            }, 300);
        },

        /**
         * Navigate to previous/next product
         */
        navigateProduct: function (direction) {
            var self = this; // Store reference to 'this'

            if (self.isLoading || !self.currentProductId) {
                return;
            }

            var $productItems = $('.products li.product');
            var currentIndex = -1;

            // Find the current product index
            $productItems.each(function (index) {
                var pid = $(this).find('.rmenu-quick-view-btn').data('product-id');
                if (pid == self.currentProductId) { // Use self instead of this
                    currentIndex = index;
                    return false;
                }
            });

            if (currentIndex === -1) {
                return;
            }

            var newIndex;
            if (direction === 'prev') {
                newIndex = currentIndex - 1;
                if (newIndex < 0) {
                    newIndex = $productItems.length - 1;
                }
            } else {
                newIndex = currentIndex + 1;
                if (newIndex >= $productItems.length) {
                    newIndex = 0;
                }
            }

            var $nextProduct = $productItems.eq(newIndex);
            var nextProductId = $nextProduct.find('.rmenu-quick-view-btn').data('product-id');

            if (nextProductId) {
                self.openQuickView(nextProductId);
            }
        },

        /**
         * Update the navigation buttons visibility
         */
        updateNavigation: function () {
            var $productItems = $('.products li.product');

            if ($productItems.length <= 1) {
                this.prevBtn.hide();
                this.nextBtn.hide();
            } else {
                this.prevBtn.show();
                this.nextBtn.show();
            }
        },

        /**
         * Initialize WooCommerce scripts
         */
        initWooScripts: function () {
            // Reinitialize variation forms
            if (typeof $.fn.wc_variation_form !== 'undefined') {
                this.modal.find('.variations_form').each(function () {
                    $(this).wc_variation_form();
                });
            }

            // Reinitialize add to cart quantity buttons
            if (typeof $.fn.trigger !== 'undefined') {
                $(document.body).trigger('init_add_to_cart_quantity');
            }
        },

        /**
         * Initialize lightbox functionality
         */
        initLightbox: function () {
            if (typeof $.fn.prettyPhoto !== 'undefined') {
                this.modal.on('click', '.rmenu-quick-view-lightbox', function (e) {
                    e.preventDefault();

                    var $this = $(this);
                    var items = [{
                        src: $this.attr('href'),
                        title: $this.data('caption') || ''
                    }];

                    $.prettyPhoto.open(items);
                    return false;
                });
            } else if (typeof $.fn.magnificPopup !== 'undefined') {
                this.modal.on('click', '.rmenu-quick-view-lightbox', function (e) {
                    e.preventDefault();

                    var $this = $(this);
                    $.magnificPopup.open({
                        items: {
                            src: $this.attr('href')
                        },
                        type: 'image'
                    });
                    return false;
                });
            }
        },

        /**
         * Mobile-specific optimizations
         */
        mobileOptimize: function () {
            var self = this;

            // Check if we're on mobile
            if (window.matchMedia('(max-width: 768px)').matches) {
                // Adjust modal styles for mobile
                self.modal.addClass('rmenu-quick-view-mobile');
            }

            // Handle resize events
            $(window).on('resize', function () {
                if (window.matchMedia('(max-width: 768px)').matches) {
                    self.modal.addClass('rmenu-quick-view-mobile');
                } else {
                    self.modal.removeClass('rmenu-quick-view-mobile');
                }
            });
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function () {
        // Initialize the Quick View
        RMenuQuickView.init();

        // Make it globally accessible
        window.rmenuQuickView = RMenuQuickView;
    });

})(jQuery);