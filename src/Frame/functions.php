<?php

/**
 * Get path to storage directory
 *
 * @param string $path
 * @return string
 */
function storage_path(string $path = ''): string
{
    return BASE_PATH . '/storage' . ($path ? '/' . $path : '');
}