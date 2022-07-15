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
class XmlOutputHelper implements OutputHelperInterface
{
    private ?string $encoding;

    public function __construct(?string $encoding = null)
    {
        $this->setEncoding($encoding);
    }

    /**
     * Will not encode existing entities.
     */
    public function escape(string $string): string
    {
        return htmlspecialchars(
            $string,
            ENT_QUOTES | ENT_SUBSTITUTE | ENT_XML1,
            $this->getEncoding(),
            false
        );
    }

    /**
     * @throws InvalidArgumentException If the value of an attribute is not a string.
     */
    public function createAttributesHtml(array $attributes): string
    {
        if (!$attributes) {
            return '';
        }

        $pairs = [];

        foreach ($attributes as $name => $value) {
            if (!is_string($value)) {
                throw new InvalidArgumentException("The value of attribute `{$name}` is not a string.");
            }

            $pairs[] = $name . '="' . $this->escape((string) $value) . '"';
        }

        return implode(' ', $pairs);
    }

    protected function createElement(
        string $tagName,
        array $attributes,
        ?string $content
    ): string {
        $attributesHtml = $this->createAttributesHtml($attributes);

        if ($attributesHtml) {
            $attributesHtml = " {$attributesHtml}";
        }

        return null === $content
            ? "<{$tagName}{$attributesHtml}/>"
            : "<{$tagName}{$attributesHtml}>{$content}</{$tagName}>"
        ;
    }

    /**
     * @param string $tagName
     * @param array|string|null $attributesOrContent
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
            $content = $attributesOrContent;
        }

        if (!is_string($content) && null !== $content) {
            throw new InvalidArgumentException('The content is not a string/null.');
        }

        return $this->createElement($tagName, $attributes, $content);
    }

    private function setEncoding(?string $encoding): self
    {
        $this->encoding = $encoding;
        return $this;
    }

    protected function getEncoding(): ?string
    {
        return $this->encoding;
    }
}
