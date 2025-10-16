<?php

namespace App\Constants;

class Status
{

    const ENABLE = 1;
    const DISABLE = 0;

    const YES = 1;
    const NO = 0;

    const VERIFIED = 1;
    const UNVERIFIED = 0;
    const UNDER_REVIEW = 2;

    const PAYMENT_INITIATE = 0;
    const PAYMENT_SUCCESS = 1;
    const PAYMENT_PENDING = 2;
    const PAYMENT_REJECT = 3;

    const TICKET_OPEN = 0;
    const TICKET_ANSWER = 1;
    const TICKET_REPLY = 2;
    const TICKET_CLOSE = 3;

    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;

    const USER_ACTIVE = 1;
    const USER_BAN = 0;

    const USER_DELETE = 9;

    const AGENT_ACTIVE = 1;
    const AGENT_BAN = 0;

    const MERCHANT_ACTIVE = 1;
    const MERCHANT_BAN = 0;

    const KYC_UNVERIFIED = 0;
    const KYC_PENDING = 2;
    const KYC_VERIFIED = 1;

    const GOOGLE_PAY = 5001;

    const CUR_BOTH = 1;
    const CUR_TEXT = 2;
    const CUR_SYM = 3;

    const PENDING = 0;
    const APPROVED = 1;
    const REJECTED = 2;

    const DISCOUNT_FIXED = 1;
    const DISCOUNT_PERCENT = 2;

    const PLATFORM_WEB = 1;
    const PLATFORM_APP = 2;

    const VIRTUAL_CARD_REUSEABLE = 1;
    const VIRTUAL_CARD_ONETIME = 2;

    const VIRTUAL_CARD_HOLDER_EXISTING = 1;
    const VIRTUAL_CARD_HOLDER_NEW      = 2;

    const VIRTUAL_CARD_INACTIVE = 0;
    const VIRTUAL_CARD_ACTIVE   = 1;
    const VIRTUAL_CARD_CLOSED = 9;

    const SUPPER_ADMIN_ID     = 1;
    const SUPER_ADMIN_ROLE_ID = 1;

    const PAYMENT_CAPTURE_INITIATE = 0;
    const PAYMENT_CAPTURE_SUCCESS = 1;
    const PAYMENT_CAPTURE_FAILED = 8;
    const PAYMENT_CAPTURE_CANCEL = 9;
}
