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

class SecurityHardeningTest extends TestCase
{
    use IntegrationTestTrait;

    protected static bool $seeded = false;

    public function setUp(): void
    {
        parent::setUp();

        if (self::$seeded) {
            return;
        }

        $command = new SeedDemoDataCommand();
        $command->execute(
            new Arguments([], [], []),
            new ConsoleIo(new StubConsoleOutput(), new StubConsoleOutput()),
        );

        self::$seeded = true;
    }

    public function testExternalRoleSwitcherRejectsGet(): void
    {
        $this->get('/strategy/external-roles/role?role=admin');

        $this->assertResponseCode(405);
    }

    public function testExternalRoleSwitcherAcceptsPost(): void
    {
        $this->enableCsrfToken();
        $this->post('/strategy/external-roles/role', ['role' => 'admin']);

        $this->assertRedirect('/strategy');
        $this->assertSession('admin', 'ExternalRoles.role');
    }

    public function testFullBackendArticleEditIgnoresOwnershipField(): void
    {
        $alice = TableRegistry::getTableLocator()->get('Users')->find()->where(['username' => 'alice'])->firstOrFail();
        $bob = TableRegistry::getTableLocator()->get('Users')->find()->where(['username' => 'bob'])->firstOrFail();
        $article = TableRegistry::getTableLocator()->get('Articles')->find()->where(['user_id' => $alice->id])->firstOrFail();

        $this->impersonate($alice->id, $alice->role_id, $alice->team_id, 'alice');
        $this->enableCsrfToken();
        $this->post('/full-backend/articles/' . $article->id . '/edit', [
            'title' => 'Retained ownership',
            'body' => $article->body,
            'status' => $article->status,
            'user_id' => $bob->id,
        ]);

        $this->assertRedirect('/full-backend/articles/' . $article->id);

        $reloaded = TableRegistry::getTableLocator()->get('Articles')->get($article->id);
        $this->assertSame($alice->id, $reloaded->user_id);
        $this->assertSame('Retained ownership', $reloaded->title);
    }

    public function testFullBackendProjectEditIgnoresTeamAndOwnerFields(): void
    {
        $alice = TableRegistry::getTableLocator()->get('Users')->find()->where(['username' => 'alice'])->firstOrFail();
        $charlie = TableRegistry::getTableLocator()->get('Users')->find()->where(['username' => 'charlie'])->firstOrFail();
        $project = TableRegistry::getTableLocator()->get('Projects')->find()->where(['user_id' => $alice->id])->firstOrFail();

        $this->impersonate($alice->id, $alice->role_id, $alice->team_id, 'alice');
        $this->enableCsrfToken();
        $this->post('/full-backend/projects/' . $project->id . '/edit', [
            'name' => 'Same owner and team',
            'description' => $project->description,
            'user_id' => $charlie->id,
            'team_id' => $charlie->team_id,
        ]);

        $this->assertRedirect('/full-backend/projects/' . $project->id);

        $reloaded = TableRegistry::getTableLocator()->get('Projects')->get($project->id);
        $this->assertSame($alice->id, $reloaded->user_id);
        $this->assertSame($alice->team_id, $reloaded->team_id);
        $this->assertSame('Same owner and team', $reloaded->name);
    }

    protected function impersonate(int $userId, int $roleId, ?int $teamId, string $username): void
    {
        $this->session([
            'Auth' => [
                'id' => $userId,
                'user_id' => $userId,
                'role_id' => $roleId,
                'team_id' => $teamId,
                'username' => $username,
            ],
        ]);
    }
}
