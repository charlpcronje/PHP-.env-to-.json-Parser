<?php
/**
 * This script reads a .env file and converts it to a .json file.
 * Adjust the $envFilePath and $jsonFilePath to point to your directories.
 */

// Configuration: Path to the .env file and the output .json file
$envFilePath = __DIR__ . '/.env'; // Replace with the actual path to your .env file
$jsonFilePath = __DIR__ . '/env.json'; // Replace with the desired path for the output .json file

/**
 * Function to parse the .env file.
 *
 * @param string $filePath The path to the .env file.
 * @return array An associative array of environment variables.
 */
function parseEnvFile($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("File not found: $filePath");
    }

    $envVars = [];
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments and blank lines
        if (strpos(trim($line), '#') === 0 || trim($line) === '') {
            continue;
        }

        // Split line into name and value
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove surrounding quotes from the value
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
            $value = $matches[1];
        }

        // Expand variables
        $value = preg_replace_callback('/\${([^}]+)}/', function($matches) use ($envVars) {
            return isset($envVars[$matches[1]]) ? $envVars[$matches[1]] : '';
        }, $value);

        $envVars[$name] = $value;
    }

    return $envVars;
}

/**
 * Function to save the environment variables to a .json file.
 *
 * @param array $envVars The associative array of environment variables.
 * @param string $filePath The path to the output .json file.
 */
function saveEnvToJson($envVars, $filePath) {
    $jsonContent = json_encode($envVars, JSON_PRETTY_PRINT);
    if (file_put_contents($filePath, $jsonContent) === false) {
        throw new Exception("Failed to write to file: $filePath");
    }
}

try {
    // Parse the .env file
    $envVars = parseEnvFile($envFilePath);

    // Save the environment variables to a .json file
    saveEnvToJson($envVars, $jsonFilePath);

    echo "Environment variables have been successfully converted to JSON and saved to $jsonFilePath\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
