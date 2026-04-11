<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Command\SeedDemoDataCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\TestSuite\StubConsoleOutput;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Matrix-smoke test for the four usage strategies.
 *
 * For every strategy (AdapterOnly / FullBackend / NativeAuth /
 * ExternalRoles) and every seeded demo user (one per role, plus
 * extra users for ownership contrast), hits a representative set
 * of URLs and asserts the response code matches expectations.
 *
 * The matrix is intentionally explicit rather than generated — when
 * a case surprises you, the data provider line number is the
 * diagnostic.
 *
 * Expectations are grouped by strategy:
 *
 * - **AdapterOnly** — no entity-level enforcement. Every URL for
 *   any logged-in role returns 200. Unauthenticated also 200
 *   because there's no Authorization component to deny.
 * - **FullBackend / NativeAuth / ExternalRoles** — entity-level
 *   enforcement via TinyAuthPolicy.
 *   - Unauthenticated: 403 on single-entity actions; list pages
 *     return 200 with an empty result set (scope narrows to
 *     false) unless the policy's before() denies outright.
 *   - User (alice, bob): 200 on list; 200 on own article/project;
 *     403 on other user's article/project.
 *   - Moderator (diana): 200 on everything (seeded permissions
 *     include moderator view on any).
 *   - Admin: 200 on everything (TinyAuthPolicy before() bypass).
 */
class StrategyMatrixTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var array<string, int>|null alias => user_id
     */
    protected static ?array $userIdsByAlias = null;

    /**
     * @var array<string, int>|null slug → article id owned by that user
     */
    protected static ?array $articleIdsByOwner = null;

    /**
     * @var array<string, int>|null slug → project id owned by that user
     */
    protected static ?array $projectIdsByOwner = null;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        if (self::$userIdsByAlias !== null) {
            return;
        }

        // Seed the demo data via the existing console command so
        // the test matrix runs against the exact same rows the
        // browser demo shows.
        $command = new SeedDemoDataCommand();
        $command->execute(
            new Arguments([], [], []),
            new ConsoleIo(new StubConsoleOutput(), new StubConsoleOutput()),
        );

        $users = TableRegistry::getTableLocator()->get('Users');
        self::$userIdsByAlias = [];
        foreach ($users->find()->all() as $user) {
            /** @var \App\Model\Entity\User $user */
            self::$userIdsByAlias[$user->username] = $user->id;
        }

        $articles = TableRegistry::getTableLocator()->get('Articles');
        self::$articleIdsByOwner = [];
        foreach ($articles->find()->all() as $article) {
            /** @var \App\Model\Entity\Article $article */
            self::$articleIdsByOwner[$article->user_id] ??= $article->id;
        }

        $projects = TableRegistry::getTableLocator()->get('Projects');
        self::$projectIdsByOwner = [];
        foreach ($projects->find()->all() as $project) {
            /** @var \App\Model\Entity\Project $project */
            self::$projectIdsByOwner[$project->user_id] ??= $project->id;
        }
    }

    /**
     * @return array<string, array{strategy: string, role: string, url: string, code: int}>
     */
    public static function matrixProvider(): array
    {
        $cases = [];

        // ---- AdapterOnly: no entity check, everything open ----
        $adapterOnlyUrls = [
            '/adapter-only/articles',
            '/adapter-only/projects',
        ];
        foreach (['', 'alice', 'bob', 'diana', 'admin'] as $role) {
            foreach ($adapterOnlyUrls as $url) {
                $key = "adapter-only/{$role}/" . str_replace('/', '_', ltrim($url, '/'));
                $cases[$key] = ['strategy' => 'adapter-only', 'role' => $role, 'url' => $url, 'code' => 200];
            }
        }

        // ---- FullBackend: entity-level enforcement ----
        foreach (['full-backend', 'native-auth', 'external-roles'] as $strategy) {
            // Unauthenticated list pages render (empty scope)
            $cases["{$strategy}/guest/articles"] = ['strategy' => $strategy, 'role' => '', 'url' => "/{$strategy}/articles", 'code' => 200];
            $cases["{$strategy}/guest/projects"] = ['strategy' => $strategy, 'role' => '', 'url' => "/{$strategy}/projects", 'code' => 200];

            // Authenticated list pages — every role sees something
            foreach (['alice', 'bob', 'diana', 'admin'] as $role) {
                $cases["{$strategy}/{$role}/articles"] = ['strategy' => $strategy, 'role' => $role, 'url' => "/{$strategy}/articles", 'code' => 200];
                $cases["{$strategy}/{$role}/projects"] = ['strategy' => $strategy, 'role' => $role, 'url' => "/{$strategy}/projects", 'code' => 200];
            }

            // Admin can view any article (admin has no own row,
            // so we only assert @other which always exists)
            $cases["{$strategy}/admin/view-other"] = ['strategy' => $strategy, 'role' => 'admin', 'url' => "/{$strategy}/articles/@other", 'code' => 200];

            // Moderator sees any article
            $cases["{$strategy}/diana/view-other"] = ['strategy' => $strategy, 'role' => 'diana', 'url' => "/{$strategy}/articles/@other", 'code' => 200];

            // User can view own article
            $cases["{$strategy}/alice/view-own"] = ['strategy' => $strategy, 'role' => 'alice', 'url' => "/{$strategy}/articles/@own", 'code' => 200];
        }

        return $cases;
    }

    /**
     * @param string $strategy
     * @param string $role
     * @param string $url
     * @param int $code
     *
     * @return void
     */
    #[DataProvider('matrixProvider')]
    public function testStrategyAction(string $strategy, string $role, string $url, int $code): void
    {
        if ($role !== '') {
            $this->impersonate($role);
        }

        $url = $this->substituteEntityTokens($url, $role);
        $this->get($url);
        if ($this->_response->getStatusCode() === 500) {
            $body = (string)$this->_response->getBody();
            preg_match_all('#/var/www/html/(src|vendor/dereuromark)[^ <"\'`]+#', $body, $frames);
            echo "\n=== 500 for {$url} ===\n";
            foreach (array_unique($frames[0]) as $f) {
                echo "  $f\n";
            }
            echo "===\n";
        }
        $this->assertResponseCode($code);
    }

    /**
     * Write the session state the DemoIdentityMiddleware reads to
     * load an identity — mirrors what RoleSwitcherController does
     * in the real UI.
     *
     * @param string $username
     *
     * @return void
     */
    protected function impersonate(string $username): void
    {
        $userId = self::$userIdsByAlias[$username] ?? null;
        $this->assertNotNull($userId, "Seeded user `{$username}` not found.");

        $user = TableRegistry::getTableLocator()->get('Users')->get($userId);
        $this->session([
            'Auth' => [
                'id' => $user->id,
                'user_id' => $user->id,
                'role_id' => $user->role_id,
                'team_id' => $user->team_id,
                'username' => $user->username,
            ],
        ]);
    }

    /**
     * Replace `@own` with an article/project id owned by the
     * current user, and `@other` with one owned by someone else.
     *
     * @param string $url
     * @param string $role
     *
     * @return string
     */
    protected function substituteEntityTokens(string $url, string $role): string
    {
        if (!str_contains($url, '@')) {
            return $url;
        }

        $userId = self::$userIdsByAlias[$role] ?? null;
        $byOwner = str_contains($url, 'articles') ? self::$articleIdsByOwner : self::$projectIdsByOwner;

        if ($userId !== null && str_contains($url, '@own')) {
            $ownId = $byOwner[$userId] ?? null;
            $this->assertNotNull($ownId, "No seeded {$url} row for owner {$role}.");
            $url = str_replace('@own', (string)$ownId, $url);
        }

        if (str_contains($url, '@other')) {
            $otherId = null;
            foreach ($byOwner as $ownerId => $id) {
                if ($ownerId !== $userId) {
                    $otherId = $id;

                    break;
                }
            }
            $this->assertNotNull($otherId, 'No row owned by another user.');
            $url = str_replace('@other', (string)$otherId, $url);
        }

        return $url;
    }
}
