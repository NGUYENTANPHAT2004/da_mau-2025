<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảo trì - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="maintenance-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <div class="maintenance-content">
                        <div class="maintenance-icon mb-4">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h1>Website đang bảo trì</h1>
                        <p class="lead">Chúng tôi đang nâng cấp hệ thống để mang đến trải nghiệm tốt hơn.</p>
                        <p>Dự kiến hoàn thành trong: <strong id="countdown"></strong></p>
                        
                        <div class="contact-info mt-4">
                            <p>Nếu cần hỗ trợ khẩn cấp, vui lòng liên hệ:</p>
                            <p>
                                <i class="fas fa-phone me-2"></i>1900 1234 |
                                <i class="fas fa-envelope me-2"></i>support@fpolyshop.com
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .maintenance-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
    }
    
    .maintenance-icon i {
        font-size: 5rem;
        color: rgba(255,255,255,0.9);
    }
    
    .maintenance-content h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .contact-info {
        background: rgba(255,255,255,0.1);
        padding: 2rem;
        border-radius: 15px;
        backdrop-filter: blur(10px);
    }
    </style>

    <script>
    // Countdown timer (example: 2 hours)
    let countdown = new Date().getTime() + (2 * 60 * 60 * 1000);
    
    let timer = setInterval(function() {
        let now = new Date().getTime();
        let distance = countdown - now;
        
        let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById("countdown").innerHTML = hours + "h " + minutes + "m " + seconds + "s";
        
        if (distance < 0) {
            clearInterval(timer);
            document.getElementById("countdown").innerHTML = "Sắp hoàn thành!";
        }
    }, 1000);
    </script>
</body>
</html>