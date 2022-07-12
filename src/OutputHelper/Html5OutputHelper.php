<?php

declare(strict_types=1);

namespace DanBettles\Marigold\OutputHelper;

use BadMethodCallException;
use DomainException;
use ReflectionMethod;

use function array_unshift;
use function in_array;
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

    public function __construct(?string $encoding = null)
    {
        parent::__construct($encoding);

        $this->setCreateEl(new ReflectionMethod(__CLASS__, 'createEl'));
    }

    /**
     * @throws DomainException If content was passed when creating a void element.
     */
    protected function createElement(
        string $tagName,
        array $attributes,
        ?string $content
    ): string {
        // See https://html.spec.whatwg.org/multipage/syntax.html#void-elements
        if (in_array($tagName, self::TAG_NAMES_BY_TYPE['void'])) {
            if (null !== $content) {
                throw new DomainException('Content was passed: a void element may not have content.');
            }

            $attributesHtml = $this->createAttributesHtml($attributes);

            if ($attributesHtml) {
                $attributesHtml = " {$attributesHtml}";
            }

            return "<{$tagName}{$attributesHtml}>";
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

    private function setCreateEl(ReflectionMethod $reflectionMethod): self
    {
        $this->createEl = $reflectionMethod;
        return $this;
    }

    private function getCreateEl(): ReflectionMethod
    {
        return $this->createEl;
    }
}
