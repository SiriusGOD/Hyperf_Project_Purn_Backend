<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
#[Constants]
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Server Error 500!")
     */
    public const SERVER_ERROR = 500;

    /**
     * @Message("Server Error 503!")
     */
    public const TOKEN_INVALID = 503;

    /**
     * @Message("oops 404!")
     */
    public const COMMON_ERROR = 404;

    /**
     * @Message("Unauthorized 401!")
     */
    public const UNAUTHORIZED = 401;

    /**
     * @Message("Forbidden 403!")
     */
    public const FORBIDDEN = 403;

    /**
     * @Message("Bad_request 400!")
     */
    public const BAD_REQUEST = 400;

    /**
     * @Message("Unprocessable_entity 405!")
     */
    public const UNPROCESSABLE_ENTITY = 405;

}
