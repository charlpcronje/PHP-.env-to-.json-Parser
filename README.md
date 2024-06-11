<?php
/**
 * This script reads a .env file and converts it to either a .json or .js file based on the specified output file extension.
 * Adjust the $envFilePath and $outputFilePath to point to your directories.
 */

// Configuration: Path to the .env file and the output file
$envFilePath = __DIR__ . '/.env'; // Replace with the actual path to your .env file
$outputFilePath = __DIR__ . '/env.json'; // Replace with the desired path for the output file (either .json or .js)

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
 * Function to save the environment variables to a .json or .js file.
 *
 * @param array $envVars The associative array of environment variables.
 * @param string $filePath The path to the output file.
 */
function saveEnvToFile($envVars, $filePath) {
    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

    if ($fileExtension === 'json') {
        $content = json_encode($envVars, JSON_PRETTY_PRINT);
    } elseif ($fileExtension === 'js') {
        $jsonContent = json_encode($envVars, JSON_PRETTY_PRINT);
        $content = "export const env = $jsonContent;";
    } else {
        throw new Exception("Unsupported file extension: $fileExtension");
    }

    if (file_put_contents($filePath, $content) === false) {
        throw new Exception("Failed to write to file: $filePath");
    }
}

try {
    // Parse the .env file
    $envVars = parseEnvFile($envFilePath);

    // Save the environment variables to the output file
    saveEnvToFile($envVars, $outputFilePath);

    echo "Environment variables have been successfully converted and saved to $outputFilePath\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
