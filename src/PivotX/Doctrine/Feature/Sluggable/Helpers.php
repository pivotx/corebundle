<?php

namespace PivotX\Doctrine\Feature\Sluggable;


class Helpers
{
    public static function normalizeSlug($name)
    {
        $name = mb_strtolower(trim($name));

        // standard transliterate
        $name = iconv('UTF-8', 'ASCII', $name);

        $name = preg_replace('/[^a-z0-9_-]+/', '-', $name);

        $name = preg_replace('/^-+/', '', $name);
        $name = preg_replace('/-+$/', '', $name);

        return $name;
    }
}
