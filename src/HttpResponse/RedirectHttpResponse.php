<?php

declare(strict_types=1);

namespace DanBettles\Marigold\HttpResponse;

use DanBettles\Marigold\HttpResponse;
use InvalidArgumentException;

use function in_array;
use function htmlspecialchars;

use const ENT_QUOTES;

/**
 * @link https://www.rfc-editor.org/rfc/rfc2616#section-10.3
 */
class RedirectHttpResponse extends HttpResponse
{
    private string $targetUrl;

    /**
     * @phpstan-param HeadersArray $headers
     */
    public function __construct(
        string $targetUrl,
        int $statusCode = self::HTTP_FOUND,
        array $headers = []
    ) {
        parent::__construct('', $statusCode, $headers);

        $this->setTargetUrl($targetUrl);
    }

    /**
     * @throws InvalidArgumentException If the status code does not identify a redirect
     */
    public function setStatusCode(int $code): self
    {
        if (!in_array($code, self::REDIRECT_STATUS_CODES)) {
            throw new InvalidArgumentException('The status code does not identify a redirect');
        }

        /** @var self */
        return parent::setStatusCode($code);
    }

    public function setTargetUrl(string $url): self
    {
        $this->targetUrl = $url;

        $escapedTargetUrl = htmlspecialchars($this->targetUrl, ENT_QUOTES, 'UTF-8');

        $this->setContent(<<<END
        <html>
        <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url='{$escapedTargetUrl}'" />
        <title>Redirecting to {$escapedTargetUrl}</title>
        </head>
        <body>Redirecting to <a href="{$escapedTargetUrl}">{$escapedTargetUrl}</a></body>
        </html>
        END);

        $augmentedHeaders = $this->getHeaders();
        $augmentedHeaders['Location'] = $this->targetUrl;
        $this->setHeaders($augmentedHeaders);

        return $this;
    }

    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }
}
