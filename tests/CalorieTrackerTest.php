<?php
use PHPUnit\Framework\TestCase;
require_once 'function.php';
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class CalorieTrackerTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function testAddCalorieEntry()
    {
        // Generate a unique user ID
        $userId = uniqid();

        // Insert a new member into the members table
        $stmt = $this->pdo->prepare("INSERT INTO members (user, pass, email) VALUES (:user, :pass, :email)");
        $stmt->execute([
            ':user' => $userId,
            ':pass' => 'test_password',
            ':email' => 'test@example.com'
        ]);

        // Insert a new calorie entry into the food_products table
        $calorieEntry = [
            'product_name' => 'Test Product',
            'calories' => 100,
            'grams_taken' => 50,
            'date' => date('Y-m-d'),
            'user' => $userId
        ];
        $stmt = $this->pdo->prepare("INSERT INTO food_products (product_name, calories, grams_taken, date, user) VALUES (:product_name, :calories, :grams_taken, :date, :user)");
        $stmt->execute([
            ':product_name' => $calorieEntry['product_name'],
            ':calories' => $calorieEntry['calories'],
            ':grams_taken' => $calorieEntry['grams_taken'],
            ':date' => $calorieEntry['date'],
            ':user' => $calorieEntry['user']
        ]);

        // Verify that the calorie entry was inserted successfully
        $stmt = $this->pdo->prepare("SELECT * FROM food_products WHERE user = :user AND product_name = :product_name");
        $stmt->execute([
            ':user' => $userId,
            ':product_name' => $calorieEntry['product_name']
        ]);
        $result = $stmt->fetch();

        $this->assertNotNull($result);
        $this->assertEquals($calorieEntry['product_name'], $result['product_name']);
        $this->assertEquals($calorieEntry['calories'], $result['calories']);
        $this->assertEquals($calorieEntry['grams_taken'], $result['grams_taken']);
        $this->assertEquals($calorieEntry['date'], $result['date']);
        $this->assertEquals($calorieEntry['user'], $result['user']);
    }

    public function testGetCalorieEntries()
    {
        $user = 'testUser ';
        $entries = getCalorieEntries($this->pdo, $user);
        $this->assertIsArray($entries);
    }
}