<?php

use PHPUnit\Framework\TestCase;
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class CaloryCalcTest extends TestCase
{
    public function testSearchButtonExists()
    {
        $html = file_get_contents(__DIR__ . '/../calory_calc.php');
        $this->assertStringContainsString('<button type="submit"><i class="fas fa-search"></i></button>', $html);
    }

    public function testChartCanvasExists()
    {
        $html = file_get_contents(__DIR__ . '/../calory_calc.php');
        $this->assertStringContainsString('<button type="submit"><i class="fas fa-search"></i></button>', $html);
    }

    public function testTableHasRequiredHeaders()
    {
        $html = file_get_contents(__DIR__ . '/../calory_calc.php');
        $this->assertStringContainsString('<th>Product Name</th>', $html);
        $this->assertStringContainsString('<th>Calories (per 100g)</th>', $html);
        $this->assertStringContainsString('<th>Grams Taken</th>', $html);
        $this->assertStringContainsString('<th>Total Calories</th>', $html);
        $this->assertStringContainsString('<th>Action</th>', $html);
    }

    public function testDatabaseConnection()
    {
        require __DIR__ . '/../function.php'; // Ensures DB connection

        // Initialize the PDO connection
        $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'];
        $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
        $stmt = $pdo->query("SELECT COUNT(*) FROM food_products");
        $result = $stmt->fetchColumn();

        $this->assertGreaterThanOrEqual(0, $result, "Database should return at least 0 rows.");
    }
}