<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 1. Початковий масив рослин
$plants = [
    [
        'name' => 'Монстера Деліціоза',
        'wateringIntervalDays' => 7,
        'daysSinceWatered' => 8
    ],
    [
        'name' => 'Фікус Еластіка',
        'wateringIntervalDays' => 5,
        'daysSinceWatered' => 3
    ],
    [
        'name' => 'Заміокулькас',
        'wateringIntervalDays' => 14,
        'daysSinceWatered' => 14
    ],
    [
        'name' => 'Сукулент Ечеверія',
        'wateringIntervalDays' => 10,
        'daysSinceWatered' => 2
    ],
    [
        'name' => 'Спатіфілум',
        'wateringIntervalDays' => 4,
        'daysSinceWatered' => 5
    ],
    [
        'name' => 'Сансевієрія',
        'wateringIntervalDays' => 12,
        'daysSinceWatered' => 6
    ],
];

// 2. Змінні для обробки форми
$errors = [];
$successMessage = '';
$name = '';
$wateringIntervalDays = '';
$lastWatered = '';

// 3. Серверна обробка POST-запиту
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $wateringIntervalDays = $_POST['wateringIntervalDays'] ?? '';
    $lastWatered = $_POST['lastWatered'] ?? '';

    // Валідація назви
    if ($name === '') {
        $errors['name'] = 'Назва рослини є обов\'язковою.';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Назва повинна містити як мінімум 2 символи.';
    }

    // Валідація інтервалу поливу
    if ($wateringIntervalDays === '') {
        $errors['wateringIntervalDays'] = 'Вкажіть інтервал поливу.';
    } elseif (!filter_var($wateringIntervalDays, FILTER_VALIDATE_INT) || (int)$wateringIntervalDays <= 0) {
        $errors['wateringIntervalDays'] = 'Інтервал має бути цілим додатним числом.';
    }

    // Валідація дати останнього поливу
    if ($lastWatered === '') {
        $errors['lastWatered'] = 'Вкажіть дату останнього поливу.';
    } else {
        $today = date('Y-m-d');
        if ($lastWatered > $today) {
            $errors['lastWatered'] = 'Дата поливу не може бути в майбутньому.';
        }
    }

    // Якщо помилок немає — додаємо нову рослину на початок масиву
    if (empty($errors)) {
        $lastWateredDate = new DateTime($lastWatered);
        $todayDate = new DateTime();
        $daysSinceWatered = (int)$todayDate->diff($lastWateredDate)->format('%a');

        array_unshift($plants, [
            'name' => $name,
            'wateringIntervalDays' => (int)$wateringIntervalDays,
            'daysSinceWatered' => $daysSinceWatered
        ]);

        $successMessage = "Рослину <strong>" . htmlspecialchars($name) . "</strong> успішно додано до каталогу!";
        
        // Очищаємо поля
        $name = '';
        $wateringIntervalDays = '';
        $lastWatered = '';
    }
}

// 4. Допоміжна функція форматування
function formatPlant(array $plant): string 
{
    return "<strong>{$plant['name']}</strong> • інтервал поливу: кожні {$plant['wateringIntervalDays']} дн.";
}

// 5. Обчислення агрегатного показника
$needsWaterCount = 0;
foreach ($plants as $plant) {
    if ($plant['daysSinceWatered'] >= $plant['wateringIntervalDays']) {
        $needsWaterCount++;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FloraCare — Догляд за рослинами</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="app-container">
        <!-- Шапка -->
        <header class="header">
            <div class="header-icon">
                <img src="alocasia.png" alt="FloraCare" class="header-icon-img">
            </div>
            <div>
                <h1>FloraCare</h1>
                <p class="subtitle">Каталог догляду за домашніми рослинами</p>
            </div>
        </header>

        <!-- Повідомлення про успішне додавання -->
        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?= $successMessage ?>
            </div>
            <script>
                // Очищаємо localStorage, якщо форму успішно оброблено сервером
                localStorage.removeItem('floracare_plant_draft');
            </script>
        <?php endif; ?>

        <!-- БЛОК 1: Форма додавання рослини -->
        <div class="form-card" style="margin-bottom: 32px;">
            <h2 class="form-title">Форма догляду за рослиною</h2>

            <!-- Виправлено action="form.php" -->
            <form action="form.php" method="post" id="plantForm" novalidate>
                
                <div class="form-group">
                    <label for="name">Назва рослини <span class="req">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($name) ?>" 
                        minlength="2"
                        required
                        placeholder="напр. Монстера Деліціоза"
                    >
                    <small class="error-text" id="error-name"><?= $errors['name'] ?? '' ?></small>
                </div>

                <div class="form-group">
                    <label for="wateringIntervalDays">Інтервал поливу (днів) <span class="req">*</span></label>
                    <input 
                        type="number" 
                        id="wateringIntervalDays" 
                        name="wateringIntervalDays" 
                        class="form-control <?= isset($errors['wateringIntervalDays']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($wateringIntervalDays) ?>" 
                        min="1" 
                        required
                        placeholder="7"
                    >
                    <small class="error-text" id="error-interval"><?= $errors['wateringIntervalDays'] ?? '' ?></small>
                </div>

                <div class="form-group">
                    <label for="lastWatered">Дата останнього поливу <span class="req">*</span></label>
                    <input 
                        type="date" 
                        id="lastWatered" 
                        name="lastWatered" 
                        class="form-control <?= isset($errors['lastWatered']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($lastWatered) ?>" 
                        max="<?= date('Y-m-d') ?>" 
                        required
                    >
                    <small class="error-text" id="error-date"><?= $errors['lastWatered'] ?? '' ?></small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Зберегти рослину</button>
                    <button type="button" id="clearDraftBtn" class="btn-secondary">Очистити чернетку</button>
                </div>

            </form>
        </div>

        <!-- БЛОК 2: Підсумок -->
        <div class="summary-card">
            <div class="summary-info">
                <span class="summary-label">Сьогодні потребують поливу</span>
                <div class="summary-value">
                    <span><?= $needsWaterCount ?></span>
                    <small>із <?= count($plants) ?> рослин</small>
                </div>
            </div>
            <div class="summary-status-icon">
                <?= $needsWaterCount > 0 ? '<img src="watering-can.png" alt="Полив" class="summary-icon-img">' : '✨' ?>
            </div>
        </div>

        <!-- БЛОК 3: Сітка картки рослин -->
        <div class="plant-grid">
            <?php foreach ($plants as $plant): ?>
                <?php 
                    $needsWater = $plant['daysSinceWatered'] >= $plant['wateringIntervalDays'];
                    $statusText = $needsWater ? 'Потребує поливу' : 'Зволожена';
                    $badgeClass = $needsWater ? 'badge-alert' : 'badge-ok';
                    $cardClass = $needsWater ? 'card-alert' : '';
                ?>
                <div class="plant-card <?= $cardClass ?>">
                    <div class="plant-card-header">
                        <div class="plant-avatar">
                            <img src="tropical-leaves.png" alt="FloraCare" class="plant-avatar-img">
                        </div>
                        <span class="badge <?= $badgeClass ?>">
                            <?= $statusText ?>
                        </span>
                    </div>

                    <div class="plant-card-body">
                        <h3 class="plant-name"><?= htmlspecialchars($plant['name']) ?></h3>
                        <p class="plant-desc"><?= formatPlant($plant) ?></p>
                    </div>

                    <div class="plant-card-footer">
                        <div class="metric">
                            <span class="metric-label">Минуло днів</span>
                            <span class="metric-value"><?= $plant['daysSinceWatered'] ?></span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Норма (днів)</span>
                            <span class="metric-value"><?= $plant['wateringIntervalDays'] ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- JS: Валідація та localStorage -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('plantForm');
            const nameInput = document.getElementById('name');
            const intervalInput = document.getElementById('wateringIntervalDays');
            const dateInput = document.getElementById('lastWatered');
            const clearBtn = document.getElementById('clearDraftBtn');

            const STORAGE_KEY = 'floracare_plant_draft';

            // Відновлення чернетки (тільки якщо в полях порожньо від PHP)
            const savedDraft = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
            if (!nameInput.value && savedDraft.name) nameInput.value = savedDraft.name;
            if (!intervalInput.value && savedDraft.interval) intervalInput.value = savedDraft.interval;
            if (!dateInput.value && savedDraft.date) dateInput.value = savedDraft.date;

            // Збереження чернетки
            function saveDraft() {
                const draft = {
                    name: nameInput.value,
                    interval: intervalInput.value,
                    date: dateInput.value
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
            }

            [nameInput, intervalInput, dateInput].forEach(input => {
                input.addEventListener('input', saveDraft);
            });

            // Очищення чернетки кнопкою
            clearBtn.addEventListener('click', () => {
                localStorage.removeItem(STORAGE_KEY);
                nameInput.value = '';
                intervalInput.value = '';
                dateInput.value = '';
            });

            // JS-валідація перед відправкою
            form.addEventListener('submit', (e) => {
                let hasError = false;

                document.querySelectorAll('.error-text').forEach(el => el.textContent = '');
                document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

                if (!nameInput.value.trim() || nameInput.value.trim().length < 2) {
                    showError(nameInput, 'error-name', 'Вкажіть назву (мінімум 2 символи).');
                    hasError = true;
                }

                const intervalVal = parseInt(intervalInput.value, 10);
                if (isNaN(intervalVal) || intervalVal <= 0) {
                    showError(intervalInput, 'error-interval', 'Вкажіть число більше 0.');
                    hasError = true;
                }

                if (!dateInput.value) {
                    showError(dateInput, 'error-date', 'Оберіть дату поливу.');
                    hasError = true;
                } else {
                    const selectedDate = new Date(dateInput.value);
                    const today = new Date();
                    today.setHours(23, 59, 59, 999);
                    if (selectedDate > today) {
                        showError(dateInput, 'error-date', 'Дата не може бути в майбутньому.');
                        hasError = true;
                    }
                }

                if (hasError) {
                    e.preventDefault();
                }
            });

            function showError(input, errorElId, message) {
                input.classList.add('is-invalid');
                document.getElementById(errorElId).textContent = message;
            }
        });
    </script>
</body>
</html>