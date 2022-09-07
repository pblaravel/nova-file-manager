<?php

declare(strict_types=1);

namespace BBSLab\NovaFileManager\Contracts\Entities;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;

interface Entity
{
    public function extension(): string;

    public function id(): string;

    public function lastModifiedAt(): string;

    public function lastModifiedAtTimestamp(): Carbon;

    public function meta(): array;

    public function mime(): string;

    public function name(): string;

    public function signedExpirationTime(): Carbon;

    public function size(): int|string;

    public function toArray(): array;

    public function type(): string;

    public function url(): string;

    public static function make(Filesystem $fileSystem, string $path, string $disk): self;
}
