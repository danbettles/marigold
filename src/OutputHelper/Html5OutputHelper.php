<?php

declare(strict_types=1);

namespace DanBettles\Marigold\OutputHelper;

use BadMethodCallException;
use InvalidArgumentException;

use function array_unshift;
use function in_array;
use function is_bool;
use function preg_match;
use function strtolower;

use const null;
use const true;

/**
 * @method string createAttributes(array<string,string|int|float|bool> $attributes)
 * @method string createEl(string $tagName, array<string,string|int|float|bool>|string|int|float|null $attributesOrContent = [], string|int|float|null $contentOrNothing = null)
 * @todo Maybe don't extend `XmlOutputHelper`.
 */
class Html5OutputHelper extends XmlOutputHelper
{
    /**
     * (Element) tag-names by (element) type.
     *
     * @var array<string,array<int,string>>
     */
    private const TAG_NAMES_BY_TYPE = [
        // See https://html.spec.whatwg.org/multipage/syntax.html#void-elements
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

    /**
     * @throws InvalidArgumentException If the name is invalid
     * @param string|int|float|bool $value
     */
    protected function createAttribute(string $name, $value): string
    {
        if (is_bool($value)) {
            if (!$this->validateXmlName($name)) {
                throw new InvalidArgumentException("Attribute name `{$name}` is invalid");
            }

            // See https://meiert.com/en/blog/boolean-attributes-of-html/
            return true === $value
                ? $name
                : ''
            ;
        }

        return parent::createAttribute($name, $value);
    }

    /**
     * @throws InvalidArgumentException If content was passed when creating a void element
     */
    protected function createElement(
        string $tagName,
        array $attributes,
        $content
    ): string {
        if (in_array($tagName, self::TAG_NAMES_BY_TYPE['void'])) {
            // See https://html.spec.whatwg.org/multipage/syntax.html#elements-2:void-elements-4
            if (null !== $content) {
                throw new InvalidArgumentException('Content was passed: a void element may not have content');
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
     * @throws BadMethodCallException If the called method does not exist
     */
    public function __call(string $methodName, array $arguments): string
    {
        $matches = null;
        // See https://developer.mozilla.org/en-US/docs/Web/HTML/Element
        $methodNameIsValid = (bool) preg_match('~^create([A-Z][a-zA-Z0-9]*)$~', $methodName, $matches);

        if (!$methodNameIsValid) {
            throw new BadMethodCallException("The method, `{$methodName}`, does not exist");
        }

        $tagName = strtolower($matches[1]);
        array_unshift($arguments, $tagName);

        /** @phpstan-ignore-next-line */
        return $this->createEl(...$arguments);
    }
}
