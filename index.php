<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dominican College of Sta. Rosa, Laguna, Inc.</title>
    <link rel="icon" type="image/png" href="uploads/logo.png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* CSS Variables for animation timing */
        :root {
            --splash-fade-duration: 0.5s;
            --header-animation-duration: 0.8s;
        }

        /* Entire page background */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('uploads/background.jpeg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }

        /* Blur overlay */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.3);
            z-index: -1;
        }

        /* Splash Screen */
        #splash {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        #splash img {
            width: 220px;
            height: auto;
            opacity: 0;
            transform: scale(0.8);
            animation: logoIn 1s ease forwards;
        }

        .school-name {
            font-size: 1.6rem;
            color: white;
            margin-top: 1rem;
            opacity: 0;
            transform: translateY(20px);
            animation: nameSlideIn 1s ease forwards;
            animation-delay: 0.8s;
            text-align: center;
            padding: 0 20px;
        }

        /* When outro starts */
        #splash.outro {
            animation: splashOutro 0.8s forwards;
        }

        /* Main content hidden at first */
        #main-content {
            opacity: 0;
            transition: opacity var(--splash-fade-duration) ease;
        }

        #main-content.show {
            opacity: 1;
        }

        /* Header animation */
        #mainHeader {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity var(--header-animation-duration) ease,
                        transform var(--header-animation-duration) ease;
        }

        #mainHeader.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Keyframes */
        @keyframes logoIn {
            to { 
                opacity: 1; 
                transform: scale(1); 
            }
        }

        @keyframes nameSlideIn {
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        /* Outro: fade + slide up */
        @keyframes splashOutro {
            to {
                opacity: 0;
                transform: translateY(-100%);
                pointer-events: none;
            }
        }

        /* Government Links Section */
        .gov-section {
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.95);
            margin: 20px;
            border-radius: 10px;
        }

        .gov-section h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .gov-boxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .gov-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.5);
        }

        .gov-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }

        .gov-box img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-bottom: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            padding: 10px;
        }

        .gov-box span {
            font-weight: bold;
            text-align: center;
        }

        /* School Systems Section */
        .systems-section {
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.95);
            margin: 20px;
            border-radius: 10px;
        }

        .systems-section h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .systems-boxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            max-width: 900px;
            margin: 0 auto;
        }

        .system-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px;
            background: white;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.5);
        }

        .system-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }

        .system-box img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-bottom: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            padding: 15px;
        }

        /* Short Content and Landing Content */
        .short-content, .landing-content {
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.95);
            margin: 20px;
            border-radius: 10px;
        }

        .short-content h5, .landing-content .description {
            color: white;
        }

        /* Side Boxes */
        .side-boxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .side-boxes .box {
            width: 100%;
            height: 250px;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.5);
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .side-boxes .box img,
        .side-boxes .box video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
    </style>
</head>
<body>
    <!-- Splash Screen -->
    <div id="splash">
        <img src="uploads/logo.png" alt="Dominican College Logo">
        <h1 class="school-name">Dominican College of Sta. Rosa, Laguna, Inc.</h1>
    </div>

    <!-- Main Content -->
    <div id="main-content">
        <!-- Header -->
        <?php include 'includes/header.php'; ?>
        
        <!-- Navbar -->
        <?php include 'includes/navbar.php'; ?>
        
        <!-- Short Content -->
        <div class="short-content">
            <div class="description">
                <h5>Dominican College of Santa Rosa is a school that teaches different academic skills and values that will enhance and develop skills of the students to prepare them on their careers in the near future.</h5>
            </div>
        </div>

        <!-- Main Landing Content -->
        <div class="landing-content">
            <div class="description">
                Dominican College of Sta. Rosa, Laguna, Inc. unceasingly provides quality education nurtured with religious experiences. And now with its fully spread wings joins educational institutions to assist in providing the nation's citizenry a holistic development scheme with religion as its core. It foresees graduates who are Christ-centered, lovers of the Virgin Mary, defenders of truth and responsive citizens.
                <br><br>
                Dominican College of Sta. Rosa Basic Education Department adapted the Department of Education's K-12 Curriculum. The school has added enrichment subjects in Science, Math and English subjects; thus calling DCSR's Basic Education's curriculum as K-12 enriched.
                <br><br>
                In March 2013, the school was granted a Certification Status as ESC-participating school by the Fund Assistance for Private School (FAPE). PAASCU made its Formal Visit in DCSR on January 11 and 12, 2018, after intensive and collaborative efforts by the entire DCSR community. On May 30, 2018, PAASCU awarded DCSR Certificate of Accreditation to the Basic Education Program for meeting the standards and fulfilling the requirements of the Association.
            </div>
        </div>

        <!-- Side Boxes -->
        <div class="side-boxes">
            <div class="box">
                <img src="https://www.dcsr.edu.ph/wp-content/uploads/2022/03/Admission-d-1536x922.png" alt="Image 1">
            </div>
            <div class="box">
                <video src="https://www.dcsr.edu.ph/wp-content/uploads/2022/03/AVP_1.mp4" autoplay muted controls loop></video>
            </div>
            <div class="box">
                <img src="../uploads/background.jpeg" alt="Image 2">
            </div>
        </div>

        <!-- Government Links Section -->
        <section class="gov-section">
            <h2>Government Links</h2>
            <div class="gov-boxes">
                <a href="https://www.philhealth.gov.ph/" class="gov-box" target="_blank" rel="noopener">
                    <img src="data:image/png;base64,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" alt="PhilHealth">
                    <span>PhilHealth</span>
                </a>
                <a href="https://www.gov.ph/" class="gov-box" target="_blank" rel="noopener">
                    <img src="https://e.gov.ph/_next/static/media/logo.e3652a48.svg" alt="eGov PH">
                    <span>eGov PH</span>
                </a>
                <a href="https://www.sss.gov.ph/" class="gov-box" target="_blank" rel="noopener">
                    <img src="data:image/png;base64,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" alt="SSS">
                    <span>Social Security System</span>
                </a>
                <a href="https://www.pagibigfund.gov.ph/" class="gov-box" target="_blank" rel="noopener">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSQeQMPTBzjaEPvNgAAO0aW7f4PStiZKw-DKA&s" alt="Pag-IBIG">
                    <span>Pag-IBIG Fund</span>
                </a>
                <a href="https://psahelpline.ph/" class="gov-box" target="_blank" rel="noopener">
                    <img src="data:image/jpeg;base64,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" alt="PSA">
                    <span>PSA Helpline</span>
                </a>
                <a href="https://www.doh.gov.ph/ORUS" class="gov-box" target="_blank" rel="noopener">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTDjXv-tpqll4SqTwydif8wl96pTFd_20p0lQ&s" alt="ORUS">
                    <span>ORUS</span>
                </a>
            </div>
        </section>

        <!-- School Systems Section -->
        <section class="systems-section">
            <h2>School Systems</h2>
            <div class="systems-boxes">
                <a href="https://dcsrsis.orangeapps.ph/dcsr/" class="system-box" target="_blank" rel="noopener">
                    <img src="https://www.dcsr.edu.ph/wp-content/uploads/2022/03/SIS.png" alt="SiS">
                    <span>SiS</span>
                </a>
                <a href="https://dcsrlms.orangeapps.ph/oa_school/" class="system-box" target="_blank" rel="noopener">
                    <img src="https://www.dcsr.edu.ph/wp-content/uploads/2022/03/LMS.png" alt="LMS">
                    <span>LMS</span>
                </a>
                <a href="https://dominicanstarosaerp.orangeapps.ph/hris/" class="system-box" target="_blank" rel="noopener">
                    <img src="https://www.dcsr.edu.ph/wp-content/uploads/2022/03/HRIS.png" alt="HRIS">
                    <span>HRIS</span>
                </a>
            </div>
        </section>

        <!-- Footer -->
        <?php include 'includes/footer.php'; ?>
    </div>

    <script src="js/main.js"></script>
</body>
</html>