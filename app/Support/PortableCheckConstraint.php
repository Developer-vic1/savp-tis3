<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Preserve PostgreSQL CHECK semantics in isolated SQLite validation databases. */
final class PortableCheckConstraint
{
    public static function statement(string $sql): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement($sql);

            return;
        }
        if (preg_match('/^\s*ALTER TABLE (\w+) DROP CONSTRAINT (\w+)\s*$/i', $sql, $drop)) {
            foreach (['insert', 'update'] as $event) {
                DB::statement('DROP TRIGGER IF EXISTS "'.$drop[2].'_'.$event.'"');
            }

            return;
        }
        if (! preg_match('/^\s*ALTER\s+TABLE\s+(\w+)\s+ADD\s+CONSTRAINT\s+(\w+)\s+CHECK\s*\((.*)\)\s*(?:NOT VALID)?\s*$/is', $sql, $match)) {
            throw new RuntimeException('Unsupported portable CHECK statement');
        }
        [, $table, $name, $expression] = $match;
        $expression = str_replace('jsonb_typeof(', 'json_type(', $expression);
        $expression = str_replace("sha256 ~ '^[a-f0-9]{64}$'", "(length(sha256) = 64 AND sha256 NOT GLOB '*[^a-f0-9]*')", $expression);
        $columns = array_column(DB::select('PRAGMA table_info("'.$table.'")'), 'name');
        $parts = preg_split("/('(?:''|[^'])*')/", $expression, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $index => $part) {
            if ($index % 2 === 0) {
                foreach ($columns as $column) {
                    $part = preg_replace('/\b'.preg_quote($column, '/').'\b/', 'NEW."'.$column.'"', $part);
                }
                $parts[$index] = $part;
            }
        }
        $condition = implode('', $parts);
        foreach (['insert', 'update'] as $event) {
            DB::statement('CREATE TRIGGER "'.$name.'_'.$event.'" BEFORE '.strtoupper($event).' ON "'.$table.'" WHEN NOT ('.$condition.") BEGIN SELECT RAISE(ABORT, 'CHECK constraint failed: ".$name."'); END");
        }
    }
}
