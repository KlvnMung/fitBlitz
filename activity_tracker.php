<?php
include_once "header.php";
include_once "function.php"; // Include functions from the functions.php file

// Load API Key from .env file
$dotenv = parse_ini_file('.env');
$apiKey = $dotenv['RAPID_API_KEY'];
$apiUrl = 'https://exercisedb.p.rapidapi.com/exercises?limit=50&offset=0';

// Fetch API Data
$exercises = fetchExercisesFromAPI($apiUrl, $apiKey);

$groupedExercises = categorizeExercises($exercises);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Tracker</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Activity Tracker</h1>

    <div>
        <h2>Machine Exercises</h2>
        <?php foreach ($groupedExercises['machine'] as $muscle => $exercises): ?>
            <button onclick="toggleExercises('machine', '<?= htmlspecialchars($muscle) ?>')">
                <?= htmlspecialchars($muscle) ?> Exercises
            </button>
            <?php foreach ($exercises as $index => $exercise): ?>
                <?php $caloriesPerSecond = $exercise['calories_burned_per_sec'] ?? 0.1; ?>
                <div class="exercise-item" data-type="machine" data-muscle="<?= htmlspecialchars($muscle) ?>" style="display: none;">
                    <h5><?= htmlspecialchars($exercise['name']) ?></h5>
                    <img src="<?= htmlspecialchars($exercise['gifUrl']) ?>" alt="<?= htmlspecialchars($exercise['name']) ?>" class="exercise-video" loading="lazy">
                    <p>Equipment: <?= htmlspecialchars($exercise['equipment']) ?></p>
                    <button onclick="startTimer(<?= $index ?>, <?= $caloriesPerSecond ?>)">Start Timer</button>
                    <button onclick="stopTimer(<?= $index ?>)">Pause Timer</button>
                    <div id="timer-<?= $index ?>">Time: 0s</div>
                    <button onclick="finishExercise(<?= $index ?>)">Finish Exercise</button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <div>
        <h2>Non-Machine Exercises</h2>
        <?php foreach ($groupedExercises['non-machine'] as $muscle => $exercises): ?>
            <button onclick="toggleExercises('non-machine', '<?= htmlspecialchars($muscle) ?>')">
                <?= htmlspecialchars($muscle) ?> Exercises
            </button>
            <?php foreach ($exercises as $index => $exercise): ?>
                <?php $caloriesPerSecond = $exercise['calories_burned_per_sec'] ?? 0.1; ?>
                <div class="exercise-item" data-type="non-machine" data-muscle="<?= htmlspecialchars($muscle) ?>" style="display: none;">
                    <h5><?= htmlspecialchars($exercise['name']) ?></h5>
                    <img src="<?= htmlspecialchars($exercise['gifUrl']) ?>" alt="<?= htmlspecialchars($exercise['name']) ?>" class="exercise-video" loading="lazy">
                    <p>Equipment: <?= htmlspecialchars($exercise['equipment']) ?></p>
                    <button onclick="startTimer(<?= $index ?>, <?= $caloriesPerSecond ?>)">Start Timer</button>
                    <button onclick="stopTimer(<?= $index ?>)">Pause Timer</button>
                    <div id="timer-<?= $index ?>">Time: 0s</div>
                    <button onclick="finishExercise(<?= $index ?>)">Finish Exercise</button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <button onclick="calculateTotalCalories()">Show Total Calories Burned</button>

    <script>
        const timers = {};
        const caloriesBurnedData = {};
        const restDuration = 30; 
        const exerciseDuration = 60; 

        function toggleExercises(type, muscle) {
            const exercises = document.querySelectorAll(`.exercise-item[data-type='${type}'][data-muscle='${muscle}']`);
            exercises.forEach(exercise => {
                exercise.style.display = exercise.style.display === 'block' ? 'none' : 'block';
            });
        }

        function startTimer(exerciseId, caloriesPerSecond = 0.1) {
            if (timers[exerciseId]) {
                clearInterval(timers[exerciseId].interval);
            }

            let timerDisplay = document.getElementById(`timer-${exerciseId}`);
            let restTime = restDuration;
            let exerciseTime = exerciseDuration;

            timers[exerciseId] = {
                caloriesPerSecond,
                totalCalories: 0,
                interval: setInterval(function() {
                    if (exerciseTime > 0) {
                        timerDisplay.textContent = `Exercise Time: ${exerciseTime}s`;
                        timers[exerciseId].totalCalories += caloriesPerSecond;
                        exerciseTime--;
                    } else if (restTime > 0) {
                        timerDisplay.textContent = `Rest Time: ${restTime}s`;
                        restTime--;
                    } else {
                        clearInterval(timers[exerciseId].interval);
                        timerDisplay.textContent = "Exercise and rest completed!";
                    }
                }, 1000)
            };
        }

        function stopTimer(exerciseId) {
            if (timers[exerciseId]) {
                clearInterval(timers[exerciseId].interval);
                document.getElementById(`timer-${exerciseId}`).textContent = "Timer paused.";
            }
        }

        function finishExercise(exerciseId) {
            if (timers[exerciseId]) {
                const exerciseCalories = timers[exerciseId].totalCalories;
                caloriesBurnedData[exerciseId] = (caloriesBurnedData[exerciseId] || 0) + exerciseCalories;
                alert(`Calories burned for this exercise: ${Math.round(exerciseCalories)}`);
                clearInterval(timers[exerciseId].interval);
                document.getElementById(`timer-${exerciseId}`).textContent = "Exercise finished!";
            }
        }

        function calculateTotalCalories() {
            let totalCalories = 0;
            for (let id in caloriesBurnedData) {
                totalCalories += caloriesBurnedData[id];
            }
            alert(`Total Calories Burned: ${Math.round(totalCalories)}`);
        }
    </script>
</body>
</html>
