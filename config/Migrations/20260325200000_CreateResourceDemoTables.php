<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Create tables for demonstrating TinyAuth resource-level permissions.
 *
 * - articles: Demonstrates "own" scope (user_id)
 * - projects: Demonstrates "own" and "team" scopes (user_id, team_id)
 * - teams: Supporting table for team-based access
 * - Adds team_id to users table for team scope demos
 */
class CreateResourceDemoTables extends BaseMigration
{
    /**
     * @return void
     */
    public function change(): void
    {
        // Teams table
        $teams = $this->table('teams');
        $teams->addColumn('name', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => false,
        ]);
        $teams->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $teams->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $teams->create();

        // Add team_id to users (for team scope demo)
        $users = $this->table('users');
        $users->addColumn('team_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
            'after' => 'role_id',
        ]);
        $users->addIndex(['team_id']);
        $users->update();

        // Articles table - demonstrates "own" scope
        $articles = $this->table('articles');
        $articles->addColumn('user_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $articles->addColumn('title', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $articles->addColumn('body', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $articles->addColumn('status', 'string', [
            'default' => 'draft',
            'limit' => 20,
            'null' => false,
        ]);
        $articles->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $articles->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $articles->addIndex(['user_id']);
        $articles->addIndex(['status']);
        $articles->create();

        // Projects table - demonstrates "own" and "team" scopes
        $projects = $this->table('projects');
        $projects->addColumn('user_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $projects->addColumn('team_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
        ]);
        $projects->addColumn('name', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $projects->addColumn('description', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $projects->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $projects->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $projects->addIndex(['user_id']);
        $projects->addIndex(['team_id']);
        $projects->create();
    }
}
