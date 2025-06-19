<?php
if (!defined('ABSPATH')) exit;

/**
 * Renders the Plugincy Cart documentation page
 * 
 * @since 1.0.5
 * @return void
 */
function onepaquc_cart_documentation()
{
?>
    <div class="wrap">
        <div class="plugincy-docs">
            <div class="plugincy-header">
                <h1>Plugincy Cart Documentation</h1>
                <p>Comprehensive guide to using the Plugincy Cart plugin for WooCommerce</p>
            </div>

            <div class="plugincy-docs-sidebar">
                <div class="plugincy-toc">
                    <h2>Table of Contents</h2>
                    <ul class="plugincy-toc-list">
                        <li><a href="#introduction">Introduction</a></li>
                        <li><a href="#features">Features</a></li>
                        <li>
                            <a href="#menu-cart">Menu Cart Integration</a>
                            <ul>
                                <li><a href="#shortcode">Using Shortcode</a></li>
                                <li><a href="#elementor">Using Elementor</a></li>
                                <li><a href="#gutenberg">Using Gutenberg</a></li>
                                <li><a href="#widget">Using Widget</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="#one-page-checkout">One Page Checkout</a>
                            <ul>
                                <li><a href="#single-product">Single Product Checkout</a></li>
                                <li><a href="#multiple-products">Multiple Products Checkout</a></li>
                                <li><a href="#templates">Available Templates</a></li>
                            </ul>
                        </li>
                        <li><a href="#plugins">Recommended Plugins</a></li>
                        <li><a href="#shortcodes">Shortcodes Reference</a></li>
                        <li><a href="#elementor-int">Elementor Integration</a></li>
                        <li><a href="#gutenberg-int">Gutenberg Integration</a></li>
                        <li><a href="#support">Support</a></li>
                    </ul>
                </div>
            </div>

            <div class="plugincy-docs-content">
                <div id="introduction" class="plugincy-section">
                    <h2>Introduction</h2>
                    <p>Plugincy Cart is a powerful WooCommerce extension that enhances your store's checkout experience with menu cart functionality and one-page checkout options. This documentation will guide you through the features and implementation of the plugin.</p>
                </div>

                <div id="features" class="plugincy-section">
                    <h2>Features</h2>
                    <ul>
                        <li>Menu cart integration for header/navigation</li>
                        <li>Single product one-page checkout</li>
                        <li>Multiple products one-page checkout with various display templates</li>
                        <li>Elementor widgets integration</li>
                        <li>Gutenberg blocks support</li>
                        <li>Widget area support</li>
                    </ul>
                </div>

                <div id="menu-cart" class="plugincy-section">
                    <h2>Menu Cart Integration</h2>
                    <p>Adding a cart to your menu or any part of your page is simple with multiple integration options:</p>

                    <div id="shortcode" class="plugincy-card">
                        <h3 class="plugincy-card-title">Using Shortcode</h3>
                        <p>Add the following shortcode to any page or post:</p>
                        <pre><code>[plugincy_cart]</code></pre>
                    </div>

                    <div id="elementor" class="plugincy-card">
                        <h3 class="plugincy-card-title">Using Elementor</h3>
                        <div class="plugincy-step">Edit your page with Elementor</div>
                        <div class="plugincy-step">Search for "Plugincy WC Cart" widget</div>
                        <div class="plugincy-step">Drag and drop the widget to your desired location</div>
                    </div>

                    <div id="gutenberg" class="plugincy-card">
                        <h3 class="plugincy-card-title">Using Gutenberg</h3>
                        <div class="plugincy-step">Edit your page with Gutenberg editor</div>
                        <div class="plugincy-step">Search for "Plugincy WC Cart" block</div>
                        <div class="plugincy-step">Add the block to your desired location</div>
                    </div>

                    <div id="widget" class="plugincy-card">
                        <h3 class="plugincy-card-title">Using Widget</h3>
                        <div class="plugincy-step">Go to Appearance > Widgets</div>
                        <div class="plugincy-step">Find "Plugincy WC Cart" widget</div>
                        <div class="plugincy-step">Add it to your desired widget area</div>
                    </div>
                </div>

                <div id="one-page-checkout" class="plugincy-section">
                    <h2>One Page Checkout</h2>

                    <div id="single-product" class="plugincy-card">
                        <h3 class="plugincy-card-title">Single Product One Page Checkout</h3>
                        <p>To enable one-page checkout for a specific product:</p>
                        <div class="plugincy-step">Edit the product in WooCommerce</div>
                        <div class="plugincy-step">Check the "One Page Checkout" option in the product data panel</div>
                        <div class="plugincy-step">Save the product</div>

                        <p>To customize the checkout form position and settings:</p>
                        <div class="plugincy-step">Navigate to On page checkout</div>
                        <div class="plugincy-step">Go to the "One Page Checkout" tab</div>
                        <div class="plugincy-step">Adjust the position and other settings as needed</div>
                    </div>

                    <div id="multiple-products" class="plugincy-card">
                        <h3 class="plugincy-card-title">Multiple Products One Page Checkout</h3>
                        <p>You can create a one-page checkout with multiple selected products using shortcodes or blocks.</p>
                        
                        <h4>Shortcode Example</h4>
                        <pre><code>[plugincy_one_page_checkout product_ids="152,153,151,142" template="product-tabs"]</code></pre>
                        
                        <h4>Parameters</h4>
                        <ul>
                            <li><code>product_ids</code>: Comma-separated list of product IDs to include</li>
                            <li><code>template</code>: Display template (see Available Templates section)</li>
                        </ul>
                    </div>

                    <div id="templates">
                        <h3>Available Templates</h3>
                        <p>Plugincy Cart offers several templates for displaying products in one-page checkout:</p>

                        <div class="plugincy-templates">
                            <div class="plugincy-template">
                                <h4>product-table</h4>
                                <p>Displays products in a table format showing product image, name, price, and add to cart button.</p>
                            </div>

                            <div class="plugincy-template">
                                <h4>product-list</h4>
                                <p>Shows products in a list with checkboxes for selection.</p>
                            </div>

                            <div class="plugincy-template">
                                <h4>product-single</h4>
                                <p>Displays products in a standard single product layout.</p>
                            </div>

                            <div class="plugincy-template">
                                <h4>product-slider</h4>
                                <p>Presents products in a carousel/slider format.</p>
                            </div>

                            <div class="plugincy-template">
                                <h4>product-accordion</h4>
                                <p>Organizes products in an accordion interface.</p>
                            </div>

                            <div class="plugincy-template">
                                <h4>product-tabs</h4>
                                <p>Arranges products in a tabbed interface.</p>
                            </div>

                            <div class="plugincy-template">
                                <h4>pricing-table</h4>
                                <p>Displays products in a comparison table style.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="plugins" class="plugincy-section">
                    <h2>Recommended Plugins</h2>
                    <div class="plugincy-note">
                        <div class="plugincy-note-title">For Enhanced Functionality</div>
                        <p>For better performance, consider installing the <a href="https://wordpress.org/plugins/woo-checkout-field-editor-pro/" target="_blank">Checkout Field Editor Pro</a> plugin. This helps customize your checkout fields efficiently.</p>
                    </div>
                </div>

                <div id="shortcodes" class="plugincy-section">
                    <h2>Shortcodes Reference</h2>
                    <table class="plugincy-table">
                        <thead>
                            <tr>
                                <th>Shortcode</th>
                                <th>Description</th>
                                <th>Parameters</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>[plugincy_cart]</code></td>
                                <td>Displays a mini cart</td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><code>plugincy_one_page_checkout]</code></td>
                                <td>Creates a one-page checkout</td>
                                <td><code>product_ids</code>, <code>template</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="elementor-int" class="plugincy-section">
                    <h2>Elementor Integration</h2>
                    <p>Plugincy Cart provides two dedicated Elementor widgets:</p>
                    <ol>
                        <li><strong>Plugincy WC Cart</strong> - For adding a mini cart</li>
                        <li><strong>Plugincy One Page Checkout</strong> - For creating Multiple Products One Page Checkout</li>
                    </ol>
                </div>

                <div id="gutenberg-int" class="plugincy-section">
                    <h2>Gutenberg Integration</h2>
                    <p>Plugincy Cart provides two dedicated Gutenberg blocks:</p>
                    <ol>
                        <li><strong>Plugincy WC Cart</strong> - For adding a mini cart</li>
                        <li><strong>Plugincy One Page Checkout</strong> - For creating Multiple Products One Page Checkout</li>
                    </ol>
                </div>

                <div id="support" class="plugincy-section">
                    <h2>Support</h2>
                    <p>If you encounter any issues or have questions about Plugincy Cart, please contact our support team at <a href="mailto:support@plugincy.com">support@plugincy.com</a> or visit our <a href="https://plugincy.com/support" target="_blank">support forum</a>.</p>
                </div>
            </div>
        </div>
    </div>
<?php
}


