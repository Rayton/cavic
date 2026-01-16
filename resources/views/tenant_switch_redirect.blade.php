<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ _lang('Switching Account') }}...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }
        .loader {
            text-align: center;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <p>{{ _lang('Switching account') }}...</p>
    </div>
    <script>
        // Force browser to navigate to new tenant URL immediately
        // This ensures the URL bar updates immediately
        (function() {
            var targetUrl = '{{ $dashboard_url }}';
            var fullUrl = window.location.origin + targetUrl;
            
            // Use window.location.href to force navigation and update URL bar
            // This is the most reliable way to ensure browser URL bar updates
            if (window.location.pathname !== targetUrl) {
                // Force immediate navigation - this will update the URL bar
                window.location.href = fullUrl;
            } else {
                // If already on the target URL, force a hard reload to ensure session is updated
                window.location.reload(true);
            }
        })();
    </script>
</body>
</html>
