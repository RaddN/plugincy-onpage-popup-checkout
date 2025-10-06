<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
global $onepaquc_allowed_tags;

$onepaquc_allowed_tags = array(
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
        'role' => array(),
    ),
    'footer' => array(
        'class' => array(),
        'id' => array(),
        'role' => array(),
    ),
    'nav' => array(
        'class' => array(),
        'id' => array(),
        'role' => array(),
    ),
    'main' => array(
        'class' => array(),
        'id' => array(),
        'role' => array(),
    ),
    'section' => array(
        'class' => array(),
        'id' => array(),
        'role' => array(),
    ),
    'article' => array(
        'class' => array(),
        'id' => array(),
        'role' => array(),
    ),
    'aside' => array(
        'class' => array(),
        'id' => array(),
        'role' => array(),
    ),
    
    // Form Elements
    'form' => array(
        'action' => array(),
        'method' => array(),
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
        'disabled' => array(),
        'autofocus' => array(),
        'form' => array(),
    ),
    'option' => array(
        'value' => array(),
        'selected' => array(),
        'disabled' => array(),
        'label' => array(),
    ),
    'optgroup' => array(
        'label' => array(),
        'disabled' => array(),
    ),
    'button' => array(
        'type' => array(),
        'name' => array(),
        'value' => array(),
        'id' => array(),
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
    ),
    'fieldset' => array(
        'disabled' => array(),
        'form' => array(),
        'name' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'legend' => array(
        'id' => array(),
        'class' => array(),
    ),
    'datalist' => array(
        'id' => array(),
        'class' => array(),
    ),
    'output' => array(
        'for' => array(),
        'form' => array(),
        'name' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'plugrogress' => array(
        'value' => array(),
        'max' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'meter' => array(
        'value' => array(),
        'min' => array(),
        'max' => array(),
        'low' => array(),
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
        'poster' => array(),
        'width' => array(),
        'height' => array(),
        'crossorigin' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'source' => array(
        'src' => array(),
        'type' => array(),
        'media' => array(),
        'sizes' => array(),
        'srcset' => array(),
    ),
    'track' => array(
        'kind' => array(),
        'src' => array(),
        'srclang' => array(),
        'label' => array(),
        'default' => array(),
    ),
    'embed' => array(
        'src' => array(),
        'type' => array(),
        'width' => array(),
        'height' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'object' => array(
        'data' => array(),
        'type' => array(),
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
    ),
    'iframe' => array(
        'src' => array(),
        'srcdoc' => array(),
        'name' => array(),
        'width' => array(),
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
    ),
    'summary' => array(
        'id' => array(),
        'class' => array(),
    ),
    'dialog' => array(
        'open' => array(),
        'id' => array(),
        'class' => array(),
    ),
    
    // Text Content Elements
    'pre' => array(
        'id' => array(),
        'class' => array(),
    ),
    'code' => array(
        'id' => array(),
        'class' => array(),
    ),
    'kbd' => array(
        'id' => array(),
        'class' => array(),
    ),
    'samp' => array(
        'id' => array(),
        'class' => array(),
    ),
    'var' => array(
        'id' => array(),
        'class' => array(),
    ),
    'small' => array(
        'id' => array(),
        'class' => array(),
    ),
    'sub' => array(
        'id' => array(),
        'class' => array(),
    ),
    'sup' => array(
        'id' => array(),
        'class' => array(),
    ),
    'mark' => array(
        'id' => array(),
        'class' => array(),
    ),
    'del' => array(
        'datetime' => array(),
        'cite' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'ins' => array(
        'datetime' => array(),
        'cite' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'q' => array(
        'cite' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'cite' => array(
        'id' => array(),
        'class' => array(),
    ),
    'abbr' => array(
        'title' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'dfn' => array(
        'title' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'time' => array(
        'datetime' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'data' => array(
        'value' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'address' => array(
        'id' => array(),
        'class' => array(),
    ),
    
    // Table Elements (Enhanced)
    'caption' => array(
        'id' => array(),
        'class' => array(),
    ),
    'thead' => array(
        'id' => array(),
        'class' => array(),
    ),
    'tbody' => array(
        'id' => array(),
        'class' => array(),
    ),
    'tfoot' => array(
        'id' => array(),
        'class' => array(),
    ),
    'colgroup' => array(
        'span' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'col' => array(
        'span' => array(),
        'id' => array(),
        'class' => array(),
    ),
    
    // Definition Lists
    'dl' => array(
        'id' => array(),
        'class' => array(),
    ),
    'dt' => array(
        'id' => array(),
        'class' => array(),
    ),
    'dd' => array(
        'id' => array(),
        'class' => array(),
    ),
    
    // Ruby Annotations
    'ruby' => array(
        'id' => array(),
        'class' => array(),
    ),
    'rt' => array(
        'id' => array(),
        'class' => array(),
    ),
    'rp' => array(
        'id' => array(),
        'class' => array(),
    ),
    
    // Bidirectional Text
    'bdi' => array(
        'dir' => array(),
        'id' => array(),
        'class' => array(),
    ),
    'bdo' => array(
        'dir' => array(),
        'id' => array(),
        'class' => array(),
    ),
    
    // Web Components
    'template' => array(
        'id' => array(),
        'class' => array(),
    ),
    'slot' => array(
        'name' => array(),
        'id' => array(),
        'class' => array(),
    ),
    
    // Math and Science
    'math' => array(
        'display' => array(),
        'xmlns' => array(),
        'id' => array(),
        'class' => array(),
    ),
    
    // Canvas and Graphics
    'canvas' => array(
        'width' => array(),
        'height' => array(),
        'id' => array(),
        'class' => array(),
    ),
    
    // Obsolete but sometimes needed
    'center' => array(
        'id' => array(),
        'class' => array(),
    ),
    'font' => array(
        'size' => array(),
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
        'fill' => array(),
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
    ),
    'symbol' => array(
        'id' => array(),
        'viewBox' => array(),
        'class' => array(),
        'preserveAspectRatio' => array(),
    ),
    'marker' => array(
        'id' => array(),
        'markerWidth' => array(),
        'markerHeight' => array(),
        'refX' => array(),
        'refY' => array(),
        'orient' => array(),
        'markerUnits' => array(),
        'class' => array(),
    ),
    'linearGradient' => array(
        'id' => array(),
        'x1' => array(),
        'y1' => array(),
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
        'clipPathUnits' => array(),
    ),
    'mask' => array(
        'id' => array(),
        'class' => array(),
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
        'width' => array(),
        'height' => array(),
        'filterUnits' => array(),
        'primitiveUnits' => array(),
        'class' => array(),
    ),
    'feGaussianBlur' => array(
        'in' => array(),
        'stdDeviation' => array(),
        'result' => array(),
    ),
    'feOffset' => array(
        'in' => array(),
        'dx' => array(),
        'dy' => array(),
        'result' => array(),
    ),
    'feDropShadow' => array(
        'dx' => array(),
        'dy' => array(),
        'stdDeviation' => array(),
        'flood-color' => array(),
        'flood-opacity' => array(),
    ),
    'image' => array(
        'x' => array(),
        'y' => array(),
        'width' => array(),
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
        global $onepaquc_allowed_tags;
        echo '<' . esc_html($headtag) . ' class="' . esc_html($class) . '">';

        // Check if icon is not empty
        if (!empty($icon)) {
            echo '<span class="plugincy_sec_icon">' . wp_kses($icon, $onepaquc_allowed_tags) . '</span>';
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

    public function switcher($name, $default = 1)
    { ?>
        <label class="switch">
            <input type="checkbox" name="<?php echo esc_attr($name); ?>" value="1" <?php checked(1, get_option($name, $default), true); ?> />
            <span class="slider round"></span>
        </label>
<?php
    }
}
