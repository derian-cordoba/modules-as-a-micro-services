<?php

namespace Modules\Shared\Tests\Unit\Http\Response\Concerns;

use Illuminate\Http\Request;

trait ModelResponseProviderTrait
{
    public static function bookResourceProvider(): array
    {
        return [
            'empty-resource' => [[]],
            'custom-resource-values' => [[
                'id' => 2,
                'name' => 'Book Two',
                'price' => 15.5,
                'quantity' => 1,
                'user_id' => 2,
                'user_link' => '/api/users/2',
                'self_link' => '/api/books/2',
            ]],
            'partial-resource-values' => [[
                'id' => 3,
                'name' => 'Book Three',
            ]],
            'null-resource' => [null],
        ];
    }

    public static function successResourceProvider(): array
    {
        return [
            'empty-resource' => [[]],
            'custom-resource-values' => [[
                'id' => 4,
                'name' => 'Book Four',
                'price' => 20.0,
                'quantity' => 3,
                'user_id' => 4,
                'user_link' => '/api/users/4',
                'self_link' => '/api/books/4',
            ]],
            'partial-resource-values' => [[
                'id' => 5,
                'name' => 'Book Five',
            ]],
            'null-resource' => [null],
        ];
    }

    public static function toResponseProvider(): array
    {
        return [
            'empty-resource' => [[], new Request()],
            'custom-resource-values-with-request' => [[
                'id' => 6,
                'name' => 'Book Six',
                'price' => 25.0,
                'quantity' => 2,
                'user_id' => 6,
                'user_link' => '/api/users/6',
                'self_link' => '/api/books/6',
            ], new Request()],
        ];
    }
}
