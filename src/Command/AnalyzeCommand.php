<?php

declare(strict_types=1);

namespace Midnight\DependencyAnalyzer\Command;

use Midnight\DependencyAnalyzer\Package;
use Midnight\DependencyAnalyzer\Project;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function preg_quote;
use function str_replace;

final class AnalyzeCommand extends Command
{
    private const FILTER = 'filter';
    private const NAME = 'analyze';
    private const PROJECT_PATH = 'project_path';

    protected function configure(): void
    {
        parent::configure();

        $this->setName(self::NAME)
            ->addArgument(
                self::PROJECT_PATH,
                InputArgument::OPTIONAL,
                'Root path of the project to be analyzed. Defaults to the current working directory.',
                '.'
            )
            ->addOption(
                self::FILTER,
                'f',
                InputOption::VALUE_REQUIRED,
                'Filter by package name. Accepts placeholders: myvendor/*-plugin'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $projectRoot */
        $projectRoot = $input->getArgument(self::PROJECT_PATH);
        /** @var string|null $filter */
        $filter = $input->getOption(self::FILTER);
        $verbose = $output->isVerbose();

        $project = new Project($projectRoot);
        $projectFiles = $project->countFiles();

        foreach ($project->dependencies() as $dependency) {
            if ($filter !== null && self::packageMatchesFilter($dependency, $filter)) {
                continue;
            }
            $usingFiles = $project->countFilesUsingSymbolsFrom($dependency);
            $usage = (float)$usingFiles / (float)$projectFiles * 100.0;

            if ($usage < 1) {
                $template = "%s (used by <fg=red>%.2f%%</> of all files)";
            } elseif ($usage < 5) {
                $template = "%s (used by <fg=yellow>%.2f%%</> of all files)";
            } else {
                $template = "%s (used by %.2f%% of all files)";
            }
            $output->writeln(\Safe\sprintf($template, $dependency->name(), $usage));

            if (!$verbose) {
                continue;
            }

            foreach ($project->usedSymbolsDefinedBy($dependency) as $symbol) {
                $nFiles = $project->countFilesUsing($symbol);
                $usage = (float)$nFiles / (float)$projectFiles * 100.0;

                if ($usage < 10) {
                    $template = '  %s (used in <fg=red>%.2f%%</> of all files)';
                } else {
                    $template = '  %s (used in %.2f%% of all files)';
                }

                $output->writeln(\Safe\sprintf($template, $symbol, $usage));
            }
        }

        return 0;
    }

    private static function packageMatchesFilter(Package $package, string $filter): bool
    {
        $tmp = 'THEPLACEHOLDER';
        $filterRegex = '/^' . str_replace($tmp, '.*', preg_quote(str_replace('*', $tmp, $filter), '/')) . '$/';
        return \Safe\preg_match($filterRegex, $package->name() ?? '') !== 1;
    }
}
