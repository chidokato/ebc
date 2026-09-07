<?php

namespace App\Support;

class NewsHtml
{
    public static function clean(string $html): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
            $body = $document->getElementsByTagName('body')->item(0);
            return $body ? self::children($body) : '';
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private static function children(\DOMNode $parent): string
    {
        $html = '';
        foreach ($parent->childNodes as $node) {
            if ($node instanceof \DOMText) {
                $html .= htmlspecialchars($node->nodeValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                continue;
            }
            if (! $node instanceof \DOMElement) {
                continue;
            }
            $tag = strtolower($node->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template'], true)) {
                continue;
            }
            $content = self::children($node);
            if (! in_array($tag, ['p', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'ul', 'ol', 'li', 'blockquote', 'a'], true)) {
                $html .= $content;
                continue;
            }
            $attributes = '';
            if ($tag === 'a') {
                $href = trim($node->getAttribute('href'));
                if (preg_match('~^(?:https?://|mailto:|tel:|/(?!/)|#)~i', $href) && ! preg_match('/[\x00-\x20\x7f\\\\]/', $href)) {
                    $attributes = ' href="'.htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'" rel="noopener noreferrer"';
                }
            }
            $html .= $tag === 'br' ? '<br>' : '<'.$tag.$attributes.'>'.$content.'</'.$tag.'>';
        }
        return $html;
    }

    public static function plain(string $text): string
    {
        return '<p>'.nl2br(htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), false).'</p>';
    }
}
