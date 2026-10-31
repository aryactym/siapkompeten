<?php

namespace App\Support;

use App\Models\Institution;
use LogicException;

final class TenantContext
{
    public const SESSION_ID = 'active_institution_id';

    public const SESSION_NAME = 'active_institution_name';

    public const SESSION_LOGO = 'active_institution_logo';

    public static function id(): ?string
    {
        try {
            $id = session(self::SESSION_ID);
        } catch (\Throwable) {
            return null;
        }

        return is_string($id) && $id !== '' ? $id : null;
    }

    public static function institution(): ?Institution
    {
        $id = self::id();

        if ($id === null) {
            return null;
        }

        return Institution::find($id);
    }

    public static function name(): ?string
    {
        try {
            $name = session(self::SESSION_NAME);
        } catch (\Throwable) {
            return null;
        }

        return is_string($name) && $name !== '' ? $name : null;
    }

    public static function requireId(): string
    {
        return self::id() ?? throw new LogicException(
            'Tenant context is missing. Set an active institution before writing tenant data.'
        );
    }

    public static function set(Institution $institution): void
    {
        session([
            self::SESSION_ID => $institution->id,
            self::SESSION_NAME => $institution->name,
            self::SESSION_LOGO => $institution->logo_path,
        ]);
    }

    public static function clear(): void
    {
        session()->forget([
            self::SESSION_ID,
            self::SESSION_NAME,
            self::SESSION_LOGO,
        ]);
    }
}
