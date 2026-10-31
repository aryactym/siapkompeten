<?php

namespace App\Domain\Dashboard\Contracts;

use App\Models\User;

interface Widget
{
    public function key(): string;

    public function type(): string;

    public function title(): string;

    /** @return array<int, string> */
    public function roles(): array;

    public function authorize(User $user): bool;

    public function ttl(): int;

    /**
     * @return \App\Domain\Dashboard\WidgetResult
     */
    public function data(\App\Domain\Dashboard\DashboardFilter $filter): \App\Domain\Dashboard\WidgetResult;
}
