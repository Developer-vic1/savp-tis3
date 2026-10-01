<?php

namespace Tests\Unit;

use App\Support\PrivateFilePath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PrivateFilePathTest extends TestCase
{
    #[DataProvider('unsafePaths')]
    public function test_download_path_cannot_escape_the_private_directory(?string $path): void
    {
        $this->assertFalse(PrivateFilePath::valid($path, 'aula-virtual/materiales'));
    }

    public function test_file_under_the_expected_directory_is_valid(): void
    {
        $this->assertTrue(PrivateFilePath::valid('aula-virtual/materiales/abc.pdf', 'aula-virtual/materiales'));
    }

    public static function unsafePaths(): array
    {
        return [[null], ['../.env'], ['aula-virtual/materiales/../../.env'], ['aula-virtual/materiales/./file.pdf'],
            ['aula-virtual/materiales\\..\\.env'], ["aula-virtual/materiales/test\0.pdf"],
            ['aula-virtual/entregas/other.pdf'], ['/aula-virtual/materiales/test.pdf']];
    }
}
