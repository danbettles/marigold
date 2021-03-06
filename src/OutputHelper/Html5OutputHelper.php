<?php

declare(strict_types=1);

namespace DanBettles\Marigold\OutputHelper;

use BadMethodCallException;
use RangeException;
use ReflectionMethod;

use function array_unshift;
use function in_array;
use function is_bool;
use function preg_match;
use function strtolower;

use const null;

class Html5OutputHelper extends XmlOutputHelper
{
    /**
     * (Element) tag-names by (element) type.
     *
     * @var array
     */
    private const TAG_NAMES_BY_TYPE = [
        'void' => [
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'source',
            'track',
            'wbr',
        ],
    ];

    private ReflectionMethod $createEl;

    public function createAttributes(array $attributes): string
    {
        if (!$attributes) {
            return '';
        }

        $pairs = [];

        foreach ($attributes as $name => $value) {
            $pair = null;

            if (is_bool($value)) {
                // See https://meiert.com/en/blog/boolean-attributes-of-html/
                // @todo Validate attribute name.
                if ($value) {
                    $pair = $name;
                }
            } else {
                $pair = $this->createAttribute($name, $value);
            }

            if (null !== $pair) {
                $pairs[] = $pair;
            }
        }

        return $pairs
            ? implode(' ', $pairs)
            : ''
        ;
    }

    /**
     * @throws RangeException If content was passed when creating a void element.
     */
    protected function createElement(
        string $tagName,
        array $attributes,
        ?string $content
    ): string {
        // See https://html.spec.whatwg.org/multipage/syntax.html#void-elements
        if (in_array($tagName, self::TAG_NAMES_BY_TYPE['void'])) {
            if (null !== $content) {
                throw new RangeException('Content was passed: a void element may not have content.');
            }

            $attributesStr = $this->createAttributes($attributes);

            if ($attributesStr) {
                $attributesStr = " {$attributesStr}";
            }

            return "<{$tagName}{$attributesStr}>";
        }

        return parent::createElement($tagName, $attributes, $content);
    }

    /**
     * @throws BadMethodCallException If the called method does not exist.
     */
    public function __call(string $methodName, array $arguments): string
    {
        $matches = null;
        // See https://developer.mozilla.org/en-US/docs/Web/HTML/Element
        $methodNameIsValid = (bool) preg_match('~^create([A-Z][a-zA-Z0-9]*)$~', $methodName, $matches);

        if (!$methodNameIsValid) {
            throw new BadMethodCallException("The method, `{$methodName}`, does not exist.");
        }

        $tagName = strtolower($matches[1]);
        array_unshift($arguments, $tagName);

        return $this->getCreateEl()->invokeArgs($this, $arguments);
    }

    private function getCreateEl(): ReflectionMethod
    {
        if (!isset($this->createEl)) {
            $this->createEl = new ReflectionMethod(__CLASS__, 'createEl');
        }

        return $this->createEl;
    }
}
