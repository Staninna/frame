<?php

namespace Frame\Model;

use PDO;
use ReflectionClass;
use ReflectionException;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $attributes = [];
    protected array $original = [];
    protected static PDO $db;
    protected array $relations = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function fill(array $attributes): void
    {
        $this->attributes = $attributes;
        $this->original = $attributes;
    }

    public static function setDatabaseConnection(PDO $connection): void
    {
        self::$db = $connection;
    }

    public static function find($id): ?static
    {
        $model = new static();
        $stmt = self::$db->prepare("SELECT * FROM $model->table WHERE $model->primaryKey = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? new static($result) : null;
    }

    public static function where($column, $operator, $value): array
    {
        $model = new static();
        $stmt = self::$db->prepare("SELECT * FROM $model->table WHERE $column $operator :value");
        $stmt->execute(['value' => $value]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($result) => new static($result), $results);
    }

    public function save(): void
    {
        if (isset($this->attributes[$this->primaryKey])) {
            $this->update();
        } else {
            $this->insert();
        }
        $this->original = $this->attributes;
    }

    protected function insert(): void
    {
        $columns = implode(', ', array_keys($this->attributes));
        $placeholders = ':' . implode(', :', array_keys($this->attributes));
        $stmt = self::$db->prepare("INSERT INTO $this->table ($columns) VALUES ($placeholders)");
        $stmt->execute($this->attributes);
        $this->attributes[$this->primaryKey] = self::$db->lastInsertId();
    }

    protected function update(): void
    {
        $setClause = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($this->attributes)));
        $stmt = self::$db->prepare("UPDATE $this->table SET $setClause WHERE $this->primaryKey = :$this->primaryKey");
        $stmt->execute($this->attributes);
    }

    public function delete(): void
    {
        $stmt = self::$db->prepare("DELETE FROM $this->table WHERE $this->primaryKey = :$this->primaryKey");
        $stmt->execute([$this->primaryKey => $this->attributes[$this->primaryKey]]);
    }

    public function getTable(): string
    {
        if (!$this->table) {
            $className = (new ReflectionClass($this))->getShortName();
            $this->table = strtolower($className) . 's';
        }
        return $this->table;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        if (method_exists($this, $name)) {
            return $this->$name();
        }

        if (array_key_exists($name, $this->relations)) {
            return $this->relations[$name];
        }

        return null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Retrieves all related models for the current model
     *
     * Example: In a User->Post relationship:
     *   - User model: `public function posts() { return $this->hasMany(Post::class); }`
     *   - Resulting SQL: SELECT * FROM posts WHERE user_id = :id
     *   - :id is the current User's ID
     * Usage: $user->posts returns all associated Post models
     *
     * @param $relatedClass
     * @param $foreignKey
     * @param $localKey
     * @return array
     */
    public function hasMany($relatedClass, $foreignKey = null, $localKey = null): array
    {
        $localKey = $localKey ?: $this->primaryKey;
        $foreignKey = $foreignKey ?: strtolower(get_class($this)) . '_id';

        $relatedModel = new $relatedClass();

        $stmt = self::$db->prepare("SELECT * FROM $relatedModel->table WHERE $foreignKey = :id");
        $stmt->execute(['id' => $this->attributes[$localKey]]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map results to related model instances
        $this->relations[debug_backtrace()[1]['function']] = array_map(fn($result) => new $relatedClass($result), $results);
        return $this->relations[debug_backtrace()[1]['function']];
    }

    /**
     * Retrieves the related model that the current model belongs to
     *
     * Example: In a Post->User relationship:
     *   - Post model: `public function user() { return $this->belongsTo(User::class); }`
     *   - Resulting SQL: SELECT * FROM users WHERE id = :id
     *   - :id is the foreign key value in the current Post model
     * Usage: $post->user returns the associated User model
     *
     * Note: If multiple records are found, a warning is echoed and only the first result is returned.
     *
     * @param string $relatedClass The class name of the related model
     * @param string|null $foreignKey The foreign key in the current model (default: lowercase related model name + '_id')
     * @param string|null $ownerKey The primary key in the related model (default: related model's primaryKey)
     * @return mixed The related model instance or null if not found
     * @throws ReflectionException
     */
    public function belongsTo(string $relatedClass, string $foreignKey = null, string $ownerKey = null): mixed
    {
        $foreignKey = $foreignKey ?: strtolower((new ReflectionClass($relatedClass))->getShortName()) . '_id';
        $ownerKey = $ownerKey ?: (new $relatedClass())->primaryKey;

        $relatedModel = new $relatedClass();

        $stmt = self::$db->prepare("SELECT * FROM $relatedModel->table WHERE $ownerKey = :id");
        $stmt->execute(['id' => $this->attributes[$foreignKey]]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (count($result) > 1) { // TODO: Make warning configurable
            // DEBUG
            echo "Warning: Multiple results found for belongsTo relationship. Only the first result will be returned.";
        }

        $this->relations[debug_backtrace()[1]['function']] = $result ? new $relatedClass($result) : null;
        return $this->relations[debug_backtrace()[1]['function']];
    }

    /**
     * Retrieves a single related model for the current model
     *
     * Example: In a User->Profile relationship:
     *   - User model: `public function profile() { return $this->hasOne(Profile::class); }`
     *   - Resulting SQL: SELECT * FROM profiles WHERE user_id = :id LIMIT 1
     *   - :id is the current User's ID
     * Usage: $user->profile returns the associated Profile model
     *
     * Note: If multiple records are found, a warning is echoed and only the first result is returned.
     *
     * @param string $relatedClass The class name of the related model
     * @param string|null $foreignKey The foreign key in the related model (default: lowercase current model name + '_id')
     * @param string|null $localKey The local key in the current model (default: current model's primaryKey)
     * @return mixed The related model instance or null if not found
     */
    public function hasOne(string $relatedClass, string $foreignKey = null, string $localKey = null): mixed
    {
        $results = $this->hasMany($relatedClass, $foreignKey, $localKey);

        if (count($results) > 1) { // TODO: Make warning configurable
            // DEBUG
            echo "Warning: Multiple results found for hasOne relationship. Only the first result will be returned.";
        }

        return $results ? $results[0] : null;
    }

    /**
     * Retrieves all related models for a many-to-many relationship
     *
     * Example: In a User->Role relationship:
     *   - User model: `public function roles() { return $this->belongsToMany(Role::class); }`
     *   - Resulting SQL:
     *     SELECT roles.*
     *     FROM roles
     *     JOIN user_role ON roles.id = user_role.role_id
     *     WHERE user_role.user_id = :id
     *   - :id is the current User's ID
     * Usage: $user->roles returns all associated Role models
     *
     * @param string $relatedClass The class name of the related model
     * @param string|null $pivotTable The name of the pivot table (default: guessed from model names)
     * @param string|null $foreignPivotKey The foreign key of the current model in the pivot table
     * @param string|null $relatedPivotKey The foreign key of the related model in the pivot table
     * @return array An array of related model instances
     * @throws ReflectionException
     */
    public function belongsToMany(string $relatedClass, string $pivotTable = null, string $foreignPivotKey = null, string $relatedPivotKey = null): array
    {
        $foreignPivotKey = $foreignPivotKey ?: strtolower(get_class($this)) . '_id';
        $relatedPivotKey = $relatedPivotKey ?: strtolower((new ReflectionClass($relatedClass))->getShortName()) . '_id';
        $pivotTable = $pivotTable ?: $this->guessPivotTableName($relatedClass);

        $relatedModel = new $relatedClass();

        $stmt = self::$db->prepare("
            SELECT $relatedModel->table.* 
            FROM $relatedModel->table
            JOIN $pivotTable ON $relatedModel->table.$relatedModel->primaryKey = $pivotTable.$relatedPivotKey
            WHERE $pivotTable.$foreignPivotKey = :id
        ");

        $stmt->execute(['id' => $this->attributes[$this->primaryKey]]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->relations[debug_backtrace()[1]['function']] = array_map(fn($result) => new $relatedClass($result), $results);

        return $this->relations[debug_backtrace()[1]['function']];
    }

    /**
     * @throws ReflectionException
     */
    protected function guessPivotTableName($relatedClass): string
    {
        $tables = [
            strtolower((new ReflectionClass($this))->getShortName()),
            strtolower((new ReflectionClass($relatedClass))->getShortName())
        ];
        sort($tables);
        return implode('_', $tables);
    }

    public function isDirty($attribute = null): bool
    {
        if ($attribute) {
            return ($this->attributes[$attribute] ?? null) !== ($this->original[$attribute] ?? null);
        }
        return $this->attributes !== $this->original;
    }

    public function isClean($attribute = null): bool
    {
        return !$this->isDirty($attribute);
    }

    public function getDirty(): array
    {
        return array_diff_assoc($this->attributes, $this->original);
    }

    public function getOriginal($attribute = null)
    {
        if ($attribute) {
            return $this->original[$attribute] ?? null;
        }
        return $this->original;
    }

    public function fresh(): ?static
    {
        return static::find($this->attributes[$this->primaryKey]);
    }

    public function refresh(): static
    {
        $fresh = $this->fresh();
        if ($fresh) {
            $this->fill($fresh->attributes);
            $this->relations = [];
        }
        return $this;
    }
}