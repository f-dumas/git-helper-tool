<?php

namespace FDTool\GitChecker\Command;

use FDTool\GitChecker\FileParser\GitDirectoryParser;
use FDTool\GitChecker\Git\GitShell;
use FDTool\GitChecker\Output\MessageOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GitCheckerCommand extends Command
{
    public const REASON_NOT_ON_MASTER = 1;
    public const REASON_FILES_MODIFIED = 2;
    public const REASON_FILES_ADDED = 3;
    public const REASON_FILES_DELETED = 4;
    public const REASON_FILES_UNTRACKED = 5;

    protected static $defaultName = 'faby:git-checker';

    private string $path;

    private array $projectWithLocalChanges = [];
    private float $commandStartTimestamp;
    private MessageOutput $outputDisplayer;
    private bool $ignoreMasterCheck = false;

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Git Checker: check your local git projects statuses')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command help you to check your git projects')
            ->addArgument('root-path', InputArgument::REQUIRED, 'Root path where your git projects are.')
            ->addOption('ignore-master-check', null, InputOption::VALUE_NONE, 'Ignore the check of projects on non-master branch.');

        parent::configure();

        $this->commandStartTimestamp = time();
    }

    private function initOptionsAndArguments(InputInterface $input): void
    {
        $this->path = (string)$input->getArgument("root-path");
        if ($input->getOption("ignore-master-check")) {
            $this->ignoreMasterCheck = true;
        }
    }

    private function initOutput(OutputInterface $output): void
    {
        $this->outputDisplayer = new MessageOutput($output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initOptionsAndArguments($input);
        $this->initOutput($output);
        // Default: status report
        $this->executeStatusReport();
        // Output display
        $this->outputDisplayer->display(
            sprintf("Command ended in %ss", time() - $this->commandStartTimestamp),
            "comment"
        );

        return Command::SUCCESS;
    }

    private function executeStatusReport(): void
    {
        foreach (GitDirectoryParser::parseFoldersToGetGitRepositories($this->path) as $folder) {
            $this->checkModifiedFiles($folder);
            $this->checkUntrackedFiles($folder);
            $this->checkNewFiles($folder);
            $this->checkRemovedFiles($folder);
            $this->checkProjectOnCustomBranches($folder);
        }
        $this->displayReport();
    }

    private function addProjectToNonConformList(string $folder, int $reason): void
    {
        $this->projectWithLocalChanges[$reason][] = $folder;
    }

    private function checkProjectOnCustomBranches(string $folder): void
    {
        if ($this->ignoreMasterCheck) {
            return;
        }

        try {
            GitShell::checkCurrentBranch($folder);
        } catch (\RuntimeException $e) {
            $this->addProjectToNonConformList(
                sprintf('%s - %s', $folder, $e->getMessage()),
                self::REASON_NOT_ON_MASTER
            );
        }
    }

    private function checkModifiedFiles(string $folder): void
    {
        if (GitShell::hasModifiedFiles($folder)) {
            $this->addProjectToNonConformList($folder, self::REASON_FILES_MODIFIED);
        }
    }

    private function checkUntrackedFiles($folder): void
    {
        if (GitShell::hasUntrackedFiles($folder)) {
            $this->addProjectToNonConformList($folder, self::REASON_FILES_UNTRACKED);
        }
    }

    private function checkNewFiles($folder): void
    {
        if (GitShell::hasAddedFiles($folder)) {
            $this->addProjectToNonConformList($folder, self::REASON_FILES_ADDED);
        }
    }

    private function checkRemovedFiles($folder): void
    {
        if (GitShell::hasDeletedFiles($folder)) {
            $this->addProjectToNonConformList($folder, self::REASON_FILES_DELETED);
        }
    }

    private function displayReport(): void
    {
        foreach ($this->projectWithLocalChanges as $reason => $projects) {
            $this->outputDisplayer->display(
                sprintf("Non-conform project: %s", static::getReasonMessage($reason)),
                "question"
            );
            foreach ($projects as $project) {
                $this->outputDisplayer->display(
                    sprintf("--- %s", $project),
                    "error"
                );
            }
        }
    }

    private static function getReasonMessage(int $reason): string
    {
        switch ($reason) {
            case static::REASON_NOT_ON_MASTER:
                return "Branch not on Master";
            case static::REASON_FILES_ADDED:
                return "Uncommited added files";
            case static::REASON_FILES_DELETED:
                return "Uncommited deleted files";
            case static::REASON_FILES_MODIFIED:
                return "Uncommited modified files";
            case static::REASON_FILES_UNTRACKED:
                return "Uncommited untracked files";
        }
    }
}
