<?php

namespace Modules\History\Tests\Feature\Http\Controllers\V1;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class CreateControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $route = 'api.v1.histories.create';

    public function test_it_can_create_history_via_api(): void
    {
        // Given
        $payload = [
            'name' => 'User Logged In',
            'type' => 'authentication',
            'user_id' => 1,
            'is_scanned' => true,
        ];

        // When
        $response = $this->postJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'name',
                        'type',
                        'user_id',
                        'metadata',
                        'created_at',
                    ],
                ],
                'meta' => [
                    'message',
                ],
            ])
            ->assertJson([
                'data' => [
                    'type' => 'histories',
                    'attributes' => [
                        'name' => 'User Logged In',
                        'type' => 'authentication',
                        'user_id' => 1,
                        'is_scanned' => true,
                    ],
                ],
                'meta' => [
                    'message' => 'Resource created successfully',
                ],
            ]);

        $this->assertDatabaseHas('histories', [
            'name' => 'User Logged In',
            'type' => 'authentication',
            'user_id' => 1,
            'is_scanned' => true,
        ]);
    }

    public function test_it_can_create_history_with_metadata(): void
    {
        // Given
        $metadata = [
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'action' => 'login',
        ];

        $payload = [
            'name' => 'User Login',
            'type' => 'authentication',
            'user_id' => 2,
            'metadata' => $metadata,
        ];

        // When
        $response = $this->postJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('data.attributes.metadata', $metadata);

        $this->assertDatabaseHas('histories', [
            'name' => 'User Login',
            'type' => 'authentication',
            'user_id' => 2,
        ]);

        $this->assertDatabaseCount('histories', 1);
    }

    #[DataProvider(methodName: 'validationErrorProvider')]
    public function test_it_validates_required_fields(
        array $payload,
        string $expectedErrorField
    ): void {
        // When
        $response = $this->postJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrorFor($expectedErrorField);
    }

    public function test_it_validates_name_max_length(): void
    {
        // Given
        $payload = [
            'name' => str_repeat('a', 256), // 256 characters (max is 255)
            'type' => 'test',
            'user_id' => 1,
        ];

        // When
        $response = $this->postJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrorFor('name');
    }

    public function test_it_validates_type_max_length(): void
    {
        // Given
        $payload = [
            'name' => 'Test',
            'type' => str_repeat('a', 101), // 101 characters (max is 100)
            'user_id' => 1,
        ];

        // When
        $response = $this->postJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrorFor('type');
    }

    public function test_it_validates_user_id_is_integer(): void
    {
        // Given
        $payload = [
            'name' => 'Test',
            'type' => 'test',
            'user_id' => 'not-an-integer',
        ];

        // When
        $response = $this->postJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('user_id');
    }

    public function test_it_validates_metadata_is_array(): void
    {
        // Given
        $payload = [
            'name' => 'Test',
            'type' => 'test',
            'user_id' => 1,
            'metadata' => 'not-an-array',
        ];

        // When
        $response = $this->postJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrorFor('metadata');
    }

    public function test_it_returns_json_api_content_type(): void
    {
        // Given
        $payload = [
            'name' => 'Test',
            'type' => 'test',
            'user_id' => 1,
        ];

        // When
        $response = $this->postJson(route($this->route), $payload);

        // Then
        $response->assertHeader('Content-Type', 'application/vnd.api+json');
    }

    public function test_it_handles_special_characters_in_name(): void
    {
        // Given
        $payload = [
            'name' => 'Test "Special" Characters & <HTML lang="en">',
            'type' => 'test',
            'user_id' => 1,
        ];

        // When
        $response = $this->postJson(route($this->route), $payload);

        // Then
        $response->assertStatus(201)
            ->assertJsonPath('data.attributes.name', 'Test "Special" Characters & <HTML lang="en">');
    }

    public function test_it_handles_unicode_characters(): void
    {
        // Given
        $payload = [
            'name' => 'Usuario Accedió 用户登录 🎉',
            'type' => 'authentication',
            'user_id' => 1,
        ];

        // When
        $response = $this->postJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('data.attributes.name', 'Usuario Accedió 用户登录 🎉');
    }

    public function test_it_handles_nested_metadata(): void
    {
        // Given
        $metadata = [
            'level1' => [
                'level2' => [
                    'level3' => 'deep value',
                ],
            ],
            'array_data' => [1, 2, 3, 4, 5],
        ];

        $payload = [
            'name' => 'Complex Metadata',
            'type' => 'test',
            'user_id' => 1,
            'metadata' => $metadata,
        ];

        // When
        $response = $this->postJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('data.attributes.metadata', $metadata);
    }

    public static function validationErrorProvider(): array
    {
        return [
            'missing name' => [
                'payload' => [
                    'type' => 'test',
                    'user_id' => 1,
                ],
                'expectedErrorField' => 'name',
            ],
            'missing type' => [
                'payload' => [
                    'name' => 'Test',
                    'user_id' => 1,
                ],
                'expectedErrorField' => 'type',
            ],
            'missing user_id' => [
                'payload' => [
                    'name' => 'Test',
                    'type' => 'test',
                ],
                'expectedErrorField' => 'user_id',
            ],
        ];
    }
}
