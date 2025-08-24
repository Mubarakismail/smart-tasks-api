<?php


namespace Modules\Task\Tests\Feature;


use Modules\Core\Tests\ModuleTestCase;
use Modules\Task\Models\Status;
use Modules\Task\Models\Task;


class TaskIndexTest extends ModuleTestCase
{
    public function test_it_lists_tasks_with_pagination_and_sorting(): void
    {
        Status::factory()->create(['code' => 'INPR', 'name' => 'In Progress', 'order' => 2]);
        Task::factory()->count(5)->create();


        $res = $this->getJson('/api/v1/tasks?per_page=2&orderBy=created_at&sortedBy=desc');
        $res->assertOk()->assertJsonStructure(['data', 'meta']);
        $this->assertEquals(2, $res->json('meta.per_page'));
    }


    public function test_it_filters_by_status_and_search(): void
    {
        $todo = Status::factory()->create(['code' => 'TODO']);
        Task::factory()->count(2)->create(['title' => 'Alpha build', 'status_id' => $todo->id]);
        Task::factory()->count(2)->create(['title' => 'Bravo task']);


        $res = $this->getJson('/api/v1/tasks?search=title:Alpha;status:1');
        $res->assertOk();
        $this->assertCount(2, $res->json('data'));
    }
}
