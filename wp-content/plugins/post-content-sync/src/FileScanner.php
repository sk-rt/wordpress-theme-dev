<?php

namespace PostContentSync;

/**
 * File Scanner Class
 *
 * Scans directories for HTML files recursively.
 */
class FileScanner
{
    /**
     * Plugin configuration
     *
     * @var array
     */
    private $config;

    /**
     * Constructor
     *
     * @param array $config Plugin configuration
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Scan base directory for HTML files recursively
     *
     * @return array Array of file information
     */
    public function scan()
    {
        $baseDir = $this->config['base_directory'] ?? '';

        if (empty($baseDir) || !is_dir($baseDir)) {
            return [];
        }

        return $this->scanDirectory($baseDir);
    }

    /**
     * Scan a directory for HTML files recursively
     *
     * @param string $directory Directory path
     * @return array Array of file information
     */
    private function scanDirectory($directory)
    {
        $files = [];
        $allowedExtensions = $this->config['allowed_extensions'] ?? ['html', 'htm'];
        $ignorePatterns = $this->config['ignore_files'] ?? [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $extension = strtolower($file->getExtension());
            if (!in_array($extension, $allowedExtensions)) {
                continue;
            }

            $filename = $file->getFilename();
            if ($this->shouldIgnoreFile($filename, $ignorePatterns)) {
                continue;
            }

            $files[] = [
                'path' => $file->getPathname(),
                'filename' => $filename,
                'relative_path' => str_replace($directory . '/', '', $file->getPathname()),
                'modified_time' => $file->getMTime(),
            ];
        }

        return $files;
    }

    /**
     * Check if file should be ignored
     *
     * @param string $filename Filename to check
     * @param array $patterns Array of ignore patterns
     * @return bool True if file should be ignored
     */
    private function shouldIgnoreFile($filename, $patterns)
    {
        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $filename)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get file count
     *
     * @return int Total number of files
     */
    public function getFileCount()
    {
        $files = $this->scan();
        return count($files);
    }
}
