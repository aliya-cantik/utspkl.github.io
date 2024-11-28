<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuis Pengetahuan Umum</title>
    <style>
        /* Styling utama untuk formulir pengisian data */
        .container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            margin: 50px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.15);
            font-family: 'Arial', sans-serif;
        }

        h1 {
            font-size: 26px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 8px;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px 14px;
            font-size: 16px;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: border-color 0.3s, box-shadow 0.3s;
            outline: none;
        }

        input[type="text"]:focus {
            border-color: #4CAF50;
            box-shadow: 0px 4px 8px rgba(0, 255, 0, 0.1);
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px 16px;
            font-size: 16px;
            font-weight: bold;
            background-color: #4CAF50;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Styling untuk pesan kecil */
        .small-text {
            font-size: 13px;
            color: #777;
            text-align: center;
            margin-top: 10px;
        }

        /* Styling kuis */
        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin-bottom: 10px;
        }

        input[type="radio"] {
            margin-right: 10px;
        }

        .quiz-buttons button {
            margin: 5px;
            padding: 10px 15px;
            font-size: 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-previous {
            background-color: #f0ad4e;
            color: white;
        }

        .btn-next {
            background-color: #5bc0de;
            color: white;
        }

        .btn-submit {
            background-color: #d9534f;
            color: white;
        }

        .btn-restart {
            background-color: #0275d8;
            color: white;
            margin-top: 20px;
        }

        /* Gambar soal */
        .question-image {
            max-width: 100%;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <?php
    session_start();

    // Handle form submission to start quiz
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name']) && isset($_POST['nim'])) {
        $_SESSION['name'] = $_POST['name'];
        $_SESSION['nim'] = $_POST['nim'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Display the form if name and NIM are not set
    if (!isset($_SESSION['name']) || !isset($_SESSION['nim'])) {
        echo "<div class='container'>";
        echo "<h1>Formulir Pengisian Data</h1>";
        echo "<form method='POST'>";
        echo "<div class='form-group'>";
        echo "<label for='name'>Nama:</label>";
        echo "<input type='text' id='name' name='name' placeholder='Masukkan nama Anda' required>";
        echo "</div>";
        echo "<div class='form-group'>";
        echo "<label for='nim'>NIM:</label>";
        echo "<input type='text' id='nim' name='nim' placeholder='Masukkan NIM Anda' required>";
        echo "</div>";
        echo "<button type='submit'>Mulai Kuis</button>";
        echo "</form>";
        echo "<p class='small-text'>Pastikan data yang Anda masukkan sudah benar.</p>";
        echo "</div>";
        exit();
    }

    // Load quiz questions
    function load_questions($file_path) {
        $questions = [];
        if (file_exists($file_path)) {
            $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $question = [];
            foreach ($lines as $line) {
                if (strpos($line, "Question:") === 0) {
                    if (!empty($question)) $questions[] = $question;
                    $question = [
                        "question" => trim(substr($line, 9)),
                        "options" => [],
                        "answer" => "",
                        "explanation" => "",
                        "image" => "" // Add a placeholder for image
                    ];
                } elseif (strpos($line, "Options:") === 0) {
                    $question["options"] = array_map('trim', explode(',', substr($line, 8)));
                } elseif (strpos($line, "Answer:") === 0) {
                    $question["answer"] = trim(substr($line, 7));
                } elseif (strpos($line, "Explanation:") === 0) {
                    $question["explanation"] = trim(substr($line, 12));
                } elseif (strpos($line, "Image:") === 0) {
                    $question["image"] = trim(substr($line, 6));
                }
            }
            if (!empty($question)) $questions[] = $question;
        }
        return $questions;
    }

    // Initialize quiz
$file_path = 'aliya.txt';
if (!isset($_SESSION['questions'])) {
    $_SESSION['questions'] = load_questions($file_path);
    shuffle($_SESSION['questions']); // Shuffle questions initially

    // Limit the questions to 5
    $_SESSION['questions'] = array_slice($_SESSION['questions'], 0, 5);

    foreach ($_SESSION['questions'] as &$q) {
        shuffle($q['options']); // Shuffle options for each question
    }
    $_SESSION['currentQuestion'] = 0;
    $_SESSION['answers'] = [];
}


    // Handle quiz navigation and actions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['answer'])) {
            $_SESSION['answers'][$_SESSION['currentQuestion']] = $_POST['answer'];
        }

        if (isset($_POST['next'])) $_SESSION['currentQuestion']++;
        elseif (isset($_POST['prev'])) $_SESSION['currentQuestion']--;
        elseif (isset($_POST['shuffle'])) shuffle($_SESSION['questions']);
        elseif (isset($_POST['submit'])) {
            echo "<div class='container'>";
            echo "<h1>Hasil Kuis</h1>";
            $score = 0;
            foreach ($_SESSION['questions'] as $index => $q) {
                $userAnswer = $_SESSION['answers'][$index] ?? 'Tidak Dijawab';
                $isCorrect = $userAnswer == $q['answer'];
                if ($isCorrect) $score++;
                echo "<p><strong>Soal " . ($index + 1) . ":</strong> " . $q['question'] . "</p>";
                if ($q['image']) echo "<img src='" . $q['image'] . "' class='question-image' />";
                echo "<p>Jawaban Anda: $userAnswer</p>";
                echo "<p>Jawaban Benar: " . $q['answer'] . "</p>";
                echo "<p>Penjelasan: " . $q['explanation'] . "</p><hr>";
            }
            echo "<p><strong>Skor Anda:</strong> $score dari " . count($_SESSION['questions']) . "</p>";
            echo "<form method='POST'><button type='submit' class='btn-restart'>Mulai Ulang Kuis</button></form>";
            session_destroy();
            echo "</div>";
            exit();
        }
    }

    // Display the current question
    $currentQuestion = $_SESSION['currentQuestion'];
    $question = $_SESSION['questions'][$currentQuestion];
    echo "<div class='container'>";
    echo "<h1>Kuis Pengetahuan Umum</h1>";
    echo "<p><strong>Soal " . ($currentQuestion + 1) . ":</strong> " . $question['question'] . "</p>";
    if ($question['image']) {
        echo "<img src='" . $question['image'] . "' class='question-image' />";
    }
    echo "<form method='POST'>";
    echo "<ul>";
    foreach ($question['options'] as $option) {
        $checked = isset($_SESSION['answers'][$currentQuestion]) && $_SESSION['answers'][$currentQuestion] == $option ? 'checked' : '';
        echo "<li><label><input type='radio' name='answer' value='$option' $checked> $option</label></li>";
    }
    echo "</ul>";
    echo "<div class='quiz-buttons'>";
    if ($currentQuestion > 0) echo "<button type='submit' name='prev' class='btn-previous'>Sebelumnya</button>";
    if ($currentQuestion < count($_SESSION['questions']) - 1) echo "<button type='submit' name='next' class='btn-next'>Selanjutnya</button>";
    echo "<button type='submit' name='shuffle' class='btn-next'>Acak Soal</button>";
    if ($currentQuestion == count($_SESSION['questions']) - 1) echo "<button type='submit' name='submit' class='btn-submit'>Selesai</button>";
    echo "</div>";
    echo "</form>";
    echo "</div>";
    ?>
</body>
</html>