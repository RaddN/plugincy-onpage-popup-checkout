<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
global $onepaquc_onepaquc_allowed_tags;

$onepaquc_onepaquc_allowed_tags = array(
    'a' => array(
        'href' => array(),
        'title' => array(),
        'class' => array(),
        'target' => array(), // Allow target attribute for links
        'style' => array(),
        'id' => array(),
    ),
    'strong' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ),
    'em' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ),
    'li' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
        'data-product-id' => array(),
        'data-cart-item-key' => array(),
    ),
    'div' => array(
        'class' => array(),
        'id' => array(), // Allow id for divs
        'style' => array(),
    ),
    'img' => array(
        'src' => array(),
        'alt' => array(),
        'class' => array(),
        'width' => array(), // Allow width attribute
        'height' => array(), // Allow height attribute
        'style' => array(),
        'id' => array(),
        'srcset' => array(),
        'sizes' => array(),
        'loading' => array(),
    ),
    'h1' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ), // Allow h1
    'h2' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ),
    'h3' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ), // Allow h3
    'h4' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ), // Allow h4
    'h5' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ), // Allow h5
    'h6' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ), // Allow h6
    'span' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ),
    'p' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ),
    'br' => array(
        'style' => array(),
        'class' => array(),
    ), // Allow line breaks
    'blockquote' => array(
        'cite' => array(), // Allow cite attribute for blockquotes
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ),
    'table' => array(
        'class' => array(),
        'style' => array(), // Allow inline styles
        'id' => array(),
    ),
    'tr' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ),
    'td' => array(
        'class' => array(),
        'colspan' => array(), // Allow colspan attribute
        'rowspan' => array(), // Allow rowspan attribute
        'style' => array(),
        'id' => array(),
    ),
    'th' => array(
        'class' => array(),
        'colspan' => array(),
        'rowspan' => array(),
        'style' => array(),
        'id' => array(),
    ),
    'ul' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ), // Allow unordered lists
    'ol' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
    ), // Allow ordered lists
    'script' => array(
        'type' => array(),
        'src' => array(),
        'async' => array(),
        'defer' => array(),
        'charset' => array(),
    ), // Be cautious with scripts

    // Style and Meta Tags
    'style' => array(
        'type' => array(),
        'media' => array(),
        'scoped' => array(),
    ),
    'link' => array(
        'rel' => array(),
        'href' => array(),
        'type' => array(),
        'media' => array(),
        'sizes' => array(),
        'hreflang' => array(),
        'crossorigin' => array(),
    ),
    'meta' => array(
        'name' => array(),
        'content' => array(),
        'http-equiv' => array(),
        'charset' => array(),
        'property' => array(), // For Open Graph
    ),
    'title' => array(),
    'base' => array(
        'href' => array(),
        'target' => array(),
    ),

    // Document Structure
    'html' => array(
        'lang' => array(),
        'dir' => array(),
        'class' => array(),
        'style' => array(),
    ),
    'head' => array(),
    'body' => array(
        'class' => array(),
        'id' => array(),
        'style' => array(),
        'onload' => array(),
    ),
    'header' => array(
        'class' => array(),
        'id' => array(),
        'style' => array(),
        'role' => array(),
    ),
    'footer' => array(
        'class' => array(),
        'id' => array(),
        'style' => array(),
        'role' => array(),
    ),
    'nav' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
        'role' => array(),
    ),
    'main' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
        'role' => array(),
    ),
    'section' => array(
        'class' => array(),
        'id' => array(),
        'style' => array(),
        'role' => array(),
    ),
    'article' => array(
        'class' => array(),
        'style' => array(),
        'id' => array(),
        'role' => array(),
    ),
    'aside' => array(
        'class' => array(),
        'id' => array(),
        'style' => array(),
        'role' => array(),
    ),

    // Form Elements
    'form' => array(
        'action' => array(),
        'method' => array(),
        'style' => array(),
        'enctype' => array(),
        'target' => array(),
        'name' => array(),
        'id' => array(),
        'class' => array(),
        'autocomplete' => array(),
        'novalidate' => array(),
        'data-mobile-style' => array(),
        'data-product_show_settings' => array(),
        'data-product_selector' => array(),
        'data-pagination_selector' => array(),
        'data-layout' => array(),
    ),
    'input' => array(
        'type' => array(),
        'name' => array(),
        'value' => array(),
        'style' => array(),
        'placeholder' => array(),
        'id' => array(),
        'class' => array(),
        'required' => array(),
        'disabled' => array(),
        'readonly' => array(),
        'checked' => array(),
        'selected' => array(),
        'multiple' => array(),
        'min' => array(),
        'max' => array(),
        'step' => array(),
        'pattern' => array(),
        'maxlength' => array(),
        'minlength' => array(),
        'size' => array(),
        'autocomplete' => array(),
        'autofocus' => array(),
        'form' => array(),
        'formaction' => array(),
        'formmethod' => array(),
        'formtarget' => array(),
        'formnovalidate' => array(),
        'accept' => array(),
        'alt' => array(),
        'src' => array(),
        'width' => array(),
        'height' => array(),
    ),
    'textarea' => array(
        'name' => array(),
        'id' => array(),
        'class' => array(),
        'placeholder' => array(),
        'rows' => array(),
        'style' => array(),
        'cols' => array(),
        'required' => array(),
        'disabled' => array(),
        'readonly' => array(),
        'maxlength' => array(),
        'minlength' => array(),
        'wrap' => array(),
        'autocomplete' => array(),
        'autofocus' => array(),
        'form' => array(),
    ),
    'select' => array(
        'name' => array(),
        'id' => array(),
        'class' => array(),
        'multiple' => array(),
        'size' => array(),
        'required' => array(),
        'style' => array(),
        'disabled' => array(),
        'autofocus' => array(),
        'form' => array(),
    ),
    'option' => array(
        'value' => array(),
        'selected' => array(),
        'style' => array(),
        'disabled' => array(),
        'label' => array(),
    ),
    'optgroup' => array(
        'label' => array(),
        'style' => array(),
        'disabled' => array(),
    ),
    'button' => array(
        'type' => array(),
        'name' => array(),
        'value' => array(),
        'id' => array(),
        'style' => array(),
        'class' => array(),
        'disabled' => array(),
        'form' => array(),
        'formaction' => array(),
        'formmethod' => array(),
        'formtarget' => array(),
        'formnovalidate' => array(),
        'autofocus' => array(),
    ),
    'label' => array(
        'for' => array(),
        'form' => array(),
        'id' => array(),
        'class' => array(),
        'style' => array(),
    ),
    'fieldset' => array(
        'disabled' => array(),
        'form' => array(),
        'style' => array(),
        'name' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'legend' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'datalist' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'output' => array(
        'for' => array(),
        'form' => array(),
        'name' => array(),
        'style' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'plugrogress' => array(
        'value' => array(),
        'max' => array(),
        'style' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'meter' => array(
        'value' => array(),
        'min' => array(),
        'max' => array(),
        'low' => array(),
        'style' => array(),
        'high' => array(),
        'optimum' => array(),
        'id' => array(),
        'class' => array(),
    ),

    // Media Elements
    'audio' => array(
        'src' => array(),
        'controls' => array(),
        'autoplay' => array(),
        'style' => array(),
        'loop' => array(),
        'muted' => array(),
        'preload' => array(),
        'crossorigin' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'video' => array(
        'src' => array(),
        'controls' => array(),
        'autoplay' => array(),
        'loop' => array(),
        'muted' => array(),
        'preload' => array(),
        'style' => array(),
        'poster' => array(),
        'width' => array(),
        'height' => array(),
        'crossorigin' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'source' => array(
        'src' => array(),
        'style' => array(),
        'type' => array(),
        'media' => array(),
        'sizes' => array(),
        'srcset' => array(),
    ),
    'track' => array(
        'kind' => array(),
        'src' => array(),
        'style' => array(),
        'srclang' => array(),
        'label' => array(),
        'default' => array(),
    ),
    'embed' => array(
        'src' => array(),
        'type' => array(),
        'width' => array(),
        'height' => array(),
        'style' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'object' => array(
        'data' => array(),
        'type' => array(),
        'style' => array(),
        'name' => array(),
        'width' => array(),
        'height' => array(),
        'form' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'param' => array(
        'name' => array(),
        'value' => array(),
        'style' => array(),
    ),
    'iframe' => array(
        'src' => array(),
        'srcdoc' => array(),
        'name' => array(),
        'width' => array(),
        'style' => array(),
        'height' => array(),
        'sandbox' => array(),
        'allow' => array(),
        'allowfullscreen' => array(),
        'loading' => array(),
        'id' => array(),
        'class' => array(),
    ),

    // Interactive Elements
    'details' => array(
        'open' => array(),
        'id' => array(),
        'class' => array(),
        'style' => array(),
    ),
    'summary' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'dialog' => array(
        'open' => array(),
        'style' => array(),
        'id' => array(),
        'class' => array(),
    ),

    // Text Content Elements
    'pre' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'code' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'kbd' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'samp' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'var' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'small' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'sub' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'sup' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'mark' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'del' => array(
        'datetime' => array(),
        'style' => array(),
        'cite' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'ins' => array(
        'datetime' => array(),
        'style' => array(),
        'cite' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'q' => array(
        'cite' => array(),
        'style' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'cite' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'abbr' => array(
        'title' => array(),
        'style' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'dfn' => array(
        'title' => array(),
        'style' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'time' => array(
        'datetime' => array(),
        'style' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'data' => array(
        'value' => array(),
        'style' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'address' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),

    // Table Elements (Enhanced)
    'caption' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'thead' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'tbody' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'tfoot' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'colgroup' => array(
        'span' => array(),
        'style' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'col' => array(
        'span' => array(),
        'style' => array(),
        'id' => array(),
        'class' => array(),
    ),

    // Definition Lists
    'dl' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'dt' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'dd' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),

    // Ruby Annotations
    'ruby' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'rt' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'rp' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),

    // Bidirectional Text
    'bdi' => array(
        'dir' => array(),
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'bdo' => array(
        'dir' => array(),
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),

    // Web Components
    'template' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'slot' => array(
        'name' => array(),
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),

    // Math and Science
    'math' => array(
        'display' => array(),
        'xmlns' => array(),
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),

    // Canvas and Graphics
    'canvas' => array(
        'width' => array(),
        'height' => array(),
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),

    // Obsolete but sometimes needed
    'center' => array(
        'id' => array(),
        'style' => array(),
        'class' => array(),
    ),
    'font' => array(
        'size' => array(),
        'style' => array(),
        'color' => array(),
        'face' => array(),
        'id' => array(),
        'class' => array(),
    ),

    // SVG Tags
    'svg' => array(
        'xmlns' => array(),
        'viewbox' => array(), // lowercase
        'viewBox' => array(), // camelCase (standard)
        'width' => array(),
        'height' => array(),
        'class' => array(),
        'id' => array(),
        'style' => array(),
        'preserveAspectRatio' => array(),
        'version' => array(),
        'x' => array(),
        'y' => array(),
    ),
    'g' => array(
        'class' => array(),
        'id' => array(),
        'transform' => array(),
        'style' => array(),
        'fill' => array(),
        'stroke' => array(),
        'opacity' => array(),
    ),
    'path' => array(
        'd' => array(),
        'class' => array(),
        'id' => array(),
        'fill' => array(),
        'stroke' => array(),
        'stroke-width' => array(),
        'stroke-dasharray' => array(),
        'stroke-linecap' => array(),
        'stroke-linejoin' => array(),
        'opacity' => array(),
        'transform' => array(),
        'style' => array(),
    ),
    'circle' => array(
        'cx' => array(),
        'cy' => array(),
        'r' => array(),
        'class' => array(),
        'id' => array(),
        'fill' => array(),
        'stroke' => array(),
        'stroke-width' => array(),
        'opacity' => array(),
        'transform' => array(),
        'style' => array(),
    ),
    'ellipse' => array(
        'cx' => array(),
        'cy' => array(),
        'rx' => array(),
        'ry' => array(),
        'class' => array(),
        'id' => array(),
        'fill' => array(),
        'stroke' => array(),
        'stroke-width' => array(),
        'opacity' => array(),
        'transform' => array(),
        'style' => array(),
    ),
    'rect' => array(
        'x' => array(),
        'y' => array(),
        'width' => array(),
        'height' => array(),
        'rx' => array(),
        'ry' => array(),
        'class' => array(),
        'id' => array(),
        'fill' => array(),
        'stroke' => array(),
        'stroke-width' => array(),
        'opacity' => array(),
        'transform' => array(),
        'style' => array(),
    ),
    'line' => array(
        'x1' => array(),
        'y1' => array(),
        'x2' => array(),
        'y2' => array(),
        'class' => array(),
        'id' => array(),
        'stroke' => array(),
        'stroke-width' => array(),
        'stroke-dasharray' => array(),
        'stroke-linecap' => array(),
        'opacity' => array(),
        'transform' => array(),
        'style' => array(),
    ),
    'polyline' => array(
        'points' => array(),
        'class' => array(),
        'id' => array(),
        'fill' => array(),
        'stroke' => array(),
        'stroke-width' => array(),
        'stroke-dasharray' => array(),
        'stroke-linecap' => array(),
        'stroke-linejoin' => array(),
        'opacity' => array(),
        'transform' => array(),
        'style' => array(),
    ),
    'polygon' => array(
        'points' => array(),
        'class' => array(),
        'id' => array(),
        'fill' => array(),
        'stroke' => array(),
        'stroke-width' => array(),
        'stroke-dasharray' => array(),
        'stroke-linecap' => array(),
        'stroke-linejoin' => array(),
        'opacity' => array(),
        'transform' => array(),
        'style' => array(),
    ),
    'text' => array(
        'x' => array(),
        'y' => array(),
        'dx' => array(),
        'dy' => array(),
        'class' => array(),
        'id' => array(),
        'fill' => array(),
        'stroke' => array(),
        'font-family' => array(),
        'font-size' => array(),
        'font-weight' => array(),
        'text-anchor' => array(),
        'dominant-baseline' => array(),
        'opacity' => array(),
        'transform' => array(),
        'style' => array(),
    ),
    'tspan' => array(
        'x' => array(),
        'y' => array(),
        'dx' => array(),
        'dy' => array(),
        'class' => array(),
        'id' => array(),
        'fill' => array(),
        'stroke' => array(),
        'font-family' => array(),
        'font-size' => array(),
        'font-weight' => array(),
        'text-anchor' => array(),
        'dominant-baseline' => array(),
        'opacity' => array(),
        'style' => array(),
    ),
    'use' => array(
        'href' => array(),
        'xlink:href' => array(),
        'x' => array(),
        'y' => array(),
        'width' => array(),
        'height' => array(),
        'class' => array(),
        'id' => array(),
        'transform' => array(),
        'style' => array(),
    ),
    'defs' => array(
        'class' => array(),
        'id' => array(),
        'style' => array(),
    ),
    'symbol' => array(
        'id' => array(),
        'viewBox' => array(),
        'class' => array(),
        'style' => array(),
        'preserveAspectRatio' => array(),
    ),
    'marker' => array(
        'id' => array(),
        'markerWidth' => array(),
        'markerHeight' => array(),
        'refX' => array(),
        'refY' => array(),
        'style' => array(),
        'orient' => array(),
        'markerUnits' => array(),
        'class' => array(),
    ),
    'linearGradient' => array(
        'id' => array(),
        'x1' => array(),
        'y1' => array(),
        'style' => array(),
        'x2' => array(),
        'y2' => array(),
        'gradientUnits' => array(),
        'gradientTransform' => array(),
        'class' => array(),
    ),
    'lineargradient' => array(
        'id' => array(),
        'x1' => array(),
        'y1' => array(),
        'style' => array(),
        'x2' => array(),
        'y2' => array(),
        'gradientUnits' => array(),
        'gradientTransform' => array(),
        'class' => array(),
    ),
    'radialGradient' => array(
        'id' => array(),
        'cx' => array(),
        'cy' => array(),
        'style' => array(),
        'r' => array(),
        'fx' => array(),
        'fy' => array(),
        'gradientUnits' => array(),
        'gradientTransform' => array(),
        'class' => array(),
    ),
    'radialgradient' => array(
        'id' => array(),
        'cx' => array(),
        'cy' => array(),
        'r' => array(),
        'style' => array(),
        'fx' => array(),
        'fy' => array(),
        'gradientUnits' => array(),
        'gradientTransform' => array(),
        'class' => array(),
    ),
    'stop' => array(
        'offset' => array(),
        'stop-color' => array(),
        'stop-opacity' => array(),
        'class' => array(),
        'style' => array(),
    ),
    'clipPath' => array(
        'id' => array(),
        'class' => array(),
        'style' => array(),
        'clipPathUnits' => array(),
    ),
    'mask' => array(
        'id' => array(),
        'class' => array(),
        'style' => array(),
        'maskUnits' => array(),
        'maskContentUnits' => array(),
        'x' => array(),
        'y' => array(),
        'width' => array(),
        'height' => array(),
    ),
    'pattern' => array(
        'id' => array(),
        'x' => array(),
        'y' => array(),
        'width' => array(),
        'style' => array(),
        'height' => array(),
        'patternUnits' => array(),
        'patternContentUnits' => array(),
        'patternTransform' => array(),
        'viewBox' => array(),
        'class' => array(),
    ),
    'filter' => array(
        'id' => array(),
        'x' => array(),
        'y' => array(),
        'style' => array(),
        'width' => array(),
        'height' => array(),
        'filterUnits' => array(),
        'primitiveUnits' => array(),
        'class' => array(),
    ),
    'feGaussianBlur' => array(
        'in' => array(),
        'style' => array(),
        'stdDeviation' => array(),
        'result' => array(),
    ),
    'feOffset' => array(
        'in' => array(),
        'dx' => array(),
        'style' => array(),
        'dy' => array(),
        'result' => array(),
    ),
    'feDropShadow' => array(
        'dx' => array(),
        'dy' => array(),
        'style' => array(),
        'stdDeviation' => array(),
        'flood-color' => array(),
        'flood-opacity' => array(),
    ),
    'image' => array(
        'x' => array(),
        'y' => array(),
        'width' => array(),
        'style' => array(),
        'height' => array(),
        'href' => array(),
        'xlink:href' => array(),
        'preserveAspectRatio' => array(),
        'class' => array(),
        'id' => array(),
        'opacity' => array(),
        'transform' => array(),
    ),
);


class onepaquc_helper
{
    public function tooltip($content)
    { ?>
        <span class="tooltip">
            <span class="question-mark">?</span>
            <span class="tooltip-text"><?php echo wp_kses_post($content); ?></span>
        </span>
    <?php
    }

    public function sec_head($headtag, $class, $icon, $title, $tooltip = '', $description = '')
    {
        global $onepaquc_onepaquc_allowed_tags;
        echo '<' . esc_html($headtag) . ' class="' . esc_html($class) . '">';

        // Check if icon is not empty
        if (!empty($icon)) {
            echo '<span class="plugincy_sec_icon">' . wp_kses($icon, $onepaquc_onepaquc_allowed_tags) . '</span>';
        }

        echo '<span class="plugincy_sec_title">' . esc_html($title);

        // Add description if it exists
        if (!empty($description)) {
            echo '<p style="margin: 0; margin-top:4px; font-weight:400;">' . esc_html($description) . '</p>';
        }

        echo '</span>';

        // Add tooltip if it exists
        if (!empty($tooltip)) {
            $this->tooltip($tooltip);
        }

        echo '</' . esc_html($headtag) . '>';
    }

    public function switcher($name, $default = 1, $notice = "", $is_paid = false)
    { ?>
        <label class="switch">
            <input <?php echo $is_paid ? 'disabled' : ''; ?> data-notice="<?php echo esc_html($notice); ?>" type="checkbox" name="<?php echo esc_attr($name); ?>" value="1" <?php checked(1, get_option($name, $default), true); ?> />
            <span class="slider round"></span>
        </label>
<?php
    }
}
