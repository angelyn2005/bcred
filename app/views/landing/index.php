<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barangay E-Credentials</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to bottom right, #dbe7fb, #e0e7ff);
            font-family: 'Poppins', sans-serif;
        }

        /* Header */
        .header {
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        .header h2 {
            color: #185adb;
            margin: 0;
        }
        .header p {
            margin: 0;
            font-size: 0.85rem;
            color: #6c757d;
        }

        /* Hero Section */
        .hero {
            text-align: center;
            padding: 120px 20px 80px;
        }
        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .hero h1 span {
            color: #185adb;
        }
        .hero p {
            font-size: 1.2rem;
            color: #555;
            max-width: 700px;
            margin: 20px auto 40px;
        }
        .hero .btn {
            padding: 12px 30px;
            font-size: 1rem;
            border-radius: 8px;
            font-weight: 600;
        }

        /* Features Section */
        .features .card {
            transition: 0.3s;
            border: none;
            border-radius: 12px;
            text-align: center;
            padding: 30px 20px;
        }
        .features .card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        .features .icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
        }
        .features h3 {
            margin-bottom: 10px;
        }

        /* Available Documents */
        .documents .doc-card {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 500;
        }

        /* How It Works */
        .steps .step-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #185adb;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 15px;
        }

        footer {
            background: #212529;
            color: #ccc;
            padding: 40px 0;
        }
        footer p {
            margin: 0;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="header mb-5">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded p-3">
                    <i class="bi bi-file-text fs-4"></i>
                </div>
                <div>
                    <h2>Barangay E-Credentials</h2>
                    <p>Request & Status Tracking</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="/login" class="btn btn-outline-primary">Login</a>
                <a href="/register" class="btn btn-primary">Register</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Request Barangay Documents <br><span>Anytime, Anywhere</span></h1>
        <p>Skip the long lines! Request your barangay certificates and clearances online. Track your application status in real-time.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="/login" class="btn btn-primary btn-lg">Get Started</a>
            <a href="/register" class="btn btn-outline-primary btn-lg">Create Account</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features container py-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="icon-circle bg-primary bg-opacity-25 text-primary mb-3">
                        <i class="bi bi-lightning"></i>
                    </div>
                    <h3>Fast & Easy</h3>
                    <p>Submit your request in minutes. No need to visit the barangay hall.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="icon-circle bg-success bg-opacity-25 text-success mb-3">
                        <i class="bi bi-clock"></i>
                    </div>
                    <h3>Real-time Tracking</h3>
                    <p>Monitor your application status from submission to release.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="icon-circle bg-purple bg-opacity-25 text-purple mb-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3>Secure & Reliable</h3>
                    <p>Your personal information is protected with industry-standard security.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Available Documents -->
    <section class="documents container py-5">
        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-center mb-4">Available Documents</h2>
            <div class="row g-3 justify-content-center">
                <?php 
                    $docs = ['Barangay Clearance','Indigency Certificate','Residency Certificate','Business Permit','Barangay ID'];
                    foreach($docs as $doc): 
                ?>
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="doc-card">
                            <i class="bi bi-check-circle text-success"></i>
                            <span><?= $doc ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="steps container py-5">
        <h2 class="text-center mb-5">How It Works</h2>
        <div class="row g-4 text-center">
            <?php 
                $steps = [
                    ['step'=>'1','title'=>'Create Account','desc'=>'Register with your basic information'],
                    ['step'=>'2','title'=>'Submit Request','desc'=>'Choose document type and fill out the form'],
                    ['step'=>'3','title'=>'Track Status','desc'=>'Monitor your request in real-time'],
                    ['step'=>'4','title'=>'Claim Document','desc'=>'Get notified when ready for release'],
                ];
                foreach($steps as $s):
            ?>
                <div class="col-md-3">
                    <div class="step-circle"><?= $s['step'] ?></div>
                    <h5><?= $s['title'] ?></h5>
                    <p class="text-muted"><?= $s['desc'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p>© <?= date('Y') ?> Barangay E-Credentials. All rights reserved.</p>
            <p class="text-muted small">For inquiries, contact your local barangay office.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
