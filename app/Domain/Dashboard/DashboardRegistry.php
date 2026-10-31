<?php

namespace App\Domain\Dashboard;

use App\Domain\Dashboard\Contracts\Widget;
use Illuminate\Support\Collection;

class DashboardRegistry
{
    /** @var array<string, class-string<Widget>> */
    protected array $widgets = [];

    public function register(string $key, string $class): void
    {
        $this->widgets[$key] = $class;
    }

    public function get(string $key): ?Widget
    {
        if (! isset($this->widgets[$key])) {
            return null;
        }

        return app($this->widgets[$key]);
    }

    /** @return Collection<int, Widget> */
    public function all(): Collection
    {
        return collect($this->widgets)->map(fn (string $class) => app($class));
    }

    /** @return array<int, string> */
    public function keys(): array
    {
        return array_keys($this->widgets);
    }
}
