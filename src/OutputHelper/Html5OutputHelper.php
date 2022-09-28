<?php

declare(strict_types=1);

namespace DanBettles\Marigold\OutputHelper;

use BadMethodCallException;
use InvalidArgumentException;
use ReflectionMethod;

use function array_unshift;
use function in_array;
use function is_bool;
use function preg_match;
use function strtolower;

use const null;

/**
 * @method string createEl(string $tagName, array<string, string|bool>|string $attributesOrContent, string|null $contentOrNothing = null)
 */
class Html5OutputHelper extends XmlOutputHelper
{
    /**
     * (Element) tag-names by (element) type.
     *
     * @var array<string, array<int, string>>
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

    /**
     * @param array<string, string|bool> $attributes
     */
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
     * @throws InvalidArgumentException If content was passed when creating a void element.
     */
    protected function createElement(
        string $tagName,
        array $attributes,
        ?string $content
    ): string {
        // See https://html.spec.whatwg.org/multipage/syntax.html#void-elements
        if (in_array($tagName, self::TAG_NAMES_BY_TYPE['void'])) {
            if (null !== $content) {
                throw new InvalidArgumentException('Content was passed: a void element may not have content.');
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
     * @param mixed[] $arguments
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

        /** @var string */
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
