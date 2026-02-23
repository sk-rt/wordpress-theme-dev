<?php

namespace PostContentSync\Parser;

/**
 * Content Extractor Class
 *
 * Extracts content from HTML body.
 */
class ContentExtractor
{
    /**
     * Plugin configuration
     *
     * @var array
     */
    private $config;

    /**
     * Constructor
     *
     * @param array $config Plugin configuration
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Extract content from DOMDocument
     *
     * @param \DOMDocument $dom DOM document
     * @return string Extracted HTML content
     */
    public function extract($dom)
    {
        $contentElementId = $this->config['content_element_id'] ?? 'psc:content';

        // Try to find the content element by ID
        $contentElement = $this->findElementById($dom, $contentElementId);

        if ($contentElement === null) {
            // Fallback: extract from body tag
            return $this->extractFromBody($dom);
        }

        // Get inner HTML of the content element
        return $this->getInnerHtml($contentElement);
    }

    /**
     * Find element by ID
     *
     * @param \DOMDocument $dom DOM document
     * @param string $id Element ID
     * @return \DOMElement|null Element or null if not found
     */
    private function findElementById($dom, $id)
    {
        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query("//*[@id='" . $id . "']");

        if ($elements->length > 0) {
            return $elements->item(0);
        }

        return null;
    }

    /**
     * Extract content from body tag
     *
     * @param \DOMDocument $dom DOM document
     * @return string Body content HTML
     */
    private function extractFromBody($dom)
    {
        $bodyElements = $dom->getElementsByTagName('body');

        if ($bodyElements->length === 0) {
            return '';
        }

        $body = $bodyElements->item(0);
        return $this->getInnerHtml($body);
    }

    /**
     * Get inner HTML of an element
     *
     * @param \DOMElement $element DOM element
     * @return string Inner HTML
     */
    private function getInnerHtml($element)
    {
        $innerHTML = '';
        $children = $element->childNodes;

        foreach ($children as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return trim($innerHTML);
    }

    /**
     * Sanitize HTML content
     *
     * @param string $html HTML content
     * @return string Sanitized HTML
     */
    public function sanitize($html)
    {
        $sanitize = $this->config['sanitize_content'] ?? true;

        if (!$sanitize) {
            return $html;
        }

        // Use WordPress wp_kses_post if available
        if (function_exists('wp_kses_post')) {
            return wp_kses_post($html);
        }

        // Fallback: basic HTML sanitization
        $allowedTags = $this->config['allowed_html_tags'] ?? [
            'a', 'abbr', 'address', 'area', 'article', 'aside', 'audio',
            'b', 'bdi', 'bdo', 'blockquote', 'br', 'button',
            'caption', 'cite', 'code', 'col', 'colgroup',
            'data', 'datalist', 'dd', 'del', 'details', 'dfn', 'div', 'dl', 'dt',
            'em', 'embed',
            'fieldset', 'figcaption', 'figure', 'footer', 'form',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hr',
            'i', 'iframe', 'img', 'input', 'ins',
            'kbd',
            'label', 'legend', 'li',
            'main', 'map', 'mark', 'meter',
            'nav',
            'ol', 'optgroup', 'option', 'output',
            'p', 'picture', 'pre', 'progress',
            'q',
            'rp', 'rt', 'ruby',
            's', 'samp', 'section', 'select', 'small', 'source', 'span', 'strong', 'sub', 'summary', 'sup',
            'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'time', 'tr', 'track',
            'u', 'ul',
            'var', 'video',
            'wbr'
        ];

        return strip_tags($html, '<' . implode('><', $allowedTags) . '>');
    }
}
