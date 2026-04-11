<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Canonical list of the four usage strategies the demo showcases.
 *
 * A "strategy" is a named bundle of wiring choices (middleware,
 * adapters, role source, policy resolver) that model one realistic
 * way of integrating tinyauth-backend into a CakePHP app.
 *
 * The slug is the URL prefix. The prefix (CamelCase) is the CakePHP
 * route prefix + PSR-4 subnamespace under `App\Controller\`.
 */
final class Strategy
{
    /**
     * @var string
     */
    public const ADAPTER_ONLY = 'adapter-only';

    /**
     * @var string
     */
    public const FULL_BACKEND = 'full-backend';

    /**
     * @var string
     */
    public const NATIVE_AUTH = 'native-auth';

    /**
     * @var string
     */
    public const EXTERNAL_ROLES = 'external-roles';

    /**
     * @return array<string, array<string, string>>
     */
    public static function all(): array
    {
        return [
            self::ADAPTER_ONLY => [
                'slug' => self::ADAPTER_ONLY,
                'prefix' => 'AdapterOnly',
                'title' => 'Adapter Only',
                'tagline' => 'Classic TinyAuth runtime, DB-backed allow/acl rules',
                'summary' => 'Replace the INI files with DB-backed allow/acl adapters. No Authorization plugin, no entity-level policies, no ownership scopes — just the admin UI in front of TinyAuth\'s classic request-level middleware.',
                'variant' => 'info',
                'icon' => '⚡',
            ],
            self::FULL_BACKEND => [
                'slug' => self::FULL_BACKEND,
                'prefix' => 'FullBackend',
                'title' => 'Full Backend',
                'tagline' => 'Everything: TinyAuth + Authorization + Policies + Scopes',
                'summary' => 'The richest mode. Request-level gating via TinyAuth middleware, entity-level enforcement via the Authorization plugin + TinyAuthPolicy, role hierarchy applied to rules, and ownership/team scopes narrowing list queries via applyScope().',
                'variant' => 'success',
                'icon' => '🎯',
            ],
            self::NATIVE_AUTH => [
                'slug' => self::NATIVE_AUTH,
                'prefix' => 'NativeAuth',
                'title' => 'Native Auth',
                'tagline' => 'Pure CakePHP Authorization, tinyauth-backend as admin UI only',
                'summary' => 'TinyAuth middleware is bypassed for this subtree — enforcement is 100% CakePHP Authorization. The same TinyAuthPolicy still reads the same DB rules, so the behavior matches Full Backend, but the code path is idiomatic `$this->Authorization->authorize()`.',
                'variant' => 'primary',
                'icon' => '🧩',
            ],
            self::EXTERNAL_ROLES => [
                'slug' => self::EXTERNAL_ROLES,
                'prefix' => 'ExternalRoles',
                'title' => 'External Roles',
                'tagline' => 'Role comes from session, not users.role_id',
                'summary' => 'Same rules, same enforcement as Full Backend — but the user\'s effective role is resolved via a session-backed `TinyAuthBackend.roleSource` callable, not `users.role_id`. Models multi-tenant / SSO scenarios where an upstream IdP owns role membership.',
                'variant' => 'warning',
                'icon' => '🔗',
            ],
        ];
    }

    /**
     * @param string $slug
     *
     * @return array<string, string>|null
     */
    public static function find(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    /**
     * Prefixes that exist as route/controller namespaces, keyed by slug.
     *
     * @return array<string, string>
     */
    public static function prefixMap(): array
    {
        $map = [];
        foreach (self::all() as $slug => $strategy) {
            $map[$slug] = $strategy['prefix'];
        }

        return $map;
    }

    /**
     * Reverse lookup: prefix → slug.
     *
     * @param string $prefix
     *
     * @return string|null
     */
    public static function slugForPrefix(string $prefix): ?string
    {
        foreach (self::all() as $slug => $strategy) {
            if ($strategy['prefix'] === $prefix) {
                return $slug;
            }
        }

        return null;
    }
}
