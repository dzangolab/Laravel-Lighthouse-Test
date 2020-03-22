<?php

namespace Knevelina\LighthouseTest\Schema;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

/**
 * A constructed GraphQL query.
 * 
 * Do not use this to construct queries in production, as the code to format the
 * query is very insecure and intended for use in test environments.
 */
class GraphQLQuery
{
    /**
     * The type of query; either "query" or "mutation".
     *
     * @var string
     */
    protected $type;

    /**
     * The field that is being queried or mutated.
     *
     * @var string
     */
    protected $field;

    /**
     * The arguments given to the query or mutation.
     *
     * @var array
     */
    protected $arguments;

    /**
     * The selection made of the returned data.
     *
     * @var array
     */
    protected $selection;

    /**
     * The mapping from variables to uploaded files.
     *
     * @var array
     */
    private $map;

    /**
     * The uploaded files.
     *
     * @var array
     */
    private $files;

    /**
     * The declared variables in the query.
     *
     * @var array
     */
    private $variables;

    /**
     * Construct a new GraphQL query. Extra variables can be given when
     * formatting the query.
     *
     * @param string $type
     * @param string $field
     * @param array $arguments
     * @param array $selection
     */
    public function __construct(string $type, string $field, array $arguments, array $selection)
    {
        if ($type !== 'query' && $type !== 'mutation') {
            throw new InvalidArgumentException('Type must be either query or mutation.');
        }

        $this->type = $type;

        $this->field = $field;

        $this->arguments = $arguments;

        $this->variables = [];

        array_walk($this->arguments, [$this, 'findVariables']);

        $this->selection = $selection;
    }

    protected function findVariables(&$value, string $key)
    {
        if (is_array($value)) {
            array_walk($value, [$this, 'findVariables']);
        }

        if ($value instanceof Variable) {
            $this->variables[] = $value;
        }
    }

    /**
     * Get the type of the query; either "type" or "mutation".
     * 
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the field that is being queried or mutated.
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get the arguments given to the query or mutation.
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get the selection made of the returned data.
     *
     * @return array
     */
    public function getSelection(): array
    {
        return $this->selection;
    }

    /**
     * Format the query to a valid GraphQL query.
     *
     * @return string
     */
    protected function formatQuery(): string
    {
        return sprintf(
            "%s%s { %s }",

            // Start with the type.
            $this->type,

            // If type declarations are required for variables, add them.
            // Otherwise, add nothing.
            ((count($this->variables) > 0)
                ? sprintf("(%s)", $this->formatVariables($this->variables))
                : ''),

            // Now add the query itself.
            implode('', [
                // Add the field being queried.
                $this->field,


                // If the field requires arguments, add them. Otherwise, add
                // nothing.
                ((count($this->arguments) > 0)
                    ? sprintf('(%s)', $this->formatArguments($this->arguments))
                    : ''),
                
                // Finally, add the selection set.
                $this->formatSelectionSet($this->selection)
            ])
        );
    }

    /**
     * Format variable declarations for a query or mutation.
     * 
     * @param array $variables
     * @return string
     */
    protected function formatVariables(array $variables): string
    {
        return implode(', ', array_map(function (Variable $var) {
            return sprintf('$%s: %s', $var->getName(), $var->getType());
        }, $variables));
    }

    /**
     * Format arguments for a query or mutation.
     *
     * @param array $arguments
     * @return string
     */
    protected function formatArguments(array $arguments): string
    {
        array_walk($arguments, function (&$value, $key) {
            $value = sprintf('%s: %s', $key, $this->formatValue($value));
        });

        return implode(', ', $arguments);
    }

    /**
     * Format a selection set for a query or mutation.
     *
     * @param array $selection
     * @return string
     */
    protected function formatSelectionSet(array $selection): string
    {
        if (count($selection) === 0) {
            return '';
        }

        array_walk($selection, function (&$value, $key) {
            if (is_array($value)) {
                $value = sprintf('%s %s', $key, $this->formatSelectionSet($value));
            }
        });

        return sprintf('{ %s }', implode("\n", $selection));
    }

    /**
     * Test whether an array is associative, or sequential.
     *
     * @param array $array
     * @return boolean
     */
    public static function isAssociativeArray(array $array): bool
    {
        if ($array === []) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Format a (scalar) value according to the GraphQL standard.
     *
     * @param mixed $value
     * @return string
     */
    protected function formatValue($value): string
    {
        if (is_array($value) ) {
            if (static::isAssociativeArray($value)) {
                return sprintf('{ %s }', $this->formatArguments($value));
            }

            return sprintf('[ %s ]', implode(', ', array_map([$this, 'formatValue'], $value)));
        }

        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            $value = str_replace('\\', '\\\\', $value);
            $value = str_replace('"', '\"', $value);
            return '"'.$value.'"';
        }

        return (string) $value;
    }

    /**
     * Look for uploaded files in variables.
     *
     * @param array|UploadedFile $value
     * @param string $key
     * @param string $prefix
     * @return void
     */
    protected function findFiles(&$value, string $key, string $prefix)
    {
        if (is_array($value)) {
            array_walk($value, [$this, 'findFiles'], $prefix.'.'.$key);
        }

        if ($value instanceof UploadedFile) {
            $id = array_push($this->files, $value) - 1;
            $this->map[substr($prefix.'.'.$key, 1)] = $id;
            $value = null;
        }
    }

    /**
     * Get the query as an associative array of POST data.
     *
     * @param array $variables
     * @return array
     */
    public function getQuery(array $variables = []): array
    {
        $this->map = [];

        $this->files = [];

        array_walk($variables, [$this, 'findFiles'], '');

        $query = [
            'query' => $this->formatQuery(),
            'variables' => $variables
        ];

        if (count($this->files)) {
            return array_merge(
                [
                    'operations' => $query,
                    'map' => $this->map
                ],
                $this->files
            );
        }

        return $query;
    }
}