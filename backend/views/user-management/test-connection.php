<?php

use yii\helpers\Html;

$this->title = 'PIDS API Connection Test';
?>

<div style="padding: 30px; background: #f8f9fa; min-height: 100vh;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h1 style="color: #2D1F13; margin-bottom: 10px;">🔍 PIDS API Connection Diagnostics</h1>
            <p style="color: #666; margin-bottom: 30px;">Testing API connectivity from the live server</p>
            
            <button id="runTest" onclick="runDiagnostics()" style="padding: 12px 24px; background: #967259; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; margin-bottom: 20px;">
                ▶️ Run Diagnostics
            </button>
            
            <div id="loading" style="display: none; padding: 20px; background: #e3f2fd; border-radius: 8px; margin-bottom: 20px;">
                <p style="margin: 0; color: #1976d2;">⏳ Running diagnostics, please wait...</p>
            </div>
            
            <div id="results" style="display: none;">
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; font-family: monospace; font-size: 14px; overflow-x: auto;">
                    <pre id="resultsContent" style="margin: 0; white-space: pre-wrap; word-wrap: break-word;"></pre>
                </div>
            </div>
            
            <div id="error" style="display: none; padding: 20px; background: #ffebee; border-radius: 8px; color: #c62828; margin-bottom: 20px;">
                <strong>❌ Error:</strong> <span id="errorMessage"></span>
            </div>
            
            <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #856404;">💡 Common Issues & Solutions</h3>
                <ul style="color: #856404; line-height: 1.8;">
                    <li><strong>SSL Certificate Error:</strong> The server may not trust the PIDS API SSL certificate. Check if CA certificates are installed.</li>
                    <li><strong>Connection Timeout:</strong> Firewall may be blocking outbound HTTPS connections. Check with your hosting provider.</li>
                    <li><strong>DNS Resolution Failed:</strong> Server cannot resolve dts.pids.gov.ph. Check DNS settings.</li>
                    <li><strong>HTTP Error 403/401:</strong> API token may be incorrect or expired.</li>
                    <li><strong>Network Unreachable:</strong> Server may be behind a proxy. Configure proxy settings if needed.</li>
                </ul>
            </div>
            
            <div style="margin-top: 20px;">
                <?= Html::a('← Back to User Management', ['index'], ['style' => 'color: #967259; text-decoration: none; font-weight: 600;']) ?>
            </div>
        </div>
    </div>
</div>

<script>
function runDiagnostics() {
    const button = document.getElementById('runTest');
    const loading = document.getElementById('loading');
    const results = document.getElementById('results');
    const resultsContent = document.getElementById('resultsContent');
    const errorDiv = document.getElementById('error');
    const errorMessage = document.getElementById('errorMessage');
    
    // Reset UI
    button.disabled = true;
    button.textContent = '⏳ Testing...';
    loading.style.display = 'block';
    results.style.display = 'none';
    errorDiv.style.display = 'none';
    
    fetch('<?= \yii\helpers\Url::to(['user-management/test-connection']) ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        loading.style.display = 'none';
        results.style.display = 'block';
        
        // Format and display results
        resultsContent.textContent = JSON.stringify(data, null, 2);
        
        // Check for failures
        if (data.diagnostics && data.diagnostics.api_test && !data.diagnostics.api_test.success) {
            errorDiv.style.display = 'block';
            errorMessage.textContent = data.diagnostics.api_test.error || 'API test failed';
        }
        
        button.disabled = false;
        button.textContent = '▶️ Run Diagnostics Again';
    })
    .catch(error => {
        loading.style.display = 'none';
        errorDiv.style.display = 'block';
        errorMessage.textContent = error.message || 'Failed to run diagnostics';
        button.disabled = false;
        button.textContent = '▶️ Run Diagnostics';
    });
}

// Auto-run on page load
window.addEventListener('load', function() {
    runDiagnostics();
});
</script>
