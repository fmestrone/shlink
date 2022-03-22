<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Core\Exception;

use Fig\Http\Message\StatusCodeInterface;
use Mezzio\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;
use Shlinkio\Shlink\Core\Model\ShortUrlIdentifier;

use function sprintf;

class MissingShortUrlPasswordException extends DomainException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    private const TITLE = 'Missing Short URL password';
    private const TYPE = 'MISSING_SHORTCODE_PASSWORD';

    public static function fromMissingPassword(ShortUrlIdentifier $identifier): self
    {
        $shortCode = $identifier->shortCode();
        $domain = $identifier->domain();
        $suffix = $domain === null ? '' : sprintf(' for domain "%s"', $domain);
        $e = new self(sprintf('Missing password for short code "%s"%s', $shortCode, $suffix));

        $e->detail = $e->getMessage();
        $e->title = self::TITLE;
        $e->type = self::TYPE;
        $e->status = StatusCodeInterface::STATUS_FORBIDDEN;
        $e->additional = ['shortCode' => $shortCode];

        if ($domain !== null) {
            $e->additional['domain'] = $domain;
        }

        return $e;
    }
}
