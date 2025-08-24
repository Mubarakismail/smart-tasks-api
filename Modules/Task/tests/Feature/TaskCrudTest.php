<?php


namespace Modules\Task\Tests\Feature;


use Modules\Auth\Models\User;
use Modules\Core\Tests\ModuleTestCase;
use Modules\Task\Models\Status;


class TaskCrudTest extends ModuleTestCase
{
    public function test_creates_updates_and_deletes_task(): void
    {
        $user = User::factory()->create();
        $status = Status::factory()->create(['code' => 'TODO']);


        $token = $user->createToken('tests')->plainTextToken;


        $task = $this->withToken($token)
            ->postJson('/api/v1/tasks', [
                'title' => 'Build API',
                'status_id' => $status->id,
            ])->assertCreated()->json('data');


        $this->withToken($token)
            ->putJson('/api/v1/tasks/' . $task['id'], ['title' => 'Build API v2'])
            ->assertOk()->assertJsonPath('data.title', 'Build API v2');

        $this->withToken($token)
            ->deleteJson('/api/v1/tasks/' . $task['id'])
            ->assertNoContent();


        $this->assertSoftDeleted('tasks', ['id' => $task['id']]);
    }
}
