<?php

namespace Tests\Assets;

class InvalidValueProvider
{
    public static function emptyStringProvider(): array
    {
        return [
            'empty' => [''],
            'null' => [null],
        ];
    }

    public static function invalidNameProvider(): array
    {
        return [
            'non-string' => [ ['this-is-an-array-element'] ],
            'too small' => ['abc'],
            'too long' => [str_repeat('a', 1000)],
        ];
    }

    public static function invalidDescriptionProvider(): array
    {
        return [
            'non-string' => [ ['string'] ],
            'too long' => [str_repeat('a', 10000)],
        ];
    }

    public static function invalidDatetimeProvider(): array
    {
        return [
            'non-string' => [ ['string'] ],
            'invalid date 1' => ['2020-01-01 90:90:90'],
            'invalid date 2' => ['some random string'],
        ];
    }
}
