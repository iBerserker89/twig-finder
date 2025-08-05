<?php

namespace TwigFinder\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveCallbackFilterIterator;

/**
 * This class implements the `search` CLI command
 * to locate `.html.twig` template files in Drupal projects.
 */
class SearchCommand extends Command
{
    // Defines the CLI command name (e.g., php twig-finder.php search)
    protected static $defaultName = 'search';

    /**
     * Command configuration:
     * - Required argument: the name (or part of it) of the .twig file
     * - Optional argument: project base path
     * - Option: --exact for exact filename match
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Searches for .html.twig files by name')
            ->addArgument('template', InputArgument::REQUIRED, 'Template filename or substring')
            ->addArgument('path', InputArgument::OPTIONAL, 'Root directory to search in', getcwd())
            ->addOption('exact', null, InputOption::VALUE_NONE, 'Search using exact filename match');
    }

    /**
     * Main execution method triggered when the command runs.
     * Performs recursive search and displays categorized results.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Reads the template name or pattern
        $template = $input->getArgument('template');

        // Reads the path to search in (defaults to current working directory)
        $path = $input->getArgument('path') ?? getcwd();

        // Checks if --exact option was passed
        $exact = $input->getOption('exact');

        $output->writeln("<info>Searching for .twig files matching '{$template}' in {$path}...</info>");

        $found = false;

        // List of directory names to ignore during search
        $ignore_dirs = ['core', 'vendor', 'node_modules', 'tests', 'demo_umami'];

        // Creates a filtered recursive iterator, skipping ignored directories
        $iterator = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($path),
                function ($file, $key, $iterator) use ($ignore_dirs) {
                    // Apply ignore logic only to directories
                    if ($iterator->hasChildren()) {
                        foreach ($ignore_dirs as $dir) {
                            if (str_contains($file->getRealPath(), DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR)) {
                                return false;
                            }
                        }
                    }
                    return true;
                }
            )
        );

        // Iterates over all valid files
        foreach ($iterator as $file) {
            // Checks if file is .html.twig
            if ($file->isFile() && str_contains($file->getFilename(), '.html.twig')) {
                $filename = $file->getFilename();

                // Determines if file matches search pattern (exact or partial)
                $match = $exact ? ($filename === $template) : str_contains($filename, $template);

                if ($match) {
                    $found = true;
                    $path_rendered = $file->getPathname();

                    // Classifies the file by context based on its path
                    if (str_contains($path_rendered, '/themes/custom')) {
                        $prefix = "🌐 Custom theme";
                    } elseif (str_contains($path_rendered, '/themes/contrib') || str_contains($path_rendered, '/core/themes')) {
                        $prefix = "🎨 Base theme";
                    } elseif (str_contains($path_rendered, '/modules/contrib')) {
                        $prefix = "🧱 Contributed module";
                    } elseif (str_contains($path_rendered, '/tests/') || str_contains($path_rendered, '/demo')) {
                        $prefix = "🧪 Test/Demo";
                    } else {
                        $prefix = "📁 Other";
                    }

                    // Outputs the categorized result
                    $output->writeln("{$prefix}: {$path_rendered}");
                }
            }
        }

        // Outputs a message if no matches were found
        if (!$found) {
            $output->writeln("<comment>No .twig file found matching '{$template}'</comment>");

            $guesses = $this->guessTwigSuggestions($template);
            if (!empty($guesses)) {
                $output->writeln("<info>Suggested template filenames based on Drupal theme system:</info>");
                
                $matches = $this->searchSuggestionsInFilesystem($guesses, $path);

                foreach ($guesses as $guess) {
                    if (in_array($guess, array_map('basename', $matches))) {
                        $match_path = array_filter($matches, fn($p) => basename($p) === $guess);
                        foreach ($match_path as $path_found) {
                            $output->writeln("✅ Found: {$path_found}");
                        }
                    } else {
                        $output->writeln("🧠 {$guess} (not found)");
                    }
                }
            }
        }

        // Return success status code
        return Command::SUCCESS;
    }

    /**
     * Guesses possible Twig template filenames based on Drupal's theme suggestions.
     *
     * Examples:
     * - user_register_form → form--user-register.html.twig
     * - node__article → node--article.html.twig
     * - block__main_navigation → block--main-navigation.html.twig
     * - views_view__events__block_1 → views-view--events--block-1.html.twig
     *
     * @param string $hook
     *   The hook or machine name to base the suggestion on.
     *
     * @return string[]
     *   A list of suggested .html.twig filenames.
     */
    private function guessTwigSuggestions(string $hook): array {
        $suggestions = [];

        // Case 1: *_form → form--[name].html.twig
        if (str_ends_with($hook, '_form')) {
            $base = 'form';
            $specific = str_replace('_form', '', $hook);
            $suggestions[] = "{$base}--" . str_replace('_', '-', $specific) . ".html.twig";
        }

        // Case 2: node__article → node--article.html.twig
        if (str_starts_with($hook, 'node__')) {
            $parts = explode('__', $hook);
            if (count($parts) >= 2) {
                $suggestions[] = 'node--' . str_replace('_', '-', $parts[1]) . '.html.twig';
            }
        }

        // Case 3: block__main_navigation → block--main-navigation.html.twig
        if (str_starts_with($hook, 'block__')) {
            $parts = explode('__', $hook);
            if (count($parts) >= 2) {
                $suggestions[] = 'block--' . str_replace('_', '-', $parts[1]) . '.html.twig';
            }
        }

        // Case 4: views_view__[view_name]__[display_id]
        if (str_starts_with($hook, 'views_view__')) {
            $parts = explode('__', $hook);
            if (count($parts) >= 3) {
                $view = str_replace('_', '-', $parts[1]);
                $display = str_replace('_', '-', $parts[2]);
                $suggestions[] = "views-view--{$view}--{$display}.html.twig";
            }
        }

        return $suggestions;
    }

    /**
     * Searches the filesystem recursively for files that exactly match
     * one of the given Twig template suggestions.
     *
     * @param array $suggestions
     *   A list of possible .html.twig filenames.
     * @param string $base_path
     *   The base directory to start the search.
     *
     * @return string[]
     *   A list of full paths where suggested templates were found.
     */
    private function searchSuggestionsInFilesystem(array $suggestions, string $base_path): array {
        $found = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($base_path),
                function ($file, $key, $iterator) {
                    // Skip dot folders
                    return $file->getFilename() !== '.' && $file->getFilename() !== '..';
                }
            )
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filename = $file->getFilename();

                foreach ($suggestions as $suggested) {
                    if ($filename === $suggested) {
                        $found[] = $file->getPathname();
                    }
                }
            }
        }

        return $found;
    }
}
