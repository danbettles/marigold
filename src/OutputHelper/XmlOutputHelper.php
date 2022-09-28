<?php

declare(strict_types=1);

namespace DanBettles\Marigold\OutputHelper;

use InvalidArgumentException;

use function htmlspecialchars;
use function implode;
use function is_array;
use function is_string;

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

    // @todo Validate attribute name.
    protected function createAttribute(string $name, string $value): string
    {
        return $name . '="' . $this->escape($value) . '"';
    }

    /**
     * @param array<string, string> $attributes
     */
    public function createAttributes(array $attributes): string
    {
        if (!$attributes) {
            return '';
        }

        $pairs = [];

        foreach ($attributes as $name => $value) {
            $pairs[] = $this->createAttribute($name, $value);
        }

        return implode(' ', $pairs);
    }

    /**
     * @param array<string, string> $attributes
     */
    protected function createElement(
        string $tagName,
        array $attributes,
        ?string $content
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
     * @param array<string, string>|string|null $attributesOrContent
     * @param string|null $contentOrNothing
     * @throws InvalidArgumentException If the content is not a string/null.
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

        if (!is_string($content) && null !== $content) {
            throw new InvalidArgumentException('The content is not a string/null.');
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
