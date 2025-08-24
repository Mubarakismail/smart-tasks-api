<?php

namespace Modules\Task\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => [
                'id' => $this->status->id,
                'code' => $this->status->code,
                'name' => $this->status->name,
            ],
            'assignee' => $this->whenLoaded('assignee', fn() => [
                'id' => $this->assignee->id, 'name' => $this->assignee->name,
                'email' => $this->assignee->email
            ]),
            'creator' => $this->whenLoaded('creator', fn() => [
                'id' => $this->creator->id, 'name' => $this->creator->name,
                'email' => $this->creator->email
            ]),
            'priority' => $this->priority,
            'due_date' => optional($this->due_date)?->toAtomString(),
            'created_at' => $this->created_at->toAtomString(),
            'updated_at' => $this->updated_at->toAtomString(),
        ];
    }
}
