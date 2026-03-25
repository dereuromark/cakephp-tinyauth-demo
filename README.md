# CakePHP TinyAuth Demo

A demo application showcasing [TinyAuth](https://github.com/dereuromark/cakephp-tinyauth) and [TinyAuth Backend](https://github.com/dereuromark/cakephp-tinyauth-backend) plugins for CakePHP 5.x.

## Features

- **Role-based access control (RBAC)** with database-backed permissions
- **TinyAuth Backend** admin interface at `/admin/auth/`
- **Role simulation** for testing different permission levels
- **Demo controllers** showing protected and public actions

## Requirements

- PHP 8.2+
- MySQL/MariaDB
- [DDEV](https://ddev.com/) (recommended) or any local development environment

## Quick Start with DDEV

```bash
# Clone the repository
git clone https://github.com/dereuromark/cakephp-tinyauth-demo.git
cd cakephp-tinyauth-demo

# Start DDEV
ddev start

# Install dependencies
ddev composer install

# Set up configuration
cp config/.env.example config/.env
cp config/app_local.example.php config/app_local.php

# Generate a security salt and update config/.env
php -r "echo bin2hex(random_bytes(32));"
# Edit config/.env and replace __REPLACE_WITH_YOUR_SALT__

# Run migrations
ddev exec bin/cake migrations migrate
ddev exec bin/cake migrations migrate -p TinyAuthBackend

# Sync controllers to TinyAuth database
ddev exec bin/cake tiny_auth_backend sync

# Seed demo data (roles, scopes, public actions)
ddev exec bin/cake seed_demo_data

# Visit the app
ddev launch
```

## Demo Roles

The demo includes three roles with hierarchical permissions:

| Role | Level | Description |
|------|-------|-------------|
| admin | 1 | Full access to everything |
| moderator | 2 | Access to reports and moderation features |
| user | 3 | Basic user access |

## URLs

- **Home**: `/` - Shows current role and available actions
- **TinyAuth Admin**: `/admin/auth/` - Permission management interface
- **Role Switcher**: Use the dropdown on the home page to simulate different roles

## Configuration

### Roles Configuration

Roles are defined in `config/roles.php`:

```php
return [
    'user' => 1,
    'moderator' => 2,
    'admin' => 3,
];
```

### TinyAuth Backend

The admin interface provides:

- **Dashboard**: Overview of controllers, actions, and roles
- **ACL**: Manage role-based permissions per action
- **Allow**: Configure public (unauthenticated) actions
- **Roles**: Manage role definitions
- **Resources**: Entity-level permissions (optional)
- **Scopes**: Permission scopes (optional)

## Testing Permissions

1. Visit the home page
2. Use the role switcher to select a role (user, moderator, or admin)
3. Try accessing different pages:
   - `/dashboard/stats` - Public (allowed for all)
   - `/reports/usage` - Public
   - `/reports/audit` - Protected (admin only by default)
   - `/admin/users` - Protected (admin only)

## Demo Scopes

Scopes define conditions for entity-level access control. The seeder creates these scopes:

| Scope | Entity Field | User Field | Use Case |
|-------|-------------|------------|----------|
| `own` | `user_id` | `id` | User can only access their own records |
| `team` | `team_id` | `team_id` | User can access records from their team |
| `department` | `department_id` | `department_id` | Department-based access |
| `company` | `company_id` | `company_id` | Company-wide access |

### How Scopes Work

When a role has a resource permission with a scope attached, the condition is evaluated:

```
entity.{entity_field} === user.{user_field}
```

Example: A "user" role with "edit" ability on "Articles" with "own" scope means:
- User can edit articles where `article.user_id === user.id`

Without a scope, the permission grants access to all entities.

## License

MIT License
