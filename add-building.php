<?php
require_once 'header.php';
$page_title = 'Add New Building';

// Check if user is logged in and is a landlord
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header('Location: signin.php');
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // File upload handling
    $uploadDir = 'uploads/buildings/';
    $imageUrl = null;
    
    if (!empty($_FILES['building_image']['name'])) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        $fileName = basename($_FILES['building_image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileSize = $_FILES['building_image']['size'];
        $fileTmp = $_FILES['building_image']['tmp_name'];
        
        if (!in_array($fileExt, $allowedExtensions)) {
            $error = "Only JPG, JPEG, PNG, and WEBP files are allowed.";
        } elseif ($fileSize > $maxFileSize) {
            $error = "File size must be less than 5MB.";
        } else {
            // Generate unique filename
            $newFileName = uniqid('building_', true) . '.' . $fileExt;
            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $imageUrl = $uploadPath;
            } else {
                $error = "Error uploading file.";
            }
        }
    }

    // Sanitize and validate input
    $required = ['building_name', 'country', 'state_county', 'city', 'address', 'building_type'];
    $data = [];
    foreach ($_POST as $key => $value) {
        $data[$key] = trim(strip_tags($value));
        if (in_array($key, $required) && empty($data[$key])) {
            $error = "Please fill in all required fields";
        }
    }

    // Additional validation for US states
    if ($data['country'] === 'United States' && !isset($_POST['state_select'])) {
        $error = "Please select a valid US state";
    }

    if (!isset($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO buildings 
                (landlord_id, building_name, country, state_county, city, town, zip_code, address, image_url, building_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $data['building_name'],
                $data['country'],
                ($data['country'] === 'United States') ? $_POST['state_select'] : $data['state_county'],
                $data['city'],
                $data['town'] ?? null,
                $data['zip_code'] ?? null,
                $data['address'],
                $imageUrl,
                $data['building_type']
            ]);

            $success = "Building added successfully!";
        } catch (PDOException $e) {
            $error = "Error saving building: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Add New Building</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php elseif (isset($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST" id="buildingForm" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <!-- Building Name -->
                        <div class="mb-3">
                            <label for="building_name" class="form-label">Building Name *</label>
                            <input type="text" class="form-control" id="building_name" name="building_name" required>
                        </div>

                        <!-- Country Select -->
                        <div class="mb-3">
                            <label for="country" class="form-label">Country *</label>
                            <select class="form-select" id="country" name="country" required>
                                <option value="" disabled selected>Loading countries...</option>
                            </select>
                        </div>

                        <!-- State/County Field -->
                        <div class="mb-3">
                            <label class="form-label">State/County *</label>
                            <div id="stateCountyContainer">
                                <!-- Dynamic content will be loaded here -->
                                <input type="text" class="form-control" name="state_county" 
                                    placeholder="Start typing state/county" disabled>
                            </div>
                            <select class="form-select d-none" id="state_select" name="state_select"></select>
                        </div>

                        <!-- City -->
                        <div class="mb-3">
                            <label for="city" class="form-label">City *</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>

                        <!-- Town -->
                        <div class="mb-3">
                            <label for="town" class="form-label">Town</label>
                            <input type="text" class="form-control" id="town" name="town">
                        </div>

                        <!-- Zip Code -->
                        <div class="mb-3">
                            <label for="zip_code" class="form-label">Zip/Postal Code</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code">
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label">Full Address *</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="building_image" class="form-label">Building Image</label>
                            <input type="file" class="form-control" id="building_image" 
                                name="building_image" accept="image/*">
                            <div class="form-text">Max file size: 5MB (JPEG, PNG, WEBP)</div>
                        </div>

                        <!-- Building Type -->
                        <div class="mb-4">
                            <label for="building_type" class="form-label">Building Type *</label>
                            <select class="form-select" id="building_type" name="building_type" required>
                                <option value="apartment">Apartment</option>
                                <option value="house">House</option>
                                <option value="commercial">Commercial</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Add Building</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('country');
    const stateCountyContainer = document.getElementById('stateCountyContainer');
    const stateSelect = document.getElementById('state_select');

    // Fetch countries
    fetch('https://restcountries.com/v3.1/all?fields=name')
        .then(response => response.json())
        .then(data => {
            countrySelect.innerHTML = '<option value="" disabled selected>Select a country</option>';
            data.sort((a, b) => a.name.common.localeCompare(b.name.common))
                .forEach(country => {
                    const option = document.createElement('option');
                    option.value = country.name.common;
                    option.textContent = country.name.common;
                    countrySelect.appendChild(option);
                });
        });

    // Handle country change
    countrySelect.addEventListener('change', function() {
        if (this.value === 'United States') {
            // Fetch US states
            fetch('https://api.census.gov/data/2010/dec/sf1?get=NAME&for=state:*')
                .then(response => response.json())
                .then(data => {
                    stateSelect.innerHTML = '';
                    data.slice(1).forEach(state => {
                        const option = document.createElement('option');
                        option.value = state[0];
                        option.textContent = state[0];
                        stateSelect.appendChild(option);
                    });
                    
                    // Show select and hide input
                    stateSelect.classList.remove('d-none');
                    stateCountyContainer.innerHTML = '<div class="form-text">Select a US state</div>';
                });
        } else {
            // Show input field for other countries
            stateSelect.classList.add('d-none');
            stateCountyContainer.innerHTML = `
                <input type="text" class="form-control" name="state_county" 
                    placeholder="Enter state/province/county" required>
            `;
        }
    });
});
</script>

<?php include 'footer.php'; ?>