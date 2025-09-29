<?php

namespace App\Utils\Constants;

enum StoragePath:string
{
    case BANNER_PATH = "banner_images";
    case CATEGORY_PATH = "category_images";
    case STORE_PATH = "stores";
    case ARTICLE_PATH = "articles";
    case REVIEW_PATH = "review_images";
    case CONFIG_PATH = "config_images";
    case EVENT_PATH = "event_images";
    case AVATAR_USER_PATH = "avatar_user";

    public static function makePath(StoragePath $type, string $filename): string
    {
        return $type->value . DIRECTORY_SEPARATOR . $filename;
    }

    public static function makePathById(StoragePath $type, string $id): string
    {
        return $type->value . DIRECTORY_SEPARATOR . $id;
    }

}
