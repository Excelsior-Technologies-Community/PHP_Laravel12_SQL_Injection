<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDO;

class QueryVisualizerController extends Controller
{
    /**
     * Display the Live SQL Query AST & Prepared Statement Visualizer
     */
    public function index()
    {
        return view('visualizer.index');
    }

    /**
     * Parse and inspect the query mechanics under PDO
     */
    public function inspect(Request $request)
    {
        $input = trim($request->input('input', "' OR '1'='1"));

        // Vulnerable Raw SQL String
        $rawSql = "SELECT id, name, email, is_admin FROM users WHERE name LIKE '%$input%' OR email LIKE '%$input%'";

        // Safe Prepared Statement SQL & Parameter Array
        $preparedSql = "SELECT id, name, email, is_admin FROM users WHERE name LIKE ? OR email LIKE ?";
        $boundParameters = [
            [
                'index' => 1,
                'placeholder' => '?',
                'target_column' => 'users.name',
                'value' => "%{$input}%",
                'pdo_type' => 'PDO::PARAM_STR',
                'length' => strlen("%{$input}%"),
                'is_literal' => true,
            ],
            [
                'index' => 2,
                'placeholder' => '?',
                'target_column' => 'users.email',
                'value' => "%{$input}%",
                'pdo_type' => 'PDO::PARAM_STR',
                'length' => strlen("%{$input}%"),
                'is_literal' => true,
            ],
        ];

        // Tokenize Raw SQL to demonstrate syntax alteration
        $rawTokens = $this->tokenizeSql($rawSql);

        // Tokenize Prepared SQL
        $preparedTokens = $this->tokenizeSql($preparedSql);

        // Security Analysis
        $containsSqlKeywords = (bool) preg_match('/\b(OR|AND|UNION|SELECT|DROP|INSERT|DELETE|--|#)\b/i', $input);

        return response()->json([
            'success' => true,
            'input' => $input,
            'raw_sql' => $rawSql,
            'raw_tokens' => $rawTokens,
            'prepared_sql' => $preparedSql,
            'prepared_tokens' => $preparedTokens,
            'bound_parameters' => $boundParameters,
            'syntax_compromised' => $containsSqlKeywords,
            'explanation' => [
                'vulnerable_risk' => $containsSqlKeywords 
                    ? 'In the raw SQL statement, user input broke out of the string quotes and introduced new AST nodes (operators / keywords), altering query execution logic.'
                    : 'Currently safe string, but any single quote or SQL operator could alter the execution tree.',
                'prepared_protection' => 'Under PDO prepared statements, the database engine compiles and validates the SQL syntax tree BEFORE bound values are delivered. The database engine never interprets bound values as SQL commands, even if they contain quotes, UNION, or comments.'
            ]
        ]);
    }

    /**
     * Simple SQL Lexer to classify tokens for visual AST inspection
     */
    private function tokenizeSql(string $sql): array
    {
        $keywords = ['SELECT', 'FROM', 'WHERE', 'LIKE', 'OR', 'AND', 'UNION', 'ALL', 'DROP', 'TABLE', 'ORDER', 'BY', 'LIMIT', 'SET', 'INSERT', 'INTO', 'DELETE'];
        $words = preg_split('/(\s+|\,|\;|\(|\)|\?)/', $sql, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $tokens = [];

        foreach ($words as $word) {
            $trimmed = trim($word);
            if ($trimmed === '') continue;

            $upper = strtoupper($trimmed);
            if (in_array($upper, $keywords)) {
                $type = 'KEYWORD';
                $badge = 'badge-keyword';
            } elseif ($trimmed === '?' || str_starts_with($trimmed, ':')) {
                $type = 'PLACEHOLDER';
                $badge = 'badge-placeholder';
            } elseif (str_starts_with($trimmed, "'") || str_ends_with($trimmed, "'") || str_starts_with($trimmed, '"')) {
                $type = 'LITERAL';
                $badge = 'badge-literal';
            } elseif (in_array($trimmed, ['--', '#', '/*'])) {
                $type = 'COMMENT';
                $badge = 'badge-comment';
            } elseif (in_array($trimmed, ['=', '<', '>', '!='])) {
                $type = 'OPERATOR';
                $badge = 'badge-operator';
            } else {
                $type = 'IDENTIFIER';
                $badge = 'badge-identifier';
            }

            $tokens[] = [
                'token' => $trimmed,
                'type' => $type,
                'badge' => $badge,
            ];
        }

        return $tokens;
    }
}
