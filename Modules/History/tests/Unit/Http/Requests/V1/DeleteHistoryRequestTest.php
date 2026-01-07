<?php

namespace Modules\History\Tests\Unit\Http\Requests\V1;

use Modules\History\Bus\Command\V1\Delete\DeleteHistoryCommand;
use Modules\History\Http\Requests\V1\DeleteHistoryRequest;
use Tests\TestCase;

final class DeleteHistoryRequestTest extends TestCase
{
    public function test_it_has_correct_validation_rules(): void
    {
        // Given
        $request = new DeleteHistoryRequest();

        // When
        $rules = $request->rules();

        // Then
        $this->assertArrayHasKey('slugs', $rules);
        $this->assertArrayHasKey('slugs.*', $rules);
        $this->assertArrayHasKey('user_id', $rules);

        $this->assertContains('required', $rules['slugs']);
        $this->assertContains('array', $rules['slugs']);

        $this->assertContains('string', $rules['slugs.*']);

        $this->assertContains('required', $rules['user_id']);
        $this->assertContains('integer', $rules['user_id']);
    }

    public function test_it_converts_to_command_with_single_slug(): void
    {
        // Given
        $request = DeleteHistoryRequest::create('/api/v1/histories', 'DELETE', [
            'slugs' => ['test-slug'],
            'user_id' => 1,
        ]);

        // When
        $command = $request->asCommand();

        // Then
        $this->assertInstanceOf(DeleteHistoryCommand::class, $command);
        $this->assertEquals(['test-slug'], $command->slugs);
        $this->assertEquals(1, $command->userId);
    }

    public function test_it_converts_to_command_with_multiple_slugs(): void
    {
        // Given
        $request = DeleteHistoryRequest::create('/api/v1/histories', 'DELETE', [
            'slugs' => ['slug-1', 'slug-2', 'slug-3'],
            'user_id' => 5,
        ]);

        // When
        $command = $request->asCommand();

        // Then
        $this->assertInstanceOf(DeleteHistoryCommand::class, $command);
        $this->assertEquals(['slug-1', 'slug-2', 'slug-3'], $command->slugs);
        $this->assertEquals(5, $command->userId);
    }

    public function test_it_converts_empty_slugs_array(): void
    {
        // Given
        $request = DeleteHistoryRequest::create('/api/v1/histories', 'DELETE', [
            'slugs' => [],
            'user_id' => 1,
        ]);

        // When
        $command = $request->asCommand();

        // Then
        $this->assertEmpty($command->slugs);
    }
}
