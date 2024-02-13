<?php

namespace Lib\Prisma\Classes;

use Lib\Prisma\Model\IModel;
use Lib\Prisma\Classes\Validator;

class Post Implements IModel {

    private $id;
    private $title;
    private $content;
    private $published;
    private $createdAt;
    private $authorId;

    public $author;
    public $column;

    protected $fields;

    private $pdo;

    public function __construct($pdo, $data = null) {
        $this->pdo = $pdo;

        $this->fields = array(
            'id' =>
            array(
                'name' => 'id',
                'type' => 'String',
                'isNullable' => '',
                'isPrimaryKey' => '1',
                'decorators' =>
                array (
                    'unique' => true,
                    'id' => true,
                    'default' => 'cuid',
                  )
                ),
            'title' =>
            array(
                'name' => 'title',
                'type' => 'String',
                'isNullable' => '',
                'isPrimaryKey' => '',
                'decorators' =>
                array (
                  )
                ),
            'content' =>
            array(
                'name' => 'content',
                'type' => 'String',
                'isNullable' => '',
                'isPrimaryKey' => '',
                'decorators' =>
                array (
                  )
                ),
            'published' =>
            array(
                'name' => 'published',
                'type' => 'Boolean',
                'isNullable' => '',
                'isPrimaryKey' => '',
                'decorators' =>
                array (
                    'default' => true,
                  )
                ),
            'createdAt' =>
            array(
                'name' => 'createdAt',
                'type' => 'DateTime',
                'isNullable' => '',
                'isPrimaryKey' => '',
                'decorators' =>
                array (
                    'default' => 'now',
                  )
                ),
            'authorId' =>
            array(
                'name' => 'authorId',
                'type' => 'String',
                'isNullable' => '1',
                'isPrimaryKey' => '',
                'decorators' =>
                array (
                  )
                ),
            'author' =>
            array(
                'name' => 'author',
                'type' => 'User',
                'isNullable' => '1',
                'isPrimaryKey' => '',
                'decorators' =>
                array (
                    'relation' => 
                    array (
                      'name' => 'author',
                      'model' => 'User',
                      'fields' => 
                      array (
                        0 => 'authorId',
                      ),
                      'references' => 
                      array (
                        0 => 'id',
                      ),
                      'onDelete' => 'SetNull',
                      'onUpdate' => 'Cascade',
                      'type' => 'OneToMany',
                      'tableName' => 'Users',
                    ),
                  )
                ),
            );

        $this->column = new class() {
            public $id = "id";
            public $title = "title";
            public $content = "content";
            public $published = "published";
            public $createdAt = "createdAt";
            public $authorId = "authorId";
            public $author = "author";
        };

        if ($data) {
            $this->id = $data['id'] ?? null;
            $this->title = $data['title'] ?? null;
            $this->content = $data['content'] ?? null;
            $this->published = $data['published'] ?? null;
            $this->createdAt = $data['createdAt'] ?? null;
            $this->authorId = $data['authorId'] ?? null;
            $this->author = new User($this->pdo, $data['author'] ?? null);
        }
    }

    public function getId() {
        return $this->id;
    }

    public function setId($value) {
        $validatedValue = Validator::validateString($value);
        $this->id = $validatedValue;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($value) {
        $validatedValue = Validator::validateString($value);
        $this->title = $validatedValue;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($value) {
        $validatedValue = Validator::validateString($value);
        $this->content = $validatedValue;
    }

    public function getPublished() {
        return $this->published;
    }

    public function setPublished($value) {
        $validatedValue = Validator::validateBoolean($value);
        $this->published = $validatedValue;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setCreatedAt($value) {
        $validatedValue = Validator::validateDateTime($value);
        $this->createdAt = $validatedValue;
    }

    public function getAuthorId() {
        return $this->authorId;
    }

    public function setAuthorId($value) {
        $validatedValue = Validator::validateString($value);
        $this->authorId = $validatedValue;
    }

    protected function includeAuthor($items) {
        if (empty($items)) {
            return $items;
        }

        $singleRecord = false;
        if (isset($items['id'])) {
            $items = [$items];
            $singleRecord = true;
        }

        $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $quotedTableName = $dbType == 'pgsql' ? "\"Users\"" : "`Users`";
        $columnName = $dbType == 'pgsql' ? "\"id\"" : "`id`";
        $foreignKeyIds = array_column($items, 'authorId');
        $foreignKeyIds = array_unique($foreignKeyIds);

        if (empty($foreignKeyIds)) {
            return $singleRecord ? reset($items) : $items;
        }

        $placeholders = rtrim(str_repeat('?, ', count($foreignKeyIds)), ', ');
        $sql = "SELECT * FROM $quotedTableName WHERE $columnName IN ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($foreignKeyIds));
        $relatedRecords = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $indexedRecords = [];
        foreach ($relatedRecords as $record) {
            $indexedRecords[$record['id']] = $record;
        }

        foreach ($items as &$item) {
            $item['author'] = $indexedRecords[$item['authorId']] ?? null;
        }

        return $singleRecord ? reset($items) : $items;
    }

    protected function fetchCreatedRecord($lastInsertId, $select = null, $include = null) {
        $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $quotedTableName = $dbType == 'pgsql' ? "\"Posts\"" : "`Posts`";
        $fieldsToSelect = '*';
        if ($select && is_array($select)) {
            $fieldsToSelect = implode(', ', array_map(function ($field) use ($dbType) {
                return $dbType == 'pgsql' ? "\"$field\"" : "`$field`";
            }, array_keys(array_filter($select, function ($include) { return $include; }))));
        }

        $columnName = $dbType == 'pgsql' ? "\"id\"" : "`id`";
        $sql = "SELECT $fieldsToSelect FROM $quotedTableName WHERE $columnName = :lastInsertId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':lastInsertId', $lastInsertId, \PDO::PARAM_INT);
        $stmt->execute();
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$record) {
            return null;
        }

        if ($include && is_array($include)) {
            foreach ($include as $relation => $shouldInclude) {
                if ($shouldInclude) {
                    $methodName = 'include' . ucfirst($relation);
                    if (method_exists($this, $methodName)) {
                        $relatedItems = [$record];
                        $record = $this->{$methodName}($relatedItems)[0];
                    }
                }
            }
        }

        return $record;
    }

    /**
     * Creates a new record in the '$tableName' table.
     *
     * This method inserts a new row into the table using the validated data.
     * It generates UUIDs or CUIDs for appropriate fields.
     * It starts a transaction and commits it after the insert operation is successful.
     * In case of an exception, the transaction is rolled back.
     * 
     * @param array $params Associative array where keys are column names and values are the data to be inserted.
     *                       'data' key is mandatory for the record's attributes.
     *                       'select' and 'include' keys are optional for specifying what to return.
     * @return mixed Returns the created record with specified 'select' and 'include' fields, or the ID of the newly inserted record on success, false on failure.
     * @throws \Exception Throws an exception if the database operation fails.
     *
     * Example Usage:
     * $prisma = new Prisma();
     * $newUser = $prisma->${$modelName}->create([
     *     'data' => [
     *         'name' => 'John Doe',
     *         'email' => 'john.doe@example.com',
     *         // ... other fields ...
     *     ],
     *     'select' => ['id' => true, 'name' => true],
     *     'include' => ['profile' => true]
     * ]);
     * if ($newUser) {
     *     echo "New user created: " . print_r($newUser, true);
     * } else {
     *     echo "Failed to create new user";
     * }
     */
    public function create($params) {
        $select = $params['select'] ?? null;
        $include = $params['include'] ?? null;
        unset($params['select'], $params['include']);
        $data = $params;

        $primaryKeyField = '';
        $insertFields = [];
        $placeholders = [];
        $bindings = [];
        $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $quotedTableName = $dbType == 'pgsql' ? "\"Posts\"" : "`Posts`";

        foreach ($this->fields as $field) {
            $fieldName = $field['name'];
            $fieldType = $field['type'];
            $isNullable = $field['isNullable'];

            if (!empty($field['decorators']['id'])) {
                $primaryKeyField = $fieldName;
            }

            if (isset($field['decorators']['default'])) {
                if ($field['decorators']['default'] === 'uuid') {
                    $bindings[$fieldName] = \Ramsey\Uuid\Uuid::uuid4()->toString();
                } elseif ($field['decorators']['default'] === 'cuid') {
                    $bindings[$fieldName] = (new \Hidehalo\Nanoid\Client())->generateId(21);
                }
            } elseif (array_key_exists($fieldName, $data) || !$isNullable) {
                $validateMethodName = "validate" . ucfirst($fieldType);
                $bindings[$fieldName] = isset($data[$fieldName]) ? Validator::$validateMethodName($data[$fieldName]) : null;
            }

            if (array_key_exists($fieldName, $bindings)) {
                $insertFields[] = $fieldName;
                $placeholders[] = ":$fieldName";
            }
        }

        $fieldStr = implode(', ', $insertFields);
        $placeholderStr = implode(', ', $placeholders);

        try {
            $this->pdo->beginTransaction();
            $sql = $dbType == 'pgsql' ? "INSERT INTO $quotedTableName ($fieldStr) VALUES ($placeholderStr) RETURNING id" : "INSERT INTO $quotedTableName ($fieldStr) VALUES ($placeholderStr)";
            $stmt = $this->pdo->prepare($sql);

            foreach ($bindings as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();
            $lastInsertId = $dbType == 'pgsql' ? $stmt->fetch(\PDO::FETCH_ASSOC)[$primaryKeyField] : $this->pdo->lastInsertId();

            $this->pdo->commit();

            // Optionally fetch and return the created record with specified 'select' and 'include' fields
            if ($select || $include) {
                // Placeholder for 'fetchCreatedRecord' logic
                return $this->fetchCreatedRecord($lastInsertId, $select, $include);
            }

            return $lastInsertId ?: true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Finds a unique record in the 'Posts' table based on provided criteria.
     * Optionally includes related records based on the 'include' parameter.
     * 
     * @param array $criteria Associative array of criteria for searching the record.
     *                         The 'include' key specifies which related records to include.
     * @return array|false The fetched record as an associative array, including any specified related records, or false if not found.
     * @throws \Exception Throws an exception if the query or inclusion of related records fails.
     *
     * Example Usage:
     * $prisma = new Prisma();
     * $uniqueCriteria = [
     *     'email' => 'john.doe@example.com', // Assuming 'email' is a unique field
     *     'include' => ['profile' => true] // Including related 'profile' records if needed
     * ];
     * $userRecord = $prisma->Post->findUnique($uniqueCriteria);
     * if ($userRecord) {
     *     echo "User found: " . print_r($userRecord, true);
     * } else {
     *     echo "No user found with the given criteria";
     * }
     */
    public function findUnique($criteria, $format = 'array')
    {
        // Ensure 'include' is processed as an associative array with relation names as keys
        $includes = isset($criteria['include']) && is_array($criteria['include']) ? array_flip($criteria['include']) : [];
        foreach ($includes as $key => &$value) {
            $value = true; // Assuming you want to include all specified relations
        }
        unset($value); // Break the reference with the last element
        unset($criteria['include']); // Correctly process 'include' for relationship handling

        $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $quotedTableName = $dbType == 'pgsql' ? "\"Posts\"" : "`Posts`";
        $sql = "SELECT * FROM $quotedTableName WHERE ";
        $conditions = [];
        $bindings = [];
        if (isset($criteria['id'])) {
            $conditions[] = "id = :id";
            $validatedValue = Validator::validateString($criteria['id']);
            $bindings[':id'] = $validatedValue;
        }
        if (empty($conditions)) {
            throw new \Exception("No valid criteria provided for finding a unique record.");
        }

        $sql .= implode(' AND ', $conditions);
        $stmt = $this->pdo->prepare($sql);
        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$record) {
            return [];
        }

        // Handle related models inclusion as requested in the 'include' parameter
        foreach ($includes as $relation => $include) {
            if ($include) {
                $includeMethodName = "include" . ucfirst($relation);
                if (method_exists($this, $includeMethodName)) {
                    $record = $this->$includeMethodName($record);
                }
            }
        }

        if ($format === 'object') {
            return new User($this->pdo, $record);
        }

        return $record;
    }

    /**
     * Retrieves multiple records from the 'Posts' table based on the provided criteria.
     * Supports filtering, selecting specific fields, LIKE search for specified fields, distinct records,
     * sorting, pagination using 'take' for limit and 'skip' for offset, and cursor-based pagination.
     * Additionally, includes validation for input criteria to ensure safe query execution.
     *
     * @param array $criteria Associative array for filtering, selecting, ordering, limiting results, and cursor-based pagination.
     *                         Supports 'select' for specifying fields, 'contains' for LIKE searches,
     *                         'distinct' for unique records, 'take' as limit, 'skip' as offset,
     *                         'orderBy' for sorting, and 'cursor' for specifying the starting point.
     * @return array An array of associative arrays, each representing a record that matches the criteria.
     */
    public function findMany($criteria = [], $format = 'array') {
        $includes = isset($criteria['include']) && is_array($criteria['include']) ? array_flip($criteria['include']) : [];
        foreach ($includes as $key => &$value) {
            $value = true; // Assuming you want to include all specified relations
        }
        unset($value); // Break the reference with the last element
        unset($criteria['include']); // Correctly process 'include' for relationship handling

        $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $quotedTableName = $dbType == 'pgsql' ? "\"Posts\"" : "`Posts`";
        $distinct = isset($criteria['distinct']) && $criteria['distinct'] ? 'DISTINCT' : '';

        $selectFields = '*';
        if (isset($criteria['select']) && is_array($criteria['select'])) {
            $selectFields = implode(', ', array_map(function ($field) use ($dbType) {
                return $dbType == 'pgsql' ? "\"$field\"" : "`$field`";
            }, array_keys($criteria['select'])));
        }

        $sql = "SELECT $distinct $selectFields FROM $quotedTableName";
        $conditions = [];
        $bindings = [];

        if (isset($criteria['cursor']) && is_array($criteria['cursor'])) {
            foreach ($criteria['cursor'] as $field => $value) {
                $criteria[$field] = ['>=' => $value];
                $fieldQuoted = $dbType == 'pgsql' ? "\"$field\"" : "`$field`";
                $conditions[] = "$fieldQuoted >= :cursor_$field";
                $bindings[":cursor_$field"] = $value;
            }
            unset($criteria['cursor']);
            if (!isset($criteria['skip'])) {
                $criteria['skip'] = 1;
            }
        }

        foreach ($criteria as $key => $value) {
            if (in_array($key, ['take', 'skip', 'orderBy', 'contains', 'distinct', 'select', 'cursor'])) continue;
            $fieldQuoted = $dbType == 'pgsql' ? "\"$key\"" : "`$key`";
            if (is_array($value) && isset($value['contains'])) {
                $likeOperator = $dbType == 'pgsql' ? 'ILIKE' : 'LIKE';
                $validatedValue = Validator::validateString($value['contains']); // Validate input
                $conditions[] = "$fieldQuoted $likeOperator :$key";
                $bindings[":$key"] = '%' . $validatedValue . '%';
            } else {
                $validatedValue = Validator::validateString($value); // Validate input
                $conditions[] = "$fieldQuoted = :$key";
                $bindings[":$key"] = $validatedValue;
            }
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        if (isset($criteria['orderBy'])) {
            $sql .= " ORDER BY " . $criteria['orderBy'];
        }
        if (isset($criteria['take'])) {
            $sql .= " LIMIT " . intval($criteria['take']);
        }
        if (isset($criteria['skip'])) {
            $sql .= " OFFSET " . intval($criteria['skip']);
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!$items) {
            return [];
        }

        // Include related models as requested in the 'include' parameter
        foreach ($includes as $relation => $include) {
            if ($include) {
                $includeMethodName = "include" . ucfirst($relation);
                if (method_exists($this, $includeMethodName)) {
                    $items = $this->$includeMethodName($items);
                }
            }
        }

        if ($format === 'object') {
            $result = [];
            foreach ($items as $item) {
                $result[] = new Post($this->pdo, $item); // Convert each item into a User object.
            }
            return $result;
        }

        return $items;
    }

    /**
     * Finds the first record in the 'Posts' table that matches the provided criteria, with support for ordering, including relations, and selecting specific fields.
     * 
     * @param array $criteria Associative array for filtering, sorting, including relations, and selecting fields.
     *                         Supports 'orderBy' for sorting, 'include' for relations, and 'select' for field subsets.
     * @return array|false The first matching record as an associative array, including any specified related records, or false if none found.
     * @throws \Exception Throws an exception if the query fails.
     *
     * Example Usage:
     * $prisma = new Prisma();
     * $criteria = [
     *     'status' => 'active',
     *     'orderBy' => ['created_at' => 'desc'],
     *     'include' => ['profile' => true],
     *     'select' => ['id' => true, 'name' => true]
     * ];
     * $firstUser = $prisma->Post->findFirst($criteria);
     * if ($firstUser) {
     *     echo "First user found: " . print_r($firstUser, true);
     * } else {
     *     echo "No user found with the given criteria.";
     * }
     */
    public function findFirst($criteria = [], $format = 'array') {
        // Ensure 'include' is processed as an associative array with relation names as keys
        $includes = isset($criteria['include']) && is_array($criteria['include']) ? array_flip($criteria['include']) : [];
        foreach ($includes as $key => &$value) {
            $value = true; // Assuming you want to include all specified relations
        }
        unset($value); // Break the reference with the last element
        unset($criteria['include']); // Correctly process 'include' for relationship handling
        
        // Handle 'select' functionality
        $selectFields = '*';
        if (isset($criteria['select']) && is_array($criteria['select'])) {
            $selectFieldsList = [];
            foreach ($criteria['select'] as $field => $included) {
                if ($included) {
                    $selectFieldsList[] = $field;
                }
            }
            $selectFields = implode(', ', $selectFieldsList);
        }
        
        $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $quotedTableName = $dbType == 'pgsql' ? "\"Posts\"" : "`Posts`";
        $sql = "SELECT $selectFields FROM $quotedTableName";
        $conditions = [];
        $bindings = [];

        // Build conditions and handle 'orderBy'
        foreach ($criteria as $key => $value) {
            if (in_array($key, ['orderBy', 'select'])) {
                continue; // Handle 'orderBy' and 'select' separately
            }

            $condition = "$key = :$key";
            $bindings[":$key"] = $value;
            $conditions[] = $condition;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        if (isset($criteria['orderBy']) && is_array($criteria['orderBy'])) {
            $orderByConditions = [];
            foreach ($criteria['orderBy'] as $field => $direction) {
                $orderByConditions[] = "$field $direction";
            }
            $sql .= " ORDER BY " . implode(', ', $orderByConditions);
        }
        $sql .= " LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$record) {
            return [];
        }
        
        // Include related models as requested
        foreach ($includes as $relation => $include) {
            if ($include) {
                $includeMethodName = "include" . ucfirst($relation);
                if (method_exists($this, $includeMethodName)) {
                    $record = $this->$includeMethodName($record);
                }
            }
        }

        if ($format === 'object') {
            return new User($this->pdo, $record);
        }

        return $record;
    }

    /**
     * Updates a record in the 'Posts' table identified by a unique identifier.
     * 
     * This method allows updating a specific record based on its unique identifier.
     * It constructs a SQL update query dynamically, using only the fields provided in the $data array.
     * Each field value is validated before updating.
     * It starts a transaction and commits it after the update operation is successful.
     * In case of an exception, the transaction is rolled back.
     * After executing the update, it retrieves the updated record and returns it.
     * 
     * @param array $identifier Associative array representing the unique identifier of the record to be updated.
     * @param array $data Associative array where keys are column names and values are the new data to be updated.
     * @return array|false The updated record as an associative array, or false if not found.
     * @throws \Exception Throws an exception if the database operation fails.
     *
     * Example Usage:
     * $prisma = new Prisma();
     * $updateData = ['name' => 'New Name', 'email' => 'newemail@example.com'];
     * $identifier = ['id' => 1];
     * $updatedRecord = $prisma->UserModel->update($identifier, $updateData);
     */
    public function update($identifier, $data) {
        // Start transaction
        $this->pdo->beginTransaction();

        try {
            // Determine the database type and set appropriate quotes for table name
            $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
            $quotedTableName = $dbType == 'pgsql' ? "\"Posts\"" : "`Posts`";
            $sql = "UPDATE $quotedTableName SET ";
            $updateFields = [];
            $bindings = [];

            foreach ($this->fields as $field) {
                $fieldName = $field['name'];
                if (array_key_exists($fieldName, $data)) {
                    $validateMethodName = 'validate' . ucfirst($field['type']);
                    $validatedValue = Validator::$validateMethodName($data[$fieldName]);
                    $updateFields[] = "$fieldName = :$fieldName";
                    $bindings[":$fieldName"] = $validatedValue;
                }
            }
            
            $sql .= implode(', ', $updateFields);

            if (!empty($identifier)) {
                $whereClauses = [];
                foreach ($identifier as $fieldName => $fieldValue) {
                    if (array_key_exists($fieldName, $this->fields)) {
                        $whereClauses[] = "$fieldName = :where_$fieldName";
                        $bindings[":where_$fieldName"] = $fieldValue;
                    }
                }

                if (!empty($whereClauses)) {
                    $sql .= " WHERE " . implode(' AND ', $whereClauses);
                }
            }

            $stmt = $this->pdo->prepare($sql);
            foreach ($bindings as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $this->pdo->commit(); // Commit transaction

            return $this->findUnique($identifier); // Return the updated record
        } catch (\Exception $e) {
            $this->pdo->rollBack(); // Rollback transaction on error
            throw $e;
        }
    }

    /**
     * Deletes a record from the 'Posts' table identified by a unique identifier.
     * 
     * This method deletes a specific record based on its unique identifier.
     * It constructs a SQL delete query dynamically, using the unique fields provided in the $identifier array.
     * It starts a transaction and commits it after the delete operation is successful.
     * In case of an exception, the transaction is rolled back.
     * The method returns the number of rows affected by the delete operation.
     * 
     * @param array $identifier Associative array representing the unique identifier of the record to be deleted.
     * @return int The number of rows affected.
     * @throws \Exception Throws an exception if the database operation fails.
     *
     * Example Usage:
     * $prisma = new Prisma();
     * $identifier = ['id' => 1];
     * $deletedRows = $prisma->UserModel->delete($identifier);
     * echo "Number of deleted rows: " . $deletedRows;
     */
    public function delete($identifier) {
        // Start transaction
        $this->pdo->beginTransaction();

        try {
            // Determine the database type and set appropriate quotes for table name
            $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
            $quotedTableName = $dbType == 'pgsql' ? "\"Posts\"" : "`Posts`";
            $sql = "DELETE FROM $quotedTableName WHERE ";
            $whereClauses = [];
            $bindings = [];

            foreach ($this->fields as $field) {
                $fieldName = $field['name'];
                if (array_key_exists($fieldName, $identifier)) {
                    $whereClauses[] = "$fieldName = :$fieldName";
                    $bindings[":$fieldName"] = $identifier[$fieldName];
                }
            }

            $sql .= implode(' AND ', $whereClauses);
            $stmt = $this->pdo->prepare($sql);
            foreach ($bindings as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $affectedRows = $stmt->rowCount();
            $this->pdo->commit(); // Commit transaction

            return $affectedRows; // Return the number of affected rows
        } catch (\Exception $e) {
            $this->pdo->rollBack(); // Rollback transaction on error
            throw $e;
        }
    }

    /**
     * Upserts a record in the 'Posts' table.
     * 
     * If a record with the specified unique criteria exists, it updates the record.
     * Otherwise, it creates a new record.
     * The method dynamically identifies the primary key for the model and uses it to determine
     * whether to perform an insert or an update operation.
     * 
     * @param array $criteria Unique criteria to identify the record to be updated or inserted.
     * @param array $data Data to be updated or inserted.
     * @return mixed ID of the upserted record or true if the operation was successful.
     * @throws \Exception Throws an exception if the database operation fails.
     */
    public function upsert($criteria, $data) {
        $existingRecord = $this->findUnique($criteria);
        if ($existingRecord) {
            // Update existing record
            $updateResult = $this->update($criteria, $data);
            // Return primary key of updated record, or true if update was successful without specific return
            return $existingRecord['id'] ?? true;
        } else {
            // Insert new record and return its primary key or true if insert was successful
            return $this->create($data);
        }
    }

    /**
     * Performs an aggregate operation on the 'Posts' table.
     * 
     * @param array $criteria Associative array specifying the aggregate operation and conditions.
     *                         Example: ['function' => 'COUNT', 'field' => '*', 'where' => ['status' => 'active']]
     * @return mixed The result of the aggregate operation.
     * @throws \Exception Throws an exception if the database operation fails.
     */
    public function aggregate($criteria) {
        // Determine the database type and set appropriate quotes for table name
        $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $quotedTableName = $dbType == 'pgsql' ? "\"Posts\"" : "`Posts`";
        if (!isset($criteria['function']) || !isset($criteria['field'])) {
            throw new \Exception('Aggregate function and field must be specified.');
        }

        $aggregateFunction = strtoupper($criteria['function']);
        $field = $criteria['field'];
        $sql = "SELECT $aggregateFunction($field) FROM $quotedTableName";

        if (isset($criteria['where'])) {
            $conditions = [];
            foreach ($criteria['where'] as $key => $value) {
                $conditions[] = "$key = :$key";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->pdo->prepare($sql);

        if (isset($criteria['where'])) {
            foreach ($criteria['where'] as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchColumn(); // Fetch the aggregate result
    }

    /**
     * Groups records in the 'Posts' table and performs aggregate operations.
     * 
     * @param array $criteria Array specifying the fields to group by.
     * @param array $aggregates Array specifying the aggregate operations (e.g., COUNT, SUM).
     * @return array An array of results with grouped data.
     * @throws \Exception Throws an exception if the database operation fails.
     */
    public function groupBy($criteria, $aggregates) {
        // Determine the database type and set appropriate quotes for table name
        $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $quotedTableName = $dbType == 'pgsql' ? "\"Posts\"" : "`Posts`";
        $groupByFields = implode(', ', $criteria);
        $aggregateFields = array_map(fn($a) => "{$a['function']}({$a['field']}) AS {$a['alias']}", $aggregates);
        $sql = "SELECT $groupByFields, " . implode(', ', $aggregateFields) . " FROM $quotedTableName GROUP BY $groupByFields";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Updates multiple records in the 'Posts' table based on provided criteria.
     * 
     * This method updates records that match the specified criteria within a transaction.
     * The criteria should contain key-value pairs where keys are the names of the fields
     * and values are the conditions to match. The data array contains the values to be updated.
     * 
     * @param array $criteria Associative array of criteria for selecting the records to be updated.
     * @param array $data Associative array where keys are column names and values are the new data to be updated.
     * @return int The number of rows affected by the update.
     * @throws \Exception Throws an exception if the database operation fails.
     *
     * Example Usage:
     * $prisma = new Prisma();
     * $criteria = ['status' => 'inactive'];
     * $updateData = ['status' => 'active'];
     * $updatedRows = $prisma->Post->updateMany($criteria, $updateData);
     */
    public function updateMany($criteria, $data) {
        try {
            $this->pdo->beginTransaction();
            // Determine the database type for potential syntax differences
            $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
            $quotedTableName = $dbType === 'pgsql' ? "\"Posts\"" : "`Posts`";
            $sql = "UPDATE $quotedTableName SET ";
            $updateFields = [];
            foreach ($data as $field => $value) {
                $updateFields[] = "$field = :$field";
            }
            $sql .= implode(', ', $updateFields);

            $conditions = [];
            foreach ($criteria as $key => $value) {
                $conditions[] = "$key = :c_$key";
            }
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }

            $stmt = $this->pdo->prepare($sql);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            foreach ($criteria as $key => $value) {
                $stmt->bindValue(":c_$key", $value);
            }

            $stmt->execute();
            $affectedRows = $stmt->rowCount();
            $this->pdo->commit();
            return $affectedRows;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Deletes multiple records from the 'Posts' table based on provided criteria.
     * 
     * This method deletes records that match the specified criteria within a transaction.
     * The criteria should contain key-value pairs where keys are the names of the fields
     * and values are the conditions for deletion.
     * 
     * @param array $criteria Associative array of criteria for selecting the records to be deleted.
     * @return int The number of rows affected by the delete operation.
     * @throws \Exception Throws an exception if the database operation fails.
     *
     * Example Usage:
     * $prisma = new Prisma();
     * $deleteCriteria = ['status' => 'inactive'];
     * $deletedRows = $prisma->Post->deleteMany($deleteCriteria);
     */
    public function deleteMany($criteria) {
        try {
            $this->pdo->beginTransaction();
            // Determine the database type for potential syntax differences
            $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
            $quotedTableName = $dbType === 'pgsql' ? "\"Posts\"" : "`Posts`";
            $sql = "DELETE FROM $quotedTableName WHERE ";
            $conditions = [];
            $bindings = [];

            foreach ($criteria as $key => $value) {
                $conditions[] = "$key = :$key";
                $bindings[":$key"] = $value;
            }

            if (!empty($conditions)) {
                $sql .= implode(' AND ', $conditions);
            }

            $stmt = $this->pdo->prepare($sql);

            foreach ($bindings as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $affectedRows = $stmt->rowCount();
            $this->pdo->commit();
            return $affectedRows; // Return the number of deleted rows
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Counts the number of records in the 'Posts' table based on provided criteria.
     * 
     * This method counts records that match the specified criteria. The criteria should
     * contain key-value pairs where keys are the names of the fields and values are the
     * conditions to match.
     * 
     * @param array $criteria Associative array of criteria for selecting the records to be counted.
     * @return int The number of records that match the criteria.
     * @throws \Exception Throws an exception if the database operation fails.
     *
     * Example Usage:
     * $prisma = new Prisma();
     * $countCriteria = ['status' => 'active'];
     * $activeUserCount = $prisma->Post->count($countCriteria);
     */
    public function count($criteria) {
        // Determine the database type for potential syntax differences
        $dbType = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $quotedTableName = $dbType === 'pgsql' ? "\"Posts\"" : "`Posts`";
        $sql = "SELECT COUNT(*) FROM $quotedTableName WHERE ";
        $conditions = [];
        $bindings = [];

        foreach ($criteria as $key => $value) {
            $conditions[] = "$key = :$key";
            $bindings[":$key"] = $value;
        }

        if (!empty($conditions)) {
            $sql .= implode(' AND ', $conditions);
        } else {
            $sql = "SELECT COUNT(*) FROM $quotedTableName"; // If no criteria, count all records
        }

        $stmt = $this->pdo->prepare($sql);

        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchColumn(); // Fetch the count result
    }

    /**
     * Executes a raw SQL command that does not return a result set.
     * 
     * This method is suitable for SQL statements like INSERT, UPDATE, DELETE.
     * It returns the number of rows affected by the SQL command.
     *
     * @param string $sql The raw SQL command to be executed.
     * @return int The number of rows affected.
     * @throws \Exception Throws an exception if the database operation fails.
     */
    public function executeRaw($sql) {
        try {
            $stmt = $this->pdo->exec($sql); // Execute the raw SQL command
            return $stmt; // Return the number of affected rows
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Executes a raw SQL query and returns the result set.
     * 
     * This method is suitable for SELECT queries or when expecting a return value.
     * It returns an array containing all of the result set rows.
     *
     * @param string $sql The raw SQL query to be executed.
     * @return array The result set as an array.
     * @throws \Exception Throws an exception if the database operation fails.
     */
    public function queryRaw($sql) {
        try {
            $stmt = $this->pdo->query($sql); // Execute the raw SQL query
            return $stmt->fetchAll(); // Fetch and return all rows from the result set
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Executes a set of operations within a database transaction.
     *
     * This method accepts an array of callable functions, each representing a database operation.
     * These operations are executed within a single database transaction. If any operation fails,
     * the entire transaction is rolled back. If all operations succeed, the transaction is committed.
     *
     * @param array $operations An array of callable functions for transactional execution.
     * @return void
     * @throws \Exception Throws an exception if the transaction fails.
     *
     * Example Usage:
     * $prisma = new Prisma();
     * $prisma->transaction([
     *     function() use ($prisma) { $prisma->UserModel->create(['name' => 'John Doe']); },
     *     function() use ($prisma) { $prisma->OrderModel->create(['userId' => 1, 'product' => 'Book']); }
     * ]);
     */
    public function transaction($operations) {
        try {
            $this->pdo->beginTransaction();
            foreach ($operations as $operation) {
                if (is_callable($operation)) {
                    call_user_func($operation);
                }
            }
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
