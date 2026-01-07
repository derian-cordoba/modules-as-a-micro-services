<?php

namespace Modules\History\Tests\Unit\Http\Requests\V1;

use Modules\History\Bus\Command\V1\Create\CreateHistoryCommand;
use Modules\History\Http\Requests\V1\CreateHistoryRequest;
use Tests\TestCase;

final class CreateHistoryRequestTest extends TestCase
{
    public function test_it_has_correct_validation_rules(): void
    {
        // Given
        $request = new CreateHistoryRequest();

        // When
        $rules = $request->rules();

        // Then
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('type', $rules);
        $this->assertArrayHasKey('user_id', $rules);
        $this->assertArrayHasKey('metadata', $rules);

        $this->assertContains('required', $rules['name']);
        $this->assertContains('string', $rules['name']);
        $this->assertContains('max:255', $rules['name']);

        $this->assertContains('required', $rules['type']);
        $this->assertContains('required', $rules['user_id']);
        $this->assertContains('integer', $rules['user_id']);

        $this->assertContains('sometimes', $rules['is_scanned']);
        $this->assertContains('boolean', $rules['is_scanned']);

        $this->assertContains('nullable', $rules['metadata']);
        $this->assertContains('array', $rules['metadata']);
    }

    public function test_it_converts_to_command(): void
    {
        // Given
        $request = CreateHistoryRequest::create('/api/v1/histories', 'POST', [
            'name' => 'Test History',
            'type' => 'test',
            'user_id' => 1,
            'is_scanned' => true,
            'metadata' => ['key' => 'value'],
        ]);

        // When
        $command = $request->asCommand();

        // Then
        $this->assertInstanceOf(CreateHistoryCommand::class, $command);
        $this->assertEquals('Test History', $command->name);
        $this->assertEquals('test', $command->type);
        $this->assertEquals(1, $command->userId);
        $this->assertTrue($command->isScanned);
        $this->assertEquals(['key' => 'value'], $command->metadata);
    }

    public function test_it_converts_to_command_without_metadata(): void
    {
        // Given
        $request = CreateHistoryRequest::create('/api/v1/histories', 'POST', [
            'name' => 'Test History',
            'type' => 'test',
            'user_id' => 1,
        ]);

        // When
        $command = $request->asCommand();

        // Then
        $this->assertNull($command->metadata);
    }
}
