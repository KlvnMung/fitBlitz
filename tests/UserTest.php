<?php
use PHPUnit\Framework\TestCase;
require_once 'function.php';
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class UserTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function testSanitizeString()
{
    $input = "alert('test');";
    $expected = "alert(&#039;test&#039;);";
    $actual = sanitizeString($input);
    $this->assertEquals($expected, $actual);
}


public function testLoginUser        ()
{
    $user = uniqid();
    $pass = 'test_password';

    // Initialize the PDO connection
    $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'];
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);

    // Insert a new user into the members table
    $stmt = $pdo->prepare("INSERT INTO members (user, pass, email) VALUES (:user, :pass, :email)");
    $stmt->execute([
        ':user' => $user,
        ':pass' => password_hash($pass, PASSWORD_DEFAULT),
        ':email' => 'test@example.com'
    ]);

    // Try to login the user
    $result = loginUser($pdo, $user, $pass);

    // Check if the login was successful
    $this->assertTrue($result);
}
}
?>