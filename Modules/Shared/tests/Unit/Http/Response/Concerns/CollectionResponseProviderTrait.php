<?php

namespace Modules\Shared\Tests\Unit\Http\Response\Concerns;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait CollectionResponseProviderTrait
{
    public static function bookCollectionProvider(): array
    {
        return [
            'empty collection' => [
                'collection' => [],
            ],
            'single book' => [
                'collection' => [
                    [
                        'id' => '1',
                        'title' => 'Clean Code',
                        'author' => 'Robert C. Martin',
                        'isbn' => '978-0132350884',
                    ],
                ],
            ],
            'multiple books' => [
                'collection' => [
                    [
                        'id' => '1',
                        'title' => 'Clean Code',
                        'author' => 'Robert C. Martin',
                        'isbn' => '978-0132350884',
                    ],
                    [
                        'id' => '2',
                        'title' => 'Domain-Driven Design',
                        'author' => 'Eric Evans',
                        'isbn' => '978-0321125217',
                    ],
                    [
                        'id' => '3',
                        'title' => 'Refactoring',
                        'author' => 'Martin Fowler',
                        'isbn' => '978-0201485677',
                    ],
                ],
            ],
        ];
    }

    public static function bookCollectionWithMetaProvider(): array
    {
        return [
            'collection with total meta' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Clean Code'],
                    ['id' => '2', 'title' => 'DDD'],
                ],
                'meta' => [
                    'total' => 2,
                ],
            ],
            'collection with multiple meta' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Clean Code'],
                ],
                'meta' => [
                    'total' => 1,
                    'category' => 'programming',
                    'language' => 'english',
                ],
            ],
        ];
    }

    public static function bookCollectionWithHeadersProvider(): array
    {
        return [
            'collection with cache header' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Clean Code'],
                ],
                'headers' => [
                    'X-Cache-Control' => 'max-age=3600',
                ],
            ],
            'collection with multiple headers' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Clean Code'],
                ],
                'headers' => [
                    'X-Request-ID' => 'abc123',
                    'X-Total-Count' => '1',
                ],
            ],
        ];
    }

    public static function toResponseProvider(): array
    {
        return [
            'empty collection' => [
                'collection' => [],
                'request' => new Request(),
            ],
            'single item collection' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Clean Code', 'author' => 'Robert C. Martin'],
                ],
                'request' => new Request(),
            ],
            'multiple items collection' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Clean Code', 'author' => 'Robert C. Martin'],
                    ['id' => '2', 'title' => 'DDD', 'author' => 'Eric Evans'],
                ],
                'request' => new Request(),
            ],
        ];
    }

    public static function paginatedCollectionProvider(): array
    {
        $items = collect([
            ['id' => '1', 'title' => 'Book 1'],
            ['id' => '2', 'title' => 'Book 2'],
            ['id' => '3', 'title' => 'Book 3'],
        ]);

        return [
            'paginated collection page 1' => [
                'collection' => new LengthAwarePaginator(
                    items: $items->take(limit: 2),
                    total: 3,
                    perPage: 2,
                    currentPage: 1
                ),
                'request' => new Request(['page' => 1, 'per_page' => 2]),
            ],
            'paginated collection page 2' => [
                'collection' => new LengthAwarePaginator(
                    items: $items->slice(offset: 2, length: 1),
                    total: 3,
                    perPage: 2,
                    currentPage: 2
                ),
                'request' => new Request(['page' => 2, 'per_page' => 2]),
            ],
        ];
    }

    public static function emptyCollectionProvider(): array
    {
        return [
            'empty array' => [
                'collection' => [],
                'request' => new Request(),
            ],
            'empty collection' => [
                'collection' => new Collection([]),
                'request' => new Request(),
            ],
        ];
    }

    public static function collectionWithMetaMergeProvider(): array
    {
        return [
            'merge single meta key' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Book 1'],
                ],
                'initialMeta' => [
                    'total' => 1,
                ],
                'request' => new Request(),
            ],
            'merge multiple meta keys' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Book 1'],
                    ['id' => '2', 'title' => 'Book 2'],
                ],
                'initialMeta' => [
                    'total' => 2,
                    'page' => 1,
                ],
                'request' => new Request(),
            ],
        ];
    }

    public static function customHeadersProvider(): array
    {
        return [
            'single custom header' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Book 1'],
                ],
                'headers' => [
                    'X-Custom-Header' => 'custom-value',
                ],
                'request' => new Request(),
            ],
            'multiple custom headers' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Book 1'],
                ],
                'headers' => [
                    'X-Request-ID' => 'req-123',
                    'X-Total-Count' => '1',
                    'X-Page-Number' => '1',
                ],
                'request' => new Request(),
            ],
        ];
    }

    public static function statusCodeProvider(): array
    {
        return [
            '200 OK' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Book 1'],
                ],
                'statusCode' => 200,
                'request' => new Request(),
            ],
            '201 Created' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Book 1'],
                ],
                'statusCode' => 201,
                'request' => new Request(),
            ],
            '202 Accepted' => [
                'collection' => [
                    ['id' => '1', 'title' => 'Book 1'],
                ],
                'statusCode' => 202,
                'request' => new Request(),
            ],
        ];
    }
}
