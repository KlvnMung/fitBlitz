<?php
require_once __DIR__.'/vendor/autoload.php'; // Autoload files using Composer autoload

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
// information.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'header.php';
if (!$loggedin) die("Please log in to access this page.");

//  News API key from newsapi.org (free API key) 
$newsApiKey = $_ENV['NEWS_API_KEY'];

// Function to fetch health articles
function fetchHealthArticles($apiKey, $query = null) {
    $baseUrl = 'https://newsapi.org/v2/top-headlines?';
    
    $params = array(
        'category' => 'health',
        'language' => 'en',
        'pageSize' => 12,
        'apiKey' => $apiKey
    );
    
    if ($query) {
        $params['q'] = $query;
    }
    
    $url = $baseUrl . http_build_query($params);
    
    $options = [
        'http' => [
            'header' => [
                'User-Agent: FitBlitz/1.0',
 
            ],
            'method' => 'GET',
            'ignore_errors' => true
        ]
    ];
    
    $context = stream_context_create($options);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        return ['error' => 'Failed to get contents', 'url' => $url];
    }
    
    $statusLine = $http_response_header[0];
    preg_match('{HTTP/\S*\s(\d{3})}', $statusLine, $match);
    $status = $match[1];
    
    if ($status !== '200') {
        return ['error' => "HTTP request failed. Status: $status", 'response' => $response, 'url' => $url];
    }
    
    return json_decode($response, true);
}

$result = fetchHealthArticles($newsApiKey, isset($_GET['search']) ? $_GET['search'] : null);

// Array of health and fitness fun facts
$funFacts = [
    "Laughing for 10-15 minutes can burn up to 40 calories!",
    "The human body contains enough iron to make a 3-inch nail.",
    "Your heart beats about 100,000 times every day!",
    "Drinking water can boost your metabolism by up to 30%.",
    "A single step uses up to 200 muscles in your body.",
    "Your bones are stronger than steel, pound for pound.",
    "20 minutes of physical activity can boost your memory.",
    "The average person walks the equivalent of 3 times around the world in a lifetime.",
    "Muscle is three times more efficient at burning calories than fat.",
    "Your body has over 650 muscles!",
    "We mzee enda ukapige tizi"
];

// Get random fun fact
$randomFact = $funFacts[array_rand($funFacts)];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Information</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="info-container">
        <!-- Fun Fact Section -->
        <div class="fun-fact-section">
            <h2>💡 Did You Know?</h2>
            <div class="fun-fact-box">
                <?php echo htmlspecialchars($randomFact); ?>
            </div>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <h2>Search Health & Fitness Articles</h2>
            <form method="GET" action="information.php" class="search-form">
                <input type="text" name="search" placeholder="Search health topics..." 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>

        <!-- Articles Section -->
        <div class="articles-section">
            <?php
            if (isset($result['error'])) {
                echo "<div class='error-message'>";
                echo "Error: " . htmlspecialchars($result['error']) . "<br>";
                if (isset($result['response'])) {
                    echo "Response: " . htmlspecialchars($result['response']) . "<br>";
                }
                echo "URL: " . htmlspecialchars($result['url']);
                echo "</div>";
            } elseif (isset($result['articles']) && !empty($result['articles'])) {
                echo '<div class="articles-grid">';
                foreach ($result['articles'] as $article) {
                    echo '<div class="article-card">';
                    if (isset($article['urlToImage'])) {
                        echo '<img src="' . htmlspecialchars($article['urlToImage']) . '" alt="Article image">';
                    }
                    echo '<div class="article-content">';
                    echo '<h3>' . htmlspecialchars($article['title']) . '</h3>';
                    echo '<p>' . htmlspecialchars(substr($article['description'] ?? '', 0, 150)) . '...</p>';
                    echo '<div class="article-meta">';
                    echo '<span>Source: ' . htmlspecialchars($article['source']['name']) . '</span>';
                    echo '<span>' . date('M d, Y', strtotime($article['publishedAt'])) . '</span>';
                    echo '</div>';
                    echo '<a href="' . htmlspecialchars($article['url']) . '" target="_blank" class="read-more">Read More</a>';
                    echo '</div></div>';
                }
                echo '</div>';
            } else {
                echo '<p class="no-results">No articles found. Please try a different search term.</p>';
            }
            ?>
        </div>
    </div>
    <script>
        // JavaScript for toggling dark mode (if not already included)
const toggleButton = document.getElementById('theme-toggle');  // assuming you have an element to toggle theme
const bodyElement = document.body;

toggleButton.addEventListener('click', () => {
    bodyElement.classList.toggle('dark-mode');
});

    </script>

<?php
require_once 'footer.php';
?>
</body>
</html>
