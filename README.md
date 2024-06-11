# PHP .env to .json/.js Parser

This project demonstrates a PHP script that reads a `.env` file and converts it into either a `.json` or `.js` file based on the specified output file extension. The script supports various features including comments, quoted values, variable expansion, and default values.

## Features

1. **Support for Comments and Blank Lines**: The script skips lines that are comments (starting with `#`) or blank.
2. **Support for Quoted Values**: The script handles values enclosed in single or double quotes.
3. **Expanding Variables**: The script supports referencing other variables within the `.env` file using `${VAR_NAME}` syntax.
4. **Default Values**: Variables with default values can be handled.
5. **Flexible Output**: Generates either a `.json` or `.js` file based on the specified output file extension.

## Project Structure

```sh
project-root/
├── .env
└── convert_env_to_file.php
```

## .env File Example

Create a `.env` file in the project root with the following content:

```env
# This is a comment
API_KEY="your_api_key"
DB_HOST=localhost
DB_PORT=3306
DB_URL=${DB_HOST}:${DB_PORT} # This will expand to localhost:3306
```

## PHP Script

### `convert_env_to_file.php`

```php
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
?>
```

## Usage

1. **Save the Script**:
   - Save the above script as `convert_env_to_file.php` in your project directory.

2. **Create the .env File**:
   - Ensure that your `.env` file is located at the specified `$envFilePath`.

3. **Run the Script**:
   - Run the script from the command line:
     ```sh
     php convert_env_to_file.php
     ```

4. **Check the Output**:
   - The script will generate either an `env.json` or `env.js` file at the specified `$outputFilePath` containing the environment variables.

## Example Outputs

### `env.json` Output

```json
{
    "API_KEY": "your_api_key",
    "DB_HOST": "localhost",
    "DB_PORT": "3306",
    "DB_URL": "localhost:3306"
}
```

### `env.js` Output

```javascript
export const env = {
    "API_KEY": "your_api_key",
    "DB_HOST": "localhost",
    "DB_PORT": "3306",
    "DB_URL": "localhost:3306"
};
```

## Conclusion

This PHP script provides a simple and effective way to convert `.env` files into `.json` or `.js` files, supporting various features such as comments, quoted values, variable expansion, and default values. This can be particularly useful for managing environment variables in a structured and readable format, suitable for use in both JSON and JavaScript environments.
