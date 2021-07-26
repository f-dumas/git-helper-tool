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
            ->addOption('remove-merged-branches', null, InputOption::VALUE_NONE, 'Remove the branches that have already been merged to master.');

        parent::configure();

        $this->commandStartTimestamp = time();
    }

    private function initOptionsAndArguments(InputInterface $input): void
    {
        if ($input->getOption("remove-merged-branches")) {
            $this->removeMergedBranches = true;
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
        $this->outputDisplayer->display('Clean untracked files', "question");
        $this->outputDisplayer->display(
            GitShell::executeGitCleanUntrackedFiles()
        );
    }

    private function cleanIgnoredFiles(): void
    {
        $this->outputDisplayer->display('Clean ignored files', "question");
        $this->outputDisplayer->display(
            GitShell::executeGitCleanIgnoredFiles()
        );
    }

    private function cleanMergedBranches(): void
    {
        if (!$this->removeMergedBranches) {
            return;
        }

        $this->outputDisplayer->display('Remove merged branches', "question");
        $this->outputDisplayer->display(
            GitShell::removeMergedBranches()
        );
    }
}
