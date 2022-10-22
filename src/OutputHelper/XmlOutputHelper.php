<?php

declare(strict_types=1);

namespace DanBettles\Marigold\OutputHelper;

use InvalidArgumentException;

use function htmlspecialchars;
use function implode;
use function is_array;
use function is_float;
use function is_int;
use function is_string;
use function preg_match;
use function strtolower;
use function substr;

use const false;
use const null;

/**
 * By default, uses the value of the `default_charset` configuration option.
 *
 * Will automatically escape attribute values but won't touch anything else.
 */
class XmlOutputHelper
{
    private ?string $encoding = null;

    /**
     * Will not encode existing entities.
     */
    public function escape(string $string): string
    {
        // PHPStan is wrong about this.  According to the PHP manual, `string|null` *is* acceptable.
        /** @phpstan-var string */
        $encoding = $this->getEncoding();

        return htmlspecialchars(
            $string,
            ENT_QUOTES | ENT_SUBSTITUTE | ENT_XML1,
            $encoding,
            false
        );
    }

    /**
     * See https://docstore.mik.ua/orelly/xml/xmlnut/ch02_04.htm
     */
    protected function validateXmlName(string $name): bool
    {
        if ('' === $name) {
            return false;
        }

        if ('xml' === strtolower(substr($name, 0, 3))) {
            return false;
        }

        return (bool) preg_match('~^[a-zA-Z_][a-zA-Z0-9_\-.:]*$~', $name);
    }

    /**
     * @param mixed $value
     */
    private function validateValue($value): bool
    {
        // There is only one way that values of these types can be represented as a string.  Boolean variables are not
        // permitted because there are numerous ways to represent their value.
        return is_string($value)
            || is_int($value)
            || is_float($value)
        ;
    }

    /**
     * @param string|int|float $value
     * @throws InvalidArgumentException If the name is invalid.
     * @throws InvalidArgumentException If the type of the value is invalid.
     */
    protected function createAttribute(string $name, $value): string
    {
        if (!$this->validateXmlName($name)) {
            throw new InvalidArgumentException("Attribute name `{$name}` is invalid.");
        }

        if (!$this->validateValue($value)) {
            throw new InvalidArgumentException("The type of attribute `{$name}` is invalid.");
        }

        return $name . '="' . $this->escape((string) $value) . '"';
    }

    /**
     * @param array<string, string|int|float> $attributes
     */
    public function createAttributes(array $attributes): string
    {
        if (!$attributes) {
            return '';
        }

        $pairs = [];

        foreach ($attributes as $name => $value) {
            $pair = $this->createAttribute($name, $value);

            if ('' !== $pair) {
                $pairs[] = $pair;
            }
        }

        return $pairs
            ? implode(' ', $pairs)
            : ''
        ;
    }

    /**
     * @param array<string, string> $attributes
     * @param string|int|float|null $content
     */
    protected function createElement(
        string $tagName,
        array $attributes,
        $content
    ): string {
        $attributesStr = $this->createAttributes($attributes);

        if ($attributesStr) {
            $attributesStr = " {$attributesStr}";
        }

        return null === $content
            ? "<{$tagName}{$attributesStr}/>"
            : "<{$tagName}{$attributesStr}>{$content}</{$tagName}>"
        ;
    }

    /**
     * @param string $tagName
     * @param array<string, string>|string|int|float|null $attributesOrContent
     * @param string|int|float|null $contentOrNothing
     * @throws InvalidArgumentException If the type of the content is invalid.
     */
    public function createEl(
        string $tagName,
        $attributesOrContent = [],
        $contentOrNothing = null
    ): string {
        $attributes = $attributesOrContent;
        $content = $contentOrNothing;

        if (!is_array($attributesOrContent)) {
            $attributes = [];
            /** @var mixed */
            $content = $attributesOrContent;
        }

        $contentIsValid = $this->validateValue($content) || null === $content;

        if (!$contentIsValid) {
            throw new InvalidArgumentException('The type of the content is invalid.');
        }

        /** @var array $attributes */
        /** @var string|null $content */
        return $this->createElement($tagName, $attributes, $content);
    }

    public function setEncoding(?string $encoding): self
    {
        $this->encoding = $encoding;
        return $this;
    }

    public function getEncoding(): ?string
    {
        return $this->encoding;
    }
}
