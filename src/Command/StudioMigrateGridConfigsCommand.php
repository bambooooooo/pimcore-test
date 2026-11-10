<?php

namespace App\Command;

use App\Studio\Grid\ClassicGridConfigConverter;
use Doctrine\DBAL\Connection;
use Pimcore\Bundle\StudioBackendBundle\Entity\Grid\GridConfiguration;
use Pimcore\Bundle\StudioBackendBundle\Entity\Grid\GridConfigurationFavorite;
use Pimcore\Bundle\StudioBackendBundle\Grid\Repository\ConfigurationRepositoryInterface;
use Pimcore\Bundle\StudioBackendBundle\Grid\Schema\ColumnConfiguration;
use Pimcore\Bundle\StudioBackendBundle\Grid\Service\ColumnConfigurationServiceInterface;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Saved grid layouts of the classic admin UI (table gridconfigs) are not read by Pimcore Studio.
 * This command copies them into Studio grid configurations - owner, global and per-user sharing and favourites
 * included. Already migrated layouts (marker in the description) are skipped, so it can run on every deploy.
 */
#[AsCommand(
    name: 'app:studio:migrate-grid-configs',
    description: 'Copies saved classic admin grid layouts into Pimcore Studio grid configurations',
)]
class StudioMigrateGridConfigsCommand extends AbstractCommand
{
    public const MARKER = '[classic grid config #%d]';

    private array $favourites = [];

    public function __construct(
        private readonly Connection $db,
        private readonly ColumnConfigurationServiceInterface $columnConfigurationService,
        private readonly ConfigurationRepositoryInterface $repository,
        private readonly ClassicGridConfigConverter $converter,
        private readonly RequestStack $requestStack,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only report what would be migrated')
            ->addOption('id', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Only these classic grid config ids');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');
        $ids = array_map('intval', $input->getOption('id'));
        $available = [];
        $migrated = 0;

        foreach ($this->db->fetchAllAssociative("SELECT * FROM gridconfigs WHERE type = 'object' ORDER BY id") as $row)
        {
            $id = (int) $row['id'];

            if (($ids && !in_array($id, $ids, true)) || $this->isMigrated($id))
            {
                continue;
            }

            $classic = json_decode((string) $row['config'], true) ?: [];
            $classId = (string) $row['classId'];
            $owner = User::getById((int) $row['ownerId']) ?? $this->anyAdmin();

            if (!ClassDefinition::getById($classId) || !$owner)
            {
                $this->io->warning(sprintf('#%d %s: class "%s" or owner missing - skipped', $id, $row['name'], $classId));
                continue;
            }

            $available[$classId] ??= $this->availableColumns($classId, $owner);
            ['columns' => $columns, 'dropped' => $dropped] = $this->converter->convert($classic, $available[$classId]);

            if (!empty($classic['filter']))
            {
                $dropped[] = 'saved filters are not migrated - set them again in Studio';
            }

            $this->io->writeln(sprintf('<info>#%d</info> %s (%s): %d columns', $id, $row['name'], $classId, count($columns)));
            foreach ($dropped as $line)
            {
                $this->io->writeln('    <comment>not migrated:</comment> ' . $line);
            }

            if (!$columns)
            {
                continue;
            }

            if (!$dryRun)
            {
                $this->repository->create($this->configuration($row, $classic, $columns, $owner));
            }

            $migrated++;
        }

        $this->io->success(sprintf('%s %d grid configuration(s).', $dryRun ? 'Would migrate' : 'Migrated', $migrated));

        return Command::SUCCESS;
    }

    private function configuration(array $row, array $classic, array $columns, User $owner): GridConfiguration
    {
        $configuration = new GridConfiguration();
        $configuration->setClassId((string) $row['classId']);
        $configuration->setName(mb_substr((string) $row['name'], 0, 255));
        $configuration->setDescription(trim(((string) $row['description']) . "\n" . sprintf(self::MARKER, $row['id'])));
        $configuration->setPageSize((int) ($classic['pageSize'] ?? 0) ?: 50);
        $configuration->setSaveFilter(false);
        $configuration->setShareGlobal((bool) $row['shareGlobally']);
        $configuration->setOwner($owner->getId());
        $configuration->setColumns($columns);

        foreach ($this->db->fetchFirstColumn('SELECT sharedWithUserId FROM gridconfig_shares WHERE gridConfigId = ?', [$row['id']]) as $userId)
        {
            if (User::getById((int) $userId))
            {
                $configuration->addShare($configuration->createShare((int) $userId));
            }
        }

        $favourites = $this->db->fetchAllAssociative(
            "SELECT ownerId, objectId FROM gridconfig_favourites WHERE gridConfigId = ? AND type = 'object'",
            [$row['id']]
        );

        foreach ($favourites as $favourite)
        {
            // classic objectId 0 = favourite for every folder of the class, Studio needs a folder: the root
            $folder = max(1, (int) $favourite['objectId']);
            $user = (int) $favourite['ownerId'];
            $taken = $this->db->fetchOne(
                'SELECT COUNT(*) FROM bundle_studio_grid_configuration_favorites WHERE user = ? AND folder = ? AND classId = ?',
                [$user, $folder, $row['classId']]
            );

            // Studio keeps one favourite per user, class and folder
            if ($taken || isset($this->favourites["$user-$folder-{$row['classId']}"]) || !User::getById($user))
            {
                continue;
            }

            $this->favourites["$user-$folder-{$row['classId']}"] = true;
            $entity = new GridConfigurationFavorite();
            $entity->setFolder($folder);
            $entity->setUser($user);
            $entity->setClassId((string) $row['classId']);
            $entity->setConfiguration($configuration);
            $configuration->addFavorite($entity);
        }

        return $configuration;
    }

    /**
     * @return array<string, array{type: string, group: array, localizable: bool, config: array}>
     */
    private function availableColumns(string $classId, User $user): array
    {
        $columns = [];

        // Studio services read the current request (locale, user) - there is none on the CLI
        if (!$this->requestStack->getCurrentRequest())
        {
            $this->requestStack->push(Request::create('/pimcore-studio/api'));
        }

        /** @var ColumnConfiguration $column */
        foreach ($this->columnConfigurationService->getAvailableDataObjectColumnConfiguration($classId, 1, $user) as $column)
        {
            $columns[$column->getKey()] ??= [
                'type' => $column->getType(),
                'group' => $column->getGroup(),
                'localizable' => $column->isLocalizable(),
                'config' => $column->getConfig(),
            ];
        }

        return $columns;
    }

    private function isMigrated(int $id): bool
    {
        return (bool) $this->db->fetchOne(
            'SELECT COUNT(*) FROM bundle_studio_grid_configurations WHERE description LIKE ?',
            ['%' . sprintf(self::MARKER, $id) . '%']
        );
    }

    private function anyAdmin(): ?User
    {
        $list = new User\Listing();
        $list->setCondition('admin = 1 AND active = 1 AND type = "user"');
        $list->setLimit(1);

        return $list->getUsers()[0] ?? null;
    }
}
