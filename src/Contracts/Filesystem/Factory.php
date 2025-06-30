<?php

namespace Kasi\Contracts\Filesystem;

interface Factory
{
    /**
     * Get a filesystem implementation.
     *
     * @param  string|null  $name
     * @return \Kasi\Contracts\Filesystem\Filesystem
     */
    public function disk($name = null);
}
