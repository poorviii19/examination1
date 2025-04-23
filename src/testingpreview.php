<?php
session_start();
include "connection.php";
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';

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
            echo "<script>alert('Profile image updated successfully.'); window.location.href = 'profile1.php';</script>";
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
<html lang="hi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="w-11/12 bg-white rounded-lg shadow-lg p-6 flex flex-col md:flex-row">
    
    <div class="flex-1 flex flex-col items-center justify-center bg-gray-50 p-4 border-r">
      <h2 class="text-xl font-semibold mb-4">Online Exam Instructions</h2>
      <ul class="list-disc space-y-2 pl-6 text-gray-700">
        <li>Welcome to Online Exam for General Aptitude Exam</li>
        <li>Exam has Total 15 Questions</li>
        <li>Total Time for Exam is 30 Minutes</li>
        <li>Negative Marking Exam: No</li>
        <li>Best of Luck for your Exam!</li>
      </ul>
      <button class="mt-4 px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"><a href="AIPHYSICSTEST.php">Start Test</a></button>
    </div>

    
    <div class="flex-1 bg-gray-50 p-4">
      <div class="flex flex-col items-center ">
        <div class="flex ">

            <video id="video" class="w-3/6 h-64 bg-black rounded mb-4 self-start" autoplay playsinline></video>
            <div class="flex flex-col items-center space-y-6 ml-[2cm] mt-[2cm]">
                <div class="flex items-center space-x-4">
                    <button id="testCameraBtn" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        Test Webcam
                    </button>
                    <span id="cameraStatus" class="hidden text-green-600 font-semibold">
                        ✅ Checked
                    </span>
                </div>
                <div class="flex items-center space-x-4" style="margin-top: 1cm;">
                    <button id="testMicBtn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        Test Microphone
                    </button>
                    <span id="micStatus" class="hidden text-green-600 font-semibold">
                        ✅ Checked
                    </span>
                </div>
            </div>
        </div>
        
        <div class="w-full">
          <div class="h-3 bg-gray-300 rounded-full overflow-hidden">
            <div id="audioBar" class="h-full bg-green-500 transition-all duration-100" style="width: 0%;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const testCameraBtn = document.getElementById('testCameraBtn');
    const testMicBtn = document.getElementById('testMicBtn');
    const video = document.getElementById('video');
    const audioBar = document.getElementById('audioBar');
    const cameraStatus = document.getElementById('cameraStatus');
    const micStatus = document.getElementById('micStatus');
    let cameraVerified = false;
    let micVerified = false;

    testCameraBtn.addEventListener('click', async () => {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        setTimeout(() => {
          cameraVerified = true;
          cameraStatus.classList.remove('hidden');
        }, 3000);
      } catch (err) {
        alert('Camera access failed.');
      }
    });

    testMicBtn.addEventListener('click', async () => {
      try {
        const micStream = await navigator.mediaDevices.getUserMedia({ audio: true });
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const analyser = audioContext.createAnalyser();
        const source = audioContext.createMediaStreamSource(micStream);
        analyser.fftSize = 256;
        source.connect(analyser);
        const dataArray = new Uint8Array(analyser.frequencyBinCount);

        function updateAudioLevel() {
          analyser.getByteFrequencyData(dataArray);
          const avg = dataArray.reduce((a, b) => a + b, 0) / dataArray.length;
          audioBar.style.width = `${Math.min(avg, 100)}%`;
          requestAnimationFrame(updateAudioLevel);
        }

        updateAudioLevel();
        setTimeout(() => {
          micVerified = true;
          micStatus.classList.remove('hidden');
        }, 3000);
      } catch (err) {
        alert('Microphone access failed.');
      }
    });
  </script>
</body>
</html>