<?php

namespace App\Utils\Constants;

enum ConfigMembership: string
{
    case ALLOW_COMMENT     = 'allow_comment';
    case ALLOW_CHOOSE_SEAT = 'allow_choose_seat';
    case ALLOW_DOCUMENTARY = 'allow_documentary';

    case LIMIT_EVENT       = 'limit_event';
    case LIMIT_MEMBER      = 'limit_member';
    case FEATURE_POLL      = 'feature_poll';
    case FEATURE_GAME      = 'feature_game';
    case FEATURE_COMMENT   = 'feature_comment';


    public function labelAdmin(): string
    {
        return match ($this) {
            self::ALLOW_COMMENT     => __('constants.config_membership.allow_comment'),
            self::ALLOW_CHOOSE_SEAT => __('constants.config_membership.allow_choose_seat'),
            self::ALLOW_DOCUMENTARY => __('constants.config_membership.allow_documentary'),
        };
    }

    public function typeAdmin(): string
    {
        return match ($this) {
            self::ALLOW_COMMENT     => 'boolean',
            self::ALLOW_CHOOSE_SEAT => 'boolean',
            self::ALLOW_DOCUMENTARY => 'boolean',
        };
    }


    public function labelSuperAdmin(): string
    {
        return match ($this) {
            self::LIMIT_EVENT     => __('constants.config_membership.limit_event'),
            self::LIMIT_MEMBER    => __('constants.config_membership.limit_member'),
            self::FEATURE_POLL    => __('constants.config_membership.feature_poll'),
            self::FEATURE_GAME    => __('constants.config_membership.feature_game'),
            self::FEATURE_COMMENT => __('constants.config_membership.feature_comment'),
        };
    }

    public function typeSuperAdmin(): string
    {
        return match ($this) {
            self::LIMIT_EVENT     => 'number',
            self::LIMIT_MEMBER    => 'number',
            self::FEATURE_POLL    => 'boolean',
            self::FEATURE_GAME    => 'boolean',
            self::FEATURE_COMMENT => 'boolean',
        };
    }
}
