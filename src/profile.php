<?php
session_start();
include "connection.php";

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Uploading image on form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uniqueName = uniqid() . '_' . basename($_FILES['profileImage']['name']);
    $uploadFile = $uploadDir . $uniqueName;

    if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadFile)) {
        $param = !empty($_SESSION['enrollment_id']) ? $_SESSION['enrollment_id'] : $_SESSION['email'];
        $query = "UPDATE users SET profile_image = ? WHERE " . (!empty($_SESSION['enrollment_id']) ? "enrollment_id = ?" : "email = ?");

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $uploadFile, $param);

        if ($stmt->execute()) {
            $_SESSION['profile_image'] = $uploadFile;
            echo "<script>alert('Profile image updated successfully.'); window.location.href = 'profile.php';</script>";
            exit;
        } else {
            echo "<script>alert('Database update failed.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('File upload failed.');</script>";
    }
}

// Fetch user info
$user = null;
$param = !empty($_SESSION['enrollment_id']) ? $_SESSION['enrollment_id'] : $_SESSION['email'];
$query = "SELECT fname, email, enrollment_id, profile_image, phone FROM users WHERE " . (!empty($_SESSION['enrollment_id']) ? "enrollment_id = ?" : "email = ?");

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $param);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['profile_image'] = $user['profile_image'];
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="output.css">
    <script>
        function updateStats(totalTests, averageScore, accuracy) {
            document.getElementById('total-tests').innerText = totalTests;
            document.getElementById('average-score').innerText = averageScore;
            document.getElementById('accuracy').innerText = `${accuracy}%`;

            document.querySelector('circle#accuracy-circle').style.setProperty('--progress', accuracy);
        }

        // Example: Update stats (You can call this function based on the actual data)
        updateStats(10, 85, 75);
        function unlockBadge(badgeName, emoji, bgColor, textColor) {
    const badgeContainer = document.getElementById('dynamic-badges');
    const badge = document.createElement('div');
    badge.className = `flex items-center ${bgColor} ${textColor} text-sm font-medium px-3 py-1 rounded-full`;
    badge.innerHTML = `${emoji} ${badgeName}`;
    badgeContainer.appendChild(badge);
  }

  // Example: Unlock badges based on tests attempted
  let testsAttempted = 5;  // This can be fetched dynamically from backend

  if (testsAttempted >= 1) unlockBadge('First Attempt', '🥇', 'bg-purple-100', 'text-purple-600');
  if (testsAttempted >= 5) unlockBadge('Test Warrior', '⚔️', 'bg-red-100', 'text-red-600');
  if (testsAttempted >= 10) unlockBadge('Elite Tester', '👑', 'bg-indigo-100', 'text-indigo-600');
  let badges = JSON.parse(localStorage.getItem('badges')) || [];

  function saveBadge(name, emoji, bgColor, textColor) {
    if (!badges.includes(name)) {
      badges.push(name);
      localStorage.setItem('badges', JSON.stringify(badges));
      unlockBadge(name, emoji, bgColor, textColor);
    }
  }

  // Example Usage
  saveBadge('Quiz Master', '🧠', 'bg-blue-100', 'text-blue-600');
  function getTodayDate() {
    return new Date().toISOString().split('T')[0];
}

function updateStreak() {
    let today = getTodayDate(), lastAttempt = localStorage.getItem('lastAttempt');
    let streak = parseInt(localStorage.getItem('currentStreak')) || 0;
    let longest = parseInt(localStorage.getItem('longestStreak')) || 0;

    if (lastAttempt !== today) {
        streak = (lastAttempt === getYesterdayDate()) ? streak + 1 : 1;
        localStorage.setItem('lastAttempt', today);
        localStorage.setItem('currentStreak', streak);
        localStorage.setItem('longestStreak', Math.max(streak, longest));
    }

    document.getElementById('currentStreak').innerText = streak;
    document.getElementById('longestStreak').innerText = longest;
}

function getYesterdayDate() {
    let d = new Date();
    d.setDate(d.getDate() - 1);
    return d.toISOString().split('T')[0];
}

window.onload = updateStreak;

    </script>
</head>

<body>
    <!-- Navbar -->
    <nav class="flex justify-between h-18 bg-white p-4 text-black shadow-lg text-center text-lg font-semibold">
        <div class="flex items-center">
            <img class="rounded-full size-10" src="Logo.png" alt="Logo">
            <h1 class="pl-2">XamXpress</h1>
        </div>
        <ul class="flex justify-center items-center space-x-10">
            <li><a href="Tdashboard.php">Home</a></li>
            <li><a href="test_design.php">Test Design</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
        <div class="relative flex items-center text-center">
        <img id="avatarButton" type="button" data-dropdown-toggle="userDropdown" data-dropdown-placement="bottom-start" class="w-10 h-10 rounded-full cursor-pointer mr-2" src="<?php echo isset($_SESSION['profile_image']) ? htmlspecialchars($_SESSION['profile_image'], ENT_QUOTES, 'UTF-8') : 'user-avtar-modified.png'; ?>" alt="User dropdown">            

                <p id="userName" name="name"><?php echo isset($user) ? htmlspecialchars($user['fname'], ENT_QUOTES, 'UTF-8') : 'Guest'; ?></p>

            <div id="userDropdown"
                class="z-10 hidden absolute mt-55 mr-10 right-0 bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700 dark:divide-gray-600">
                <div class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                <div id="userEmail" class="font-medium truncate" name="email">
                        <?php echo isset($user) ? htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') : 'Not logged in'; ?>
                    </div>
                </div>
                <div class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="avatarButton">
                    <a href="profile.php"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Profile</a>
                </div>
                <div class="py-1 flex justify-center items-center">
                    <a href="index.php"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Logout</a>
                    <img src="logout.png" alt="logout" class="w-6 h-6 ml-2">
                </div>
            </div>
        </div>

        <script>
            document.getElementById('avatarButton').addEventListener('click', function (event) {
            event.stopPropagation();
            var dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', function (event) {
            var dropdown = document.getElementById('userDropdown');
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        });
        </script>
    </nav>

    <div class="flex">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>
            <link rel="stylesheet" href="output.css">
        </head>

        <body>
            <div class="flex ">

            <div class=" transition-transform duration-300 hover:scale-105 hover:z-10  ml-15 mt-10 rounded-xl border-white border-2 shadow-2xl shadow-gray-400 h-[14cm] w-[10cm] ">
                    
                    <div class="pl-[3cm]">
                        <!-- Profile Image Display -->
                        <img id="profileImage"
                            src="<?php echo isset($_SESSION['profile_image']) ? htmlspecialchars($_SESSION['profile_image'], ENT_QUOTES, 'UTF-8') : 'default-profile.png'; ?>"
                            class="rounded-full mt-4 size-35 ring-3 ring-gray-300 mb-4" alt="">

                        <!-- Working Upload Form -->
                        <form action="profile.php" method="POST" enctype="multipart/form-data">
                            <label
                                class="text-sm cursor-pointer bg-blue-500 text-white px-2 py-2 ml-5 relative rounded-md hover:bg-blue-600">
                                Upload Image
                                <input type="file" name="profileImage" accept="image/*" class="hidden"
                                    onchange="this.form.submit()">
                            </label>
                        </form>
                    </div>
                    <br>
                    <div class="font-bold text-xl mt-5 text-center">
                        <p id="userName" name="name">
                            <?php echo isset($user) ? htmlspecialchars($user['fname'], ENT_QUOTES, 'UTF-8') : 'Guest'; ?>
                        </p>
                        <br>
                    </div>
                    <div class="ml-15 mt-2">
                        <div class="flex space-x-2">
                            <img src="University.png" class="size-5">
                            Teacher at XYZ University<br>
                        </div>
                        <div class="flex space-x-2 mt-5">
                            <img src="identity.png" class="size-5">
                            <p>Enrollment ID:
                                <?php echo isset($user) ? htmlspecialchars($_SESSION['enrollment_id'], ENT_QUOTES, 'UTF-8') : 'N/A'; ?>
                            </p>

                        </div>

                        <div class="flex space-x-2  mt-5">
                            <img src="phone-call.png" class="size-5">
                            <p class="text-center">Mob: 
                                <?php echo isset($user) ? htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8') : 'N/A'; ?>
                            </p>
                        </div>
                        <div class="flex space-x-2  mt-5">
                            <img src="email.png" class="size-5">
                            <p class="text-center">Email: 
                                <?php echo isset($user) ? htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') : 'N/A'; ?>
                            </p>
                        </div>
                        <div class="flex space-x-2  mt-5">
                            <img src="calendar.png" class="size-5">
                            <p class="text-center">DOB: 06-10-2000</p>
                        </div>
                        <br>
                    </div>

                </div>
                <div>

                    <div
                        class="border-white border-1 transition-transform duration-300 hover:scale-105 hover:z-10 shadow-2xl shadow-gray-400 rounded-xl ml-8 mt-10 h-20 w-205 pl-10 pt-6 font-semibold">
                       
                        <div class="flex space-x-15">
                            <a href="" class="text-sm">Previously Created Tests</a>
                            <a href="" class="text-sm">Documents & Certificates</a>
                            <a href="" class="text-sm">Result History</a>
                            <div class="flex space-x-2">
                                <a href="" class="text-sm">Preference & Settings</a>
                                <img src="settings.png" onclick="" class="size-5">
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="border-white border-1 transition-transform duration-300 hover:scale-105 hover:z-10 shadow-2xl shadow-gray-400 rounded-xl ml-8  mt-4 h-60 w-[18cm] pl-6 pt-6">
                        <h1 class="text-2xl font-bold flex justify-center">Quick Stats</h1>
                        <div class="flex justify-center mx-auto space-x-20 mt-10">
                            <div>
                                <h1 class="text-lg font-semibold ml-[-0.5cm]">Total Test Taken</h1>
                                <div class="relative size-25">
                                    <!-- Background Ring -->
                                    <svg class="absolute inset-0 w-full h-full transform -rotate-90"
                                        viewBox="0 0 100 100">
                                        <circle class="text-gray-200 stroke-current" cx="50" cy="50" r="40"
                                            stroke-width="10" fill="transparent" />
                                    </svg>

                                    <!-- Progress Ring (Dynamic) -->
                                    <svg class="absolute inset-0 w-full h-full transform -rotate-90"
                                        viewBox="0 0 100 100">
                                        <circle class="text-blue-500 stroke-current transition-all duration-300" cx="50"
                                            cy="50" r="40" stroke-width="10" fill="transparent" stroke-dasharray="251.2"
                                            stroke-dashoffset="calc(251.2 - (251.2 * var(--progress)) / 100)" />
                                    </svg>

                                    <!-- Profile Image or Test Count in Center -->
                                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-2xl font-bold text-gray-700" id="progress-text">0</span>

                                    </div>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-lg font-semibold">Average Score</h1>
                                <div class="relative size-25">
                                    <!-- Background Ring -->
                                    <svg class="absolute inset-0 w-full h-full transform -rotate-90"
                                        viewBox="0 0 100 100">
                                        <circle class="text-gray-200 stroke-current" cx="50" cy="50" r="40"
                                            stroke-width="10" fill="transparent" />
                                    </svg>

                                    <!-- Progress Ring (Dynamic) -->
                                    <svg class="absolute inset-0 w-full h-full transform -rotate-90"
                                        viewBox="0 0 100 100">
                                        <circle class="text-blue-500 stroke-current transition-all duration-300" cx="50"
                                            cy="50" r="40" stroke-width="10" fill="transparent" stroke-dasharray="251.2"
                                            stroke-dashoffset="calc(251.2 - (251.2 * var(--progress)) / 100)" />
                                    </svg>

                                    <!-- Profile Image or Test Count in Center -->
                                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-2xl font-bold text-gray-700" id="progress-text">0</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-lg font-semibold ml-4">Accuracy</h1>
                                <div class="relative size-25">
                                    <!-- Background Ring -->
                                    <svg class="absolute inset-0 w-full h-full transform -rotate-90"
                                        viewBox="0 0 100 100">
                                        <circle class="text-gray-200 stroke-current" cx="50" cy="50" r="40"
                                            stroke-width="10" fill="transparent" />
                                    </svg>

                                    <!-- Progress Ring (Dynamic) -->
                                    <svg class="absolute inset-0 w-full h-full transform -rotate-90"
                                        viewBox="0 0 100 100">
                                        <circle class="text-blue-500 stroke-current transition-all duration-300" cx="50"
                                            cy="50" r="40" stroke-width="10" fill="transparent" stroke-dasharray="251.2"
                                            stroke-dashoffset="calc(251.2 - (251.2 * var(--progress)) / 100)" />
                                    </svg>

                                    <!-- Profile Image or Test Count in Center -->
                                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-2xl font-bold text-gray-700" id="progress-text">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <br>

                    <div class="flex">

                        <div
                            class="border-white border-1 transition-transform duration-300 hover:scale-110 hover:z-10 shadow-2xl shadow-gray-400 rounded-xl ml-8 mt-8 h-[4cm] w-[9.7cm] pl-10 flex">
                            <div class="p-6">
                                <h2 class="text-xl font-semibold text-gray-700">Achievements</h2>
                                <div class="flex flex-wrap gap-3 mt-4">
                                  
                                  <!-- Example Badge 1: Beginner -->
                                  <div class="flex items-center bg-blue-100 text-blue-600 text-sm font-medium px-3 py-1 rounded-full">
                                    🎯 Beginner
                                  </div>
                              
                                  <!-- Example Badge 2: Test Master -->
                                  <div class="flex items-center bg-green-100 text-green-600 text-sm font-medium px-3 py-1 rounded-full">
                                    🏆 Test Master
                                  </div>
                              
                                  <!-- Example Badge 3: Consistency King -->
                                  <div class="flex items-center bg-yellow-100 text-yellow-600 text-sm font-medium px-3 py-1 rounded-full">
                                    🔥 Consistency King
                                  </div>
                              
                                  <!-- Dynamic Badge Container -->
                                  <div id="dynamic-badges"></div>
                                </div>
                              </div>
                        </div>
                        <div
                            class="border-white transition-transform duration-300 hover:scale-110 hover:z-10 flex border-1 shadow-2xl shadow-gray-400 rounded-xl ml-6  mt-8 h-[4cm] w-[9.7cm] pl-10 ">
                            <div class="p-6">
                                <h2 class="text-xl font-semibold text-gray-700">🔥 Test Streak</h2>
                                <p class="mt-2 text-lg text-blue-500">Current Streak: <span id="currentStreak">0</span> Days</p>
                                <p class="text-sm text-gray-600">Longest Streak: <span id="longestStreak">0</span> Days</p>
                                <p class="mt-2 text-red-500 hidden" id="resetMsg">⚠ Streak reset! You missed a day.</p>
                            </div>
                            

                        </div>
                    </div>
                </div>
            </div>
        </body>

</html>
</body>

</html>