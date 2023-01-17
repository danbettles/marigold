<?php

declare(strict_types=1);

namespace DanBettles\Marigold;

use RuntimeException;

use function preg_replace;
use function str_replace;
use function trim;

use const null;

/**
 * This is a very lightweight, but effective, CSS minifier derived from https://github.com/danbettles/yacm.  It is
 * slightly different from other minifiers in that it preserves the approximate vertical structure of the original CSS.
 * So unless the formatting of the input is very unusual, the output should be just about readable.
 */
class CssMinifier
{
    /**
     * @throws RuntimeException If it failed to perform the replacement.
     */
    private function replace(
        string $pattern,
        string $replacement,
        string $subject
    ): string {
        $result = preg_replace($pattern, $replacement, $subject);

        if (null === $result) {
            throw new RuntimeException("Failed to replace `{$pattern}` with `{$replacement}`.");
        }

        return $result;
    }

    /**
     * Removes superfluous whitespace from the specified CSS; enough newlines are left, however, to preserve the basic
     * vertical structure of the CSS, to make it just about readable after minification.
     */
    public function removeSuperfluousWhitespaceFilter(string $css): string
    {
        // Normalize newlines.
        $css = str_replace(["\r\n", "\r", "\n"], "\n", $css);

        // Normalize horizontal whitespace.
        $css = str_replace("\t", ' ', $css);

        // Replace multiple, contiguous occurrences of the same whitespace character with just one.
        // We don't simply replace all whitespace characters with spaces - for example - because we want to retain the
        // vertical structure of the CSS, so that it's just about readable after minification.
        $css = $this->replace('/([\n ])\1+/', '$1', $css);

        // Remove horizontal whitespace from around delimiters.
        $css = $this->replace('/[ ]*([,;\{\}])[ ]*/', '$1', $css);

        // Remove horizontal space from after colons.  We can't *easily* remove whitespace from before colons without
        // risking breaking selectors containing a pseudo-class.
        $css = str_replace(': ', ':', $css);

        // Remove leading and trailing whitespace from lines.
        $css = $this->replace('/^[ ]*(.*?)[ ]*$/m', '$1', $css);

        // Remove empty lines.
        $css = trim($this->replace('/(?<=\n)[ ]*\n|/', '', $css));

        return $css;
    }

    /**
     * Removes comments from the specified CSS.
     */
    public function removeCommentsFilter(string $css): string
    {
        return $this->replace('~\/\*(.*?)\*\/~s', '', $css);
    }

    /**
     * Removes units from zero values.
     *
     * A zero value with units, no matter what unit of measurement is used, always equates to zero ("0"), so the units
     * are a waste of space.
     */
    public function removeUnitsFromZeroesFilter(string $css): string
    {
        // See http://www.w3.org/TR/CSS21/grammar.html#scanner and http://www.w3schools.com/cssref/css_units.asp
        return $this->replace('/\b0((?:em|ex|ch|rem|vw|vh|vmin|vm|vmax|cm|mm|in|px|pt|pc)\b|%)/i', '0', $css);
    }

    /**
     * If possible, replaces hex colours with their condensed equivalents.
     */
    public function condenseHexColoursFilter(string $css): string
    {
        $hexByte = '[\da-fA-F]';

        return $this->replace("/#({$hexByte})\\1({$hexByte})\\2({$hexByte})\\3/", '#$1$2$3', $css);
    }

    /**
     * Minifies the specified CSS.
     */
    public function minify(string $css): string
    {
        $css = $this->removeCommentsFilter($css);
        $css = $this->removeUnitsFromZeroesFilter($css);
        $css = $this->condenseHexColoursFilter($css);
        $css = $this->removeSuperfluousWhitespaceFilter($css);

        return $css;
    }
}
