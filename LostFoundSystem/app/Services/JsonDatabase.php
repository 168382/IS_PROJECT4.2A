<?php

namespace App\Services;

/**
 * JsonDatabase — A flat-file JSON database service.
 *
 * Provides CRUD operations using JSON files stored in database/json/.
 * Each "table" is a separate JSON file. Thread-safe via file locking.
 * This eliminates the need for MySQL/SQLite during development.
 */
class JsonDatabase
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = database_path('json');

        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
    }

    /**
     * Get the file path for a given table name.
     */
    protected function filePath(string $table): string
    {
        return $this->basePath . '/' . $table . '.json';
    }

    /**
     * Read all records from a table.
     */
    public function all(string $table): array
    {
        $file = $this->filePath($table);

        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Write all records to a table (overwrite).
     */
    protected function writeAll(string $table, array $data): void
    {
        $file = $this->filePath($table);
        $handle = fopen($file, 'c');
        flock($handle, LOCK_EX);
        ftruncate($handle, 0);
        fwrite($handle, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * Find a record by ID.
     */
    public function find(string $table, int $id): ?array
    {
        $records = $this->all($table);

        foreach ($records as $record) {
            if (isset($record['id']) && (int)$record['id'] === $id) {
                return $record;
            }
        }

        return null;
    }

    /**
     * Find a record by a specific field value.
     */
    public function findBy(string $table, string $field, $value): ?array
    {
        $records = $this->all($table);

        foreach ($records as $record) {
            if (isset($record[$field]) && $record[$field] === $value) {
                return $record;
            }
        }

        return null;
    }

    /**
     * Find all records matching a specific field value.
     */
    public function where(string $table, string $field, $value): array
    {
        $records = $this->all($table);

        return array_values(array_filter($records, function ($record) use ($field, $value) {
            return isset($record[$field]) && $record[$field] === $value;
        }));
    }

    /**
     * Insert a new record. Returns the record with auto-incremented ID.
     */
    public function insert(string $table, array $data): array
    {
        $records = $this->all($table);

        // Auto-increment ID
        $maxId = 0;
        foreach ($records as $record) {
            if (isset($record['id']) && (int)$record['id'] > $maxId) {
                $maxId = (int)$record['id'];
            }
        }

        $data['id'] = $maxId + 1;
        $data['created_at'] = $data['created_at'] ?? now()->toDateTimeString();
        $data['updated_at'] = $data['updated_at'] ?? now()->toDateTimeString();

        $records[] = $data;
        $this->writeAll($table, $records);

        return $data;
    }

    /**
     * Update a record by ID.
     */
    public function update(string $table, int $id, array $data): ?array
    {
        $records = $this->all($table);
        $updated = null;

        foreach ($records as &$record) {
            if (isset($record['id']) && (int)$record['id'] === $id) {
                $record = array_merge($record, $data);
                $record['updated_at'] = now()->toDateTimeString();
                $updated = $record;
                break;
            }
        }

        if ($updated) {
            $this->writeAll($table, $records);
        }

        return $updated;
    }

    /**
     * Delete a record by ID.
     */
    public function delete(string $table, int $id): bool
    {
        $records = $this->all($table);
        $original = count($records);

        $records = array_values(array_filter($records, function ($record) use ($id) {
            return !isset($record['id']) || (int)$record['id'] !== $id;
        }));

        if (count($records) < $original) {
            $this->writeAll($table, $records);
            return true;
        }

        return false;
    }

    /**
     * Count records in a table, optionally filtered.
     */
    public function count(string $table, ?string $field = null, $value = null): int
    {
        if ($field && $value !== null) {
            return count($this->where($table, $field, $value));
        }

        return count($this->all($table));
    }

    /**
     * Search records by keyword across multiple fields.
     */
    public function search(string $table, array $fields, string $keyword): array
    {
        $records = $this->all($table);
        $keyword = strtolower($keyword);

        return array_values(array_filter($records, function ($record) use ($fields, $keyword) {
            foreach ($fields as $field) {
                if (isset($record[$field]) && str_contains(strtolower((string)$record[$field]), $keyword)) {
                    return true;
                }
            }
            return false;
        }));
    }
}
