<?php

namespace FDTool\GitChecker\Output;


use Symfony\Component\Console\Output\OutputInterface;

class MessageOutput
{
    protected OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function display(?string $message, string $colorType = "info"): void
    {
        $this->output->writeln(
            sprintf("<%s>$message</>", $colorType)
        );
    }

    public function displayCustomColor(?string $message, string $color = null, string $backgroundColor = null): void
    {
        $this->output->writeln($message);
    }
}
