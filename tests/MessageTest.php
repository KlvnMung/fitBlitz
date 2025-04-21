<?php
use PHPUnit\Framework\TestCase;
require_once 'function.php';
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class MessageTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function testInsertMessage()
    {
        $user = 'testUser ';
        $view = 'testRecipient';
        $text = "Hello, this is a test message!";

        $result = insertMessage($this->pdo, $user, $view, $text);
        $this->assertTrue($result);
    }

    public function testDeleteMessage()
    {
        $user = 'testUser ';
        $messageId = 1; // Assume message ID 1 exists

        $result = deleteMessage($this->pdo, $messageId, $user);
        $this->assertTrue($result);
    }
}
?>