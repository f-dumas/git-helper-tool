<?php

namespace FDTool\GitChecker\Command;

use FDTool\GitChecker\Git\GitShell;
use FDTool\GitChecker\Output\MessageOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class GitCleanCommand extends Command
{
    protected static $defaultName = 'faby:git-clean';
    private float $commandStartTimestamp;
    private MessageOutput $outputDisplayer;
    private bool $removeMergedBranches = false;

    private const OPTION_UNTRACKED_FILES = 'remove-untracked-files';
    private const OPTION_IGNORED_FILES = 'remove-ignored-files';
    private const OPTION_MERGED_BRANCHES = 'remove-merged-branches';
    private const OPTION_ALL = 'all';

    // God mode
    private bool $enableAllOptions = false;
    private array $enabledConfigurations = [];


    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Git clean: remove untracked and ignored files and folders from your local git repository.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command help you to clean your git projects')
            ->addOption(static::OPTION_UNTRACKED_FILES, null, InputOption::VALUE_NONE, 'Remove untracked files and modifications.')
            ->addOption(static::OPTION_IGNORED_FILES, null, InputOption::VALUE_NONE, 'Remove ignored files.')
            ->addOption(static::OPTION_MERGED_BRANCHES, null, InputOption::VALUE_NONE, 'Remove the branches that have already been merged to master.')
            ->addOption(static::OPTION_ALL, null, InputOption::VALUE_NONE, 'Enable all options.');

        parent::configure();

        $this->commandStartTimestamp = time();
    }

    private function initOptionsAndArguments(InputInterface $input): void
    {
        if ($input->getOption(static::OPTION_ALL)) {
            $this->enableAllOptions = true;

            // Useless to check the other options
            return;
        }
        if ($input->getOption(static::OPTION_IGNORED_FILES)) {
            $this->enabledConfigurations[static::OPTION_IGNORED_FILES] = true;
        }
        if ($input->getOption(static::OPTION_UNTRACKED_FILES)) {
            $this->enabledConfigurations[static::OPTION_UNTRACKED_FILES] = true;
        }
        if ($input->getOption(static::OPTION_MERGED_BRANCHES)) {
            $this->enabledConfigurations[static::OPTION_MERGED_BRANCHES] = true;
        }
    }

    private function initOutput(OutputInterface $output): void
    {
        $this->outputDisplayer = new MessageOutput($output);
    }

    public function isOptionEnabled(string $option): bool
    {
        if ($this->enableAllOptions || (isset($this->enabledConfigurations[$option]) && $this->enabledConfigurations[$option])) {
            return true;
        }

        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initOptionsAndArguments($input);
        $this->initOutput($output);
        $this->cleanUntrackedFiles();
        $this->cleanIgnoredFiles();
        $this->cleanMergedBranches();

        $this->outputDisplayer->display(
            sprintf("Command ended in %ss", time() - $this->commandStartTimestamp),
            "comment"
        );

        return Command::SUCCESS;
    }

    private function cleanUntrackedFiles(): void
    {
        if (!$this->isOptionEnabled(self::OPTION_UNTRACKED_FILES)) {
            return;
        }
        $this->outputDisplayer->display('Clean untracked files', "question");
        $this->outputDisplayer->display(
            GitShell::executeGitCleanUntrackedFiles()
        );
    }

    private function cleanIgnoredFiles(): void
    {
        if (!$this->isOptionEnabled(self::OPTION_IGNORED_FILES)) {
            return;
        }
        $this->outputDisplayer->display('Clean ignored files', "question");
        $this->outputDisplayer->display(
            GitShell::executeGitCleanIgnoredFiles()
        );
    }

    private function cleanMergedBranches(): void
    {
        if (!$this->isOptionEnabled(self::OPTION_MERGED_BRANCHES)) {
            return;
        }
        $this->outputDisplayer->display('Remove merged branches', "question");
        $this->outputDisplayer->display(
            GitShell::removeMergedBranches()
        );
    }
}
