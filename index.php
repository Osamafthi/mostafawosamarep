<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mostafa Ahmed | Portfolio</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #1a1a2e;
            --secondary: #16213e;
            --accent: #0f3460;
            --highlight: #e94560;
            --text: #eaeaea;
            --text-muted: #a0a0a0;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%);
            min-height: 100vh;
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.08);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .photo-wrap {
            width: 180px;
            height: 180px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid var(--highlight);
            box-shadow: 0 0 30px rgba(233, 69, 96, 0.3);
            animation: photoPop 0.6s ease-out 0.3s both;
        }

        @keyframes photoPop {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .photo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(90deg, #fff, var(--highlight));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeIn 0.8s ease-out 0.4s both;
        }

        .degree {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(233, 69, 96, 0.2);
            color: var(--highlight);
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 1rem;
            border: 1px solid rgba(233, 69, 96, 0.3);
            animation: fadeIn 0.8s ease-out 0.5s both;
        }

        .degree::before {
            content: "🎓";
            font-size: 1.2rem;
        }

        .tagline {
            color: var(--text-muted);
            margin-top: 1.5rem;
            font-size: 0.95rem;
            animation: fadeIn 0.8s ease-out 0.6s both;
        }

        .links {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeIn 0.8s ease-out 0.7s both;
        }

        .links a {
            color: var(--text);
            text-decoration: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .links a:hover {
            background: var(--highlight);
            border-color: var(--highlight);
            transform: translateY(-2px);
        }

        footer {
            margin-top: 2rem;
            font-size: 0.8rem;
            color: var(--text-muted);
            animation: fadeIn 0.8s ease-out 0.8s both;
        }

        @media (max-width: 600px) {
            .container { padding: 1rem; }
            .card { padding: 2rem 1.5rem; }
            h1 { font-size: 1.75rem; }
            .photo-wrap { width: 140px; height: 140px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <main class="card">
            <div class="photo-wrap">
                <!-- Replace src with your photo path, e.g. photo.jpg -->
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Mostafa" alt="Mostafa Ahmed">
            </div>
            <h1>Mostafa Ahmed</h1>
            <p class="degree">Computer Science Degree</p>
            <p class="tagline">Building solutions through code</p>
            <div class="links">
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
            </div>
        </main>
        <footer>&copy; <?php echo date('Y'); ?> Mostafa Ahmed</footer>
    </div>

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth' });
            });
        });

        // Subtle parallax on mouse move
        document.addEventListener('mousemove', (e) => {
            const card = document.querySelector('.card');
            const x = (e.clientX - window.innerWidth / 2) * 0.01;
            const y = (e.clientY - window.innerHeight / 2) * 0.01;
            card.style.transform = `perspective(1000px) rotateY(${x}deg) rotateX(${-y}deg)`;
        });

        document.addEventListener('mouseleave', () => {
            document.querySelector('.card').style.transform = 'perspective(1000px) rotateY(0) rotateX(0)';
        });
    </script>
</body>
</html>
