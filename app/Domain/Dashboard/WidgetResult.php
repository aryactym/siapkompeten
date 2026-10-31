<?php

namespace App\Domain\Dashboard;

class WidgetResult
{
    /**
     * @param array<int, string> $categories
     * @param array<int, array{name: string, data: array<int, int|float>, color?: string}> $series
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public string $key,
        public string $type,
        public string $title,
        public string $unit = '',
        public array $categories = [],
        public array $series = [],
        public array $meta = [],
        public bool $empty = false,
        public ?array $list = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type,
            'title' => $this->title,
            'unit' => $this->unit,
            'categories' => $this->categories,
            'series' => $this->series,
            'meta' => array_merge($this->meta, [
                'generated_at' => now()->toIso8601String(),
            ]),
            'empty' => $this->empty,
            'list' => $this->list,
        ];
    }
}
